<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <link rel="preload" href="/source/style/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="/source/style/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="/source/style/custom.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="/source/style/bootstrap.min.css">
        <link rel="stylesheet" href="/source/style/bootstrap-icons.min.css">
        <link rel="stylesheet" href="/source/style/custom.css">
    </noscript>
    <script src="/source/script/bootstrap.min.js" defer></script>
</head>

<body>
    <?php include_once $_SERVER["DOCUMENT_ROOT"] . "/preloader.php"; ?>
    <?php include_once $_SERVER["DOCUMENT_ROOT"] . "/header.php"; ?>
    <div class="container mt-5">
        <div class="row mt-5 mb-md-4 mb-2">
            <div class="col-md-8 col-11 mx-auto py-5 rounded-4 d-flex flex-column gap-5 align-items-center justify-content-center">
                <img style="max-width: 200px; width:calc(100% - 32px);" class="h-auto" src="/source/image/folio.webp" alt=""/>
                <input class="form-control shadow p-3 border-0 rounded-4" placeholder="Конфиденциальный поиск в Интернете…" type="search" name="" id=""/>
            </div>
        </div>
        <div class="row" id="renderContent">
        </div>
    </div>
    <script>
        // if ('serviceWorker' in navigator) {
        //     navigator.serviceWorker
        //         .register('/source/script/sw.js')
        //         .then((registration) => {
        //             console.log('Service Worker зарегистрирован:', registration.scope);
        //         })
        //         .catch((error) => {
        //             console.error('Ошибка при регистрации Service Worker:', error);
        //         });
        // }
    </script>
    <script>
        const displayArray = (arr) => {
            const result = arr.map((value, index) => {
                if (index % 5 < 2) {
                    // Первые две (1,2) - <h1>
                    return `
                        <a href="/a/@${value['uuid']}" class="col-lg-6 col-12 my-3">
                            <div class="w-100 h-auto border rounded-5 position-relative ratioBlock1" >
                                <!-- Фоновый элемент с bg-body и z-index -->
                                <div class="position-absolute rounded-4" style="z-index: 2; bottom:0">
                                    <div class="p-lg-5 p-sm-4 p-4 text-white">
                                        <h2>${value['title']}</h2>
                                        <p class="m-0">Content</p>
                                    </div>
                                </div>
                                <div class="w-100 h-100 position-absolute rounded-4" style="background-color: hsla(0, 100%, 0%,0.6); backdrop-filter: blur(4px); z-index: 1;">   
                                </div>
                                <img class="w-100 h-100 rounded-4" style="object-fit: cover; z-index: 2;" src="/article/trash/img_679a2f1c56aa1.webp" alt="" />
                            </div>
                        </a>`;
                } else if (index % 5 < 5) {
                    // Следующие три (3,4,5) - <p>
                    return `
                        <a href="/a/@${value['uuid']}" class="col-lg-4 col-12 my-3">
                            <div class="w-100 h-auto border rounded-4 position-relative ratioBlock2">
                                <div class="position-absolute rounded-4" style="z-index: 2; bottom:0">
                                    <div class="p-md-4 p-4 text-white">
                                        <h3>${value['title']}</h3>
                                        <p class="m-0">Content</p>
                                    </div>
                                </div>
                                <div class="w-100 h-100 position-absolute rounded-4" style="background-color: hsla(0, 100%, 0%,0.6); backdrop-filter: blur(4px); z-index: 1;"></div>
                                <img class="w-100 h-100 rounded-4" style="object-fit: cover; z-index: 2;" src="/article/trash/img_679ce3590efa4.webp" alt="" />
                            </div>
                        </a>`;
                }
                return '';
            }).join('');

            return result;
        }

        const fetchArticle = async () => {
            try {
                const res = await fetch("/backend/all-article.php");
                const data = await res.json();
                if (data.status === "success") document.getElementById("renderContent").innerHTML = displayArray(data.array);
            } catch (error) {
                console.error(error);
            }
        };

        window.addEventListener("load",()=>fetchArticle());
    </script>
</body>

</html>