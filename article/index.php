<?php
    // Определяем константы для путей
    define('ROOT_DIR', $_SERVER["DOCUMENT_ROOT"]);
    define('CACHE_DIR', ROOT_DIR . "/store/cache/");

    // Проверка входного параметра
    if (empty($_GET['article']) || trim($_GET['article']) === '') {
        http_response_code(404);
        include_once ROOT_DIR . "/server/error-404.php";
        exit;
    }

    // Подключение зависимостей
    require_once ROOT_DIR . "/backend/auth-on-frontend.php";
    require_once ROOT_DIR . "/database.php";
    require_once ROOT_DIR . "/_globalEnvValues.php";

    // Экранирование ввода
    $articleId = htmlspecialchars($_GET['article'], ENT_QUOTES, 'UTF-8');
    $get = $articleId;

    try {
        // Инициализация БД
        $db = new SimpleDB();

        // Запрос данных статьи
        $userArticle = $db->fetch(
            "SELECT 
                AES_DECRYPT(u.fullname, ?) AS fullname, 
                u.login, 
                t.author 
            FROM topics t 
            JOIN users u ON u.uuid = t.author 
            WHERE t.uuid = ? 
            AND t.status <> 1 
            LIMIT 1",
            [GLOBAL_ENCRYPT_KEY, $articleId]
        );

        // Проверка результата запроса
        if ($userArticle === false || $userArticle === null) {
            http_response_code(404);
            include_once ROOT_DIR . "/server/error-404.php";
            exit;
        }

        // Проверка кэшированного файла
        $fullPath = CACHE_DIR . $articleId . ".html";
        if (!file_exists($fullPath)) {
            http_response_code(404);
            include_once ROOT_DIR . "/server/error-404.php";
            exit;
        }

        // Проверка сохранений и репостов (оптимизированный запрос)
        $saved = null;
        $repost = null;
        if (isset($user_auth_uuid)) {
            $userData = $db->fetch(
                "SELECT 
                    NULLIF(EXISTS(SELECT 1 FROM saved WHERE author = ? AND article = ?), 0) AS saved,
                    NULLIF(EXISTS(SELECT 1 FROM repost WHERE author = ? AND article = ?), 0) AS repost
                FROM dual
                LIMIT 1",
                [$user_auth_uuid, $articleId, $user_auth_uuid, $articleId]
            );
            $saved = $userData['saved'] ?? null;
            $repost = $userData['repost'] ?? null;
        }

        // Чтение кэшированного HTML
        $html = file_get_contents($fullPath);
        if ($html === false) {
            throw new Exception("Не удалось прочитать файл кэша: $fullPath");
        }

        // Закрытие соединения с БД (если требуется)
        $db->close();

        // Установка заголовков ответа
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: max-age=3600'); // Кэширование на 1 час

    } catch (Exception $e) {
        // Обработка ошибок
        http_response_code(500);
        error_log("Ошибка в скрипте: " . $e->getMessage()); // Логирование
        include_once ROOT_DIR . "/server/error-500.php";
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Document</title>
    <link rel="preload" href="/source/style/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="/source/style/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="/source/style/bootstrap.min.css">
        <link rel="stylesheet" href="/source/style/bootstrap-icons.min.css">
    </noscript>
    <script src="/source/script/bootstrap.min.js" defer></script>
    <style>
        /* Переопределяем стиль активной ссылки */
        .nav-link.active {
            background-color: transparent!important;
            color: var(--bs-body-color)!important;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include_once $_SERVER["DOCUMENT_ROOT"] . "/preloader.php";?>
    <?php include_once $_SERVER["DOCUMENT_ROOT"] . "/header.php"; ?>
    <div class="container-fluid">
        <div class="row position-fixed w-100 d-flex d-lg-none py-3 my-0" style="bottom:0;z-index:4;background-color: rgba(var(--bs-body-bg-rgb),0.8);backdrop-filter: blur(10px);-webkit-backdrop-filter: blur(10px);">
            <a href="/@<?php echo $userArticle['login'];?>" class="col-8 d-flex flex-wrap gap-2 align-items-center link-body-emphasis text-decoration-none">
                <div class="bg-body-secondary rounded-circle" style="width:40px;height:auto;aspect-ratio: 1/1;">
                    <img class="w-100 h-auto rounded-circle" style="aspect-ratio: 1/1; object-fit: cover;" src="/backend/image.php?fullname=<?php echo $userArticle['fullname'];?>" alt=""/>
                </div>
                <div class="ms-1">
                    <h6 class="m-0"><?php echo $userArticle['fullname'];?></h6>
                    <p style="font-size:13px;" class="m-0 p-0 text-secondary">@<?php echo $userArticle['login'];?></p>
                </div>
            </a>
            <div class="col-4 d-flex align-items-center justify-content-end gap-2">
                <button type="button" class="repost_1 btn bg-body-tertiary border rounded-5" data-bs-toggle="modal" data-bs-target="#okkiHeaderSectionModal"><i class="bi bi-list-columns-reverse"></i></button>
                <button onclick="actionUser(1,'repost')" type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Share" class="repost_1 btn bg-body-tertiary border rounded-5 <?php echo isset($repost)?'d-none':'';?>"><i class="nav-icon bi bi-reply-all"></i></button>
                <button onclick="actionUser(2,'repost','-reverse')" type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Share" class="repost_2 btn btn-danger border rounded-5 <?php echo isset($repost)?'':'d-none';?>"><i class="nav-icon bi bi-reply-all-fill"></i></button>
                <div class="repost_preloader d-none spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <button onclick="actionUser(1,'saved')" type="button" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Save" class="saved_1 btn bg-body-tertiary border rounded-5 <?php echo isset($saved)?'d-none':'';?>"><i class="nav-icon bi bi-bookmark"></i></button>
                <button onclick="actionUser(2,'saved','-reverse')" type="button" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Save" class="saved_2 btn btn-primary border rounded-5 <?php echo isset($saved)?'':'d-none';?>"><i class="nav-icon bi bi-bookmark-fill"></i></button>
                <div class="saved_preloader d-none spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    <div class="container-xxl">
        <div class="row position-relative">
            <div class="col-xl-10 col-lg-9 col-12 order-lg-1 order-2">
                <?php echo $html;?>
            </div>
            <div class="col-xl-2 col-lg-3 col-12 d-lg-flex d-none flex-column gap-4 align-items-center order-lg-2 order-1">
                <div class="position-sticky d-flex flex-column align-items-center" style="top:130px;">
                    <div class="h-auto bg-body-secondary rounded-circle mx-auto" style="max-width:72px;min-width:40px;aspect-ratio: 1/1;">
                        <img class="w-100 h-100 rounded-circle" style="object-fit: cover;" src="/backend/image.php?fullname=<?php echo $userArticle['fullname'];?>&&size=150" alt=""/>
                    </div>
                    <h5 class="text-center m-0 mt-4 mb-2 lh-md"><?php echo $userArticle['fullname'];?></h5>
                    <a href="/@<?php echo $userArticle['login'];?>" style="font-size:13px;" class="text-center nav-link mb-3 text-secondary">@<?php echo $userArticle['login'];?></a>
                    <div class="d-flex gap-2" style="transform: scale(0.9);">
                        <a data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="More" href="/@<?php echo $userArticle['login'];?>" class="btn btn-primary rounded-5">More</a>
                        <button onclick="actionUser(1,'repost')" type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Share" class="repost_1 btn bg-body-tertiary border rounded-5 <?php echo isset($repost)?'d-none':'';?>"><i class="nav-icon bi bi-reply-all"></i></button>
                        <button onclick="actionUser(2,'repost','-reverse')" type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Share" class="repost_2 btn btn-danger border rounded-5 <?php echo isset($repost)?'':'d-none';?>"><i class="nav-icon bi bi-reply-all-fill"></i></button>
                        <div class="repost_preloader d-none spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <button onclick="actionUser(1,'saved')" type="button" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Save" class="saved_1 btn bg-body-tertiary border rounded-5 <?php echo isset($saved)?'d-none':'';?>"><i class="nav-icon bi bi-bookmark"></i></button>
                        <button onclick="actionUser(2,'saved','-reverse')" type="button" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Save" class="saved_2 btn btn-primary border rounded-5 <?php echo isset($saved)?'':'d-none';?>"><i class="nav-icon bi bi-bookmark-fill"></i></button>
                        <div class="saved_preloader d-none spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <script>
                        window.addEventListener("load", () => {
                            // Получаем все элементы с атрибутом data-bs-toggle="tooltip" за один раз
                            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                            
                            // Маппируем NodeList напрямую на новый массив, создавая тултипы для всех элементов
                            for (const tooltipTriggerEl of tooltipTriggerList) {
                                new bootstrap.Tooltip(tooltipTriggerEl);
                            }
                        });

                        const actionUser = async (num,type, reverse = "") => {
                            const current = document.querySelectorAll(`.${type}_${num}`);
                            const btn1 = document.querySelectorAll(`.${type}_1`);
                            const btn2 = document.querySelectorAll(`.${type}_2`);
                            const preloader = document.querySelectorAll(`.${type}_preloader`);

                            current.forEach(render=>render.classList.toggle("d-none"));

                            preloader.forEach(loader=>loader.classList.toggle("d-none"));

                            const uuid = crypto.randomUUID();
                            try {
                                const req = await fetch(`/backend/action-user${reverse}.php`, {
                                    method: "PUT",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({ uuid, article: "<?php echo $get;?>", type })
                                });

                                const res = await req.json();
                                console.log(res);

                                if (res.status === "error" && res.message === "auth-required") {
                                    new bootstrap.Modal(document.getElementById('signProcessModal')).show();
                                }

                                if (res.status === "success") {
                                    // Переключаем классы только после успешного ответа
                                    preloader.forEach(loader=>loader.classList.toggle("d-none"));
                                    num!==1?document.querySelectorAll(`.${type}_1`).forEach(render=>render.classList.toggle("d-none")):document.querySelectorAll(`.${type}_2`).forEach(render=>render.classList.toggle("d-none"));
                                }
                            } catch (error) {
                                console.error('Error:', error);
                            }
                        };

                    </script>
                </div>
            </div>
        </div>
    </div>
</body>
</html>