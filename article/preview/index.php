<?php 

    if(!isset($_GET['article'])) die("Article not found");
    include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    $db = new SimpleDB();
    require_once $_SERVER["DOCUMENT_ROOT"] . "/backend/_okki.php";
    $okki = new Okki();
    $article = htmlspecialchars($_GET['article']);
    try {
        $get = $db->fetch("SELECT AES_DECRYPT(`title`,?) AS `title`,`time` FROM `topics` WHERE `uuid`=?",[GLOBAL_ENCRYPT_KEY,$article]);
        if(!$get) die("Not found title on database");
        $scheme = $db->fetchAll("SELECT `uuid`, `tag`,`tag_uuid`, `type` FROM `scheme` WHERE `uuid`=?",[$article]);
        $content = $db->fetchAll("SELECT `scheme_uuid`, `tag_uuid`,`component_uuid`, AES_DECRYPT(`content`,?) AS `content` FROM `content` WHERE `scheme_uuid`=?",[GLOBAL_ENCRYPT_KEY,$article]);
        $titleArticle = $get['title'];
        $firstArray = $scheme;
        $secondArray = $content;
        foreach ($firstArray as &$item) {
            $tagUuid = $item['tag_uuid'];
            $type = $item['type'];

            // Фильтруем второй массив по совпадающему tag_uuid
            $matches = array_filter($secondArray, function($entry) use ($tagUuid) {
                return $entry['tag_uuid'] === $tagUuid;
            });

            if ($type === 'single' && !empty($matches)) {
                // Для single добавляем первый найденный content
                $match = reset($matches); // Берем первый элемент
                $item['content'] = $match['content'];
            } elseif ($type === 'multiple' && !empty($matches)) {
                // Для multiple добавляем массив объектов в contents
                $item['contents'] = array_values($matches); // Перенумеруем массив
            }
        }     
    } catch(Exception $e) {
        die("Error: " . $e);
    }
?>

<?php
    function jsonToTable($jsonString) {
        // Декодируем JSON в PHP массив
        $data = json_decode($jsonString, true);

        // Проверяем, что массив не пуст
        if (empty($data) || !is_array($data)) return "<p>Ошибка: Неверный формат данных.</p>";

        // Получаем значения первого объекта (они станут заголовками `thead`)
        $headers = array_values($data[0]);

        // Начинаем таблицу
        $html = '<table class="table table-bordered">';

        // Создаём thead
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= "<th>" . htmlspecialchars($header) . "</th>";
        }
        $html .= '</tr></thead>';

        // Создаём tbody (начиная со второго объекта)
        $html .= '<tbody>';
        for ($i = 1; $i < count($data); $i++) {
            $html .= '<tr>';
            foreach (array_keys($data[$i]) as $key) {
                $html .= "<td>" . htmlspecialchars($data[$i][$key]) . "</td>";
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        // Закрываем таблицу
        $html .= '</table>';

        return $html;
    }
    function youtubeMediaConverter($url,$render) {
        if (str_contains($url, "youtube.com")) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
            $videoId = $query['v'] ?? null;
        } else if(str_contains($url, "youtu.be")) {  
            $videoId = parse_url($url, PHP_URL_PATH);
            $videoId = $videoId ? trim($videoId, '/') : null;
        }
        $iframe = $videoId ? '<iframe loading="lazy" data-preloader="preloader-' . $render['tag_uuid'] . '" class="rounded-5 youtube-media bg-body-secondary w-100 h-auto" style="aspect-ratio:3/2;" data-src="https://www.youtube-nocookie.com/embed/' . $videoId . '" title="YouTube video player" frameborder="0" allow="encrypted-media" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>':"";
        return $iframe;
    }
    $html = "";
    $html .= '<div class="container-fluid py-5 d-flex flex-column gap-5" id="actionWithArticle">';
    $html .= '<div class="row gap-4">';
    $html .= '<div class="col-12"><h1 class="m-0 p-0">' . $titleArticle . '</h1></div>';
    $html .= '<div class="col-12 my-3"><p class="text-secondary m-0 p-0"><i class="bi bi-calendar-week me-2"></i>' . date("H:i, d.m.Y",$get['time']). '</p></div>';
    foreach($firstArray as $render) {
        if($render['type']==="single") {
            switch($render['tag']) {
                case "header": $html .= '<div class="col-12"><h2 id="header_' . $render['tag_uuid'] . '" class="border-bottom pb-4 fs-2"><div>' .nl2br($render['content']). '</div></h2></div>'; break;
                case "header-3": $html .= '<div class="col-12"><h3 id="header_' . $render['tag_uuid'] . '" class="fs-3"><div>' .nl2br($render['content']). '</div></h3></div>'; break;
                case "header-4": $html .= '<div class="col-12"><h4 id="header_' . $render['tag_uuid'] . '" class="fs-4"><div>' .nl2br($render['content']). '</div></h4></div>'; break;
                case "header-5": $html .= '<div class="col-12"><h5 id="header_' . $render['tag_uuid'] . '" class="fs-5"><div>' .nl2br($render['content']). '</div></h5></div>'; break;
                case "header-6": $html .= '<div class="col-12"><h6 id="header_' . $render['tag_uuid'] . '" class="fs-6"><div>' .nl2br($render['content']). '</div></h6></div>'; break;
                case "paragraph": $html .= '<div class="col-12"><p class="m-0 lh-lg"><div class="lh-lg">' . nl2br($render['content']). "</div></p></div>"; break;
                case "blockquote": $html .= '<div class="col-12"><blockquote class="m-0 border-start border-4 border-info px-4 py-3 rounded-3 lead fs-6"><div class="lh-lg">' . nl2br($render['content']). "</div></blockquote></div>"; break;
                case "table": $html .= '<div class="col-12"><div class="tab-content table-responsive">' . jsonToTable($render['content']) . '</div></div>'; break;
                case "image": $html .= 
                    '<div class="col-12 mb-4">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="position-relative border-dark-subtle" style="max-width:700px;width:100%;height:auto;aspect-ratio:3/2;object-fit:cover;cursor: pointer;z-index:2;">
                                <div id="preloader-' . $render['tag_uuid'] . '" class="w-100 bg-body-secondary position-absolute rounded-5 d-flex align-items-center justify-content-center" style="height:auto;aspect-ratio:3/2;object-fit:cover;cursor: pointer;">
                                    <i class="bi bi-image-fill display-1"></i>
                                </div>
                                <img onclick="okkiPreviewImage(\'' . $render["content"] . '\')" data-preloader="preloader-' . $render['tag_uuid'] . '"  data-bs-toggle="modal" data-bs-target="#okkiPreviewImageModal" class="col-12 rounded-5 image-media bg-body-secondary border border-dark-subtle w-100" style="height:auto;aspect-ratio:3/2;object-fit:cover;cursor: pointer;" data-src="' . $render['content'] . '"/>
                            </div>
                        </div>
                    </div>'; 
                    break;
                case "youtube": $html .= 
                    '<div class="col-12 mb-4">
                        
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="position-relative border-dark-subtle" style="max-width:700px;width:100%;height:auto;aspect-ratio:3/2;">
                                <div id="preloader-' . $render['tag_uuid'] . '" class="w-100 bg-body-secondary position-absolute rounded-5 d-flex align-items-center justify-content-center" style="height:auto;aspect-ratio:3/2;rounded-5">
                                    <i class="bi bi-youtube display-1"></i>
                                </div>
                                ' . youtubeMediaConverter($render['content'],$render) . '
                            </div>
                        </div>
                    </div>'; 
                    break;
                case "code-preview": $html .= 
                    '<div class="col-12">
                        <div class="bg-body-secondary border rounded-4 p-3 mx-auto" style="max-width:800px;width:100%;">
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
                                <h6 class="m-0 p-0 text-body">Code</h6>
                                <button onclick="okkiCopyCode(\'code_' . $render["tag_uuid"] . '\')" class="btn btn-secondary rounded-4" style="font-size:13px;"><i class="bi bi-copy me-2"></i>Copy</button>
                            </div>
                            <span class="font-monospace m-0 p-0" style="font-size:14px;" id="code_' . $render["tag_uuid"] . '"><pre class="m-0 p-0">' . nl2br(htmlspecialchars($render['content'])) . "</pre></span>
                        </div>
                    </div>";
                    break;
            }
        } else {
            if($render['tag']==="ul") {
                $html .= '<div class="col-12"><ul class="d-flex flex-column gap-4">';
                foreach($render['contents'] as $display) {
                    $html .= '<li class="mx-lg-4 ms-2">' . nl2br($display['content']) . '</li>';
                }
                $html .= '</ul></div>';
            } else if($render['tag']==="ol") {
                $html .= '<div class="col-12"><ol class="d-flex flex-column gap-4">';
                foreach($render['contents'] as $display) {
                    $html .= '<li class="mx-lg-4 ms-2">' . nl2br($display['content']) . '</li>';
                }
                $html .= '</ol></div>';
            } else if($render['tag']==="image") {
                $html .= '<div class="col-12"><div class="row">';
                foreach($render['contents'] as $display) {
                    $html .= '<div class="col-xl-2 col-sm-3 col-4 my-4">
                                <div class="w-100 h-auto bg-body-secondary rounded-5 position-relative border border-dark-subtle" style="aspect-ratio:1/1;">
                                    <div id="preloader-' . $display['component_uuid'] . '" class="w-100 h-auto position-absolute d-flex align-items-center justify-content-center bg-body-secondary rounded-5" style="aspect-ratio:1/1">
                                        <i class="bi bi-image-fill display-3"></i>
                                    </div>
                                    <img onclick="okkiPreviewImage(\'' . $display["content"] . '\')" 
                                        class="image-media w-100 h-auto rounded-5" 
                                        data-bs-toggle="modal" data-bs-target="#okkiPreviewImageModal" 
                                        style="aspect-ratio:1/1;cursor:pointer;object-fit:cover;" 
                                        data-preloader="preloader-' . $display['component_uuid'] . '"
                                        data-src="' . $display['content'] . '"/>
                                </div>
                            </div>';
                }
                $html .= '</div></div>';
            }
        }
    }
    $html .= '</div>';
    $html .= '</div>';
    function getScrollSpyNavItems(array $firstArray): array {
        $levelMap = [
            "header"    => 2,
            "header-3"  => 3,
            "header-4"  => 4,
            "header-5"  => 5,
            "header-6"  => 6,
        ];
        $navItems = [];

        foreach ($firstArray as $render) {
            if ($render['type'] !== "single" || !isset($levelMap[$render['tag']]) || empty($render['tag_uuid'])) {
                continue;
            }

            $level = $levelMap[$render['tag']];
            $id    = "header_" . htmlspecialchars($render['tag_uuid']);
            $title = strip_tags(nl2br($render['content']));

            $navItems[] = [
                'id'    => $id,
                'title' => $title,
                'level' => $level,
            ];
        }

        return $navItems;
    }

    function renderScrollSpyNav(array $navItems): string {
        if (empty($navItems)) {
            return '';
        }

        $nav  = '<nav id="simple-list-example" class="h-100 flex-column align-items-stretch w-100 pe-2" style="font-size:1rem;">';
        $nav .= '<nav class="nav nav-pills flex-column position-sticky border-end" style="top:130px;">';

        foreach ($navItems as $item) {
            $extraClass = $item['level'] > 2 ? 'ms-0 my-1' : '';
            $nav .= '<a class="nav-link rounded-4 link-body-emphasis opacity-75 ' . $extraClass . '" style="font-size:14px;" href="#' . $item['id'] . '">'
                . $item['title'] . '</a>';
        }

        $nav .= '</nav></nav>';

        return $nav;
    }

    function renderNavForModalForm(array $navItems):string {
        if (empty($navItems)) {
            return '';
        }

        $nav  = '<nav class="h-100 flex-column align-items-stretch w-100">';
        $nav .= '<nav class="nav nav-pills flex-column">';

        foreach ($navItems as $item) {
            $nav .= '<a onclick="okkiHideModalHeaderSection()" class="nav-link rounded-4 p-0 m-0 mb-3 link-body-emphasis" href="#' . $item['id'] . '">'
                . $item['title'] . '</a>';
        }

        $nav .= '</nav></nav>';

        return $nav;

    }

    $navItems = getScrollSpyNavItems($firstArray);
    $navbar  = renderScrollSpyNav($navItems);
    $navbarMobile = renderNavForModalForm($navItems);
?>

<div data-bs-theme="dark" class="modal fade" id="okkiHeaderSectionModal" tabindex="-1" aria-labelledby="okkiHeaderSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 rounded-5 p-3 text-body">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php echo $navbarMobile;?>
            </div>
        </div>
    </div>
</div>

<script>
    const okkiHideModalHeaderSection = () => {
        const myModalEl = document.getElementById('okkiHeaderSectionModal');
        const myModal = bootstrap.Modal.getInstance(myModalEl) || new bootstrap.Modal(myModalEl);
        myModal.hide();
    }
</script>

<div data-bs-theme="dark" class="modal fade" id="okkiPreviewImageModal" tabindex="-1" aria-labelledby="okkiPreviewImageModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 d-flex justify-content-end">
                <button type="button" class="btn btn-light rounded-circle py-2" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body d-flex align-items-center justify-content-center">
                <img id="okkiPreviewImageSrc" class="img-fluid rounded-5 bg-body-secondary" style="width:100%;" src="/source/image/600x400.svg" alt=""/>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php if($navbar) { ?>
        <div class="col-md-3 order-md-1 d-md-block d-none position-relative">
            <?php echo $navbar;?>
            <?php //echo strlen($navbar); ?>
        </div>
        <div class="col-md-9 order-md-2">
            <div data-bs-spy="scroll" data-bs-target="#simple-list-example" data-bs-offset="130" data-bs-smooth-scroll="true" class="scrollspy-example" tabindex="0">
                <?php echo $html; ?>
            </div>
        </div>
    <?php } else { ?>
        <div class="col-12">
            <?php echo $html; ?>
        </div>
    <?php } ?>
</div>
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast bg-dark text-white" role="alert" aria-live="assertive" aria-atomic="true"
        style="max-width:200px;">
        <div class="toast-body text-center">
            Скопировано
        </div>
    </div>
</div>
<script>
    const okkiCopyCode = (query) => {
        const copyText = document.getElementById(query);
        if (!copyText) return console.error('Element not found');
        const selection = window.getSelection();
        const range = document.createRange();
        range.selectNodeContents(copyText);
        selection.removeAllRanges();
        selection.addRange(range);
        const toastBootstrap = bootstrap.Toast.getOrCreateInstance(document.getElementById("liveToast"))
        try {
            navigator.clipboard.writeText(copyText.textContent || copyText.innerText);
            toastBootstrap.show()
        } catch (err) {
            console.error('Failed to copy text: ', err);
        } finally {
            selection.removeAllRanges();
        }
    };
    const okkiPreviewImage = (src) => document.getElementById("okkiPreviewImageSrc").src=src;
</script>
<script>
    window.addEventListener("load",() => {
        const list = [".youtube-media",".image-media"];
        list.map(render=>{
            document.querySelectorAll(render).forEach(block=>{
                if (block && block.dataset.src) {
                    block.src = block.dataset.src;
                    delete block.dataset.src;
                }
                block.addEventListener("load",()=>{
                    const idPreloader = block.dataset.preloader;
                    const htmlBlock = document.getElementById(idPreloader);
                    htmlBlock && htmlBlock.classList.toggle("d-none");
                });
            });
        });
    });
</script>