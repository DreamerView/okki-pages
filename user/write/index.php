<?php 
    require_once $_SERVER["DOCUMENT_ROOT"] . "/backend/auth-on-frontend.php";
    
    if(!isset($_GET['article'])) {
        http_response_code(404);
        include_once $_SERVER["DOCUMENT_ROOT"] . "/server/error-404.php";
        exit;
    }

    $get = $_GET['article'];
    
    if(!$user_auth_uuid) {
        http_response_code(403);
        include_once $_SERVER["DOCUMENT_ROOT"] . "/server/error-403.php";
        exit;
    }

    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    $db = new SimpleDB();

    $article = $db->fetch("SELECT `uuid` FROM `topics` WHERE `uuid`=? AND `author`=?",[$get,$user_auth_uuid]);

    if(!$article) {
        http_response_code(403);
        include_once $_SERVER["DOCUMENT_ROOT"] . "/server/error-403.php";
        exit;
    }

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <link rel="stylesheet" href="/source/style/bootstrap.min.css" />
    <link rel="stylesheet" href="/source/style/bootstrap-icons.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="/source/script/bootstrap.min.js" defer></script>
    <style>
        [contenteditable]:empty[data-placeholder]:before {
            content: attr(data-placeholder);
            color:#999;
        }

        [contenteditable]:not(:empty)[data-placeholder]:before {
            content: "";
        }
        td div {
            display: none;
        }

        td:hover div {
            display: block;
        }

        td {
            max-width: 1px; /* Ограничиваем ширину */
            white-space: normal; /* Позволяет переносить текст */
        }

        td span[contenteditable] {
            min-width: 100%; /* Гарантируем, что будет занимать всю ширину */
            word-break: break-word; /* Переносим длинные слова */
            overflow-wrap: break-word;
        }
    </style>
</head>

<body data-bs-theme="light">
    <?php include_once "modal.html"; ?>
    <div class="position-sticky w-100 d-flex gap-3 flex-wrap justify-content-center py-2 border-bottom border-dark-subtle bg-body-secondary" style="top:0; margin:0 auto;z-index:999;">
        <div class="bg-dark rounded-4 d-none d-md-flex">
            <button type="button" class="btn btn-dark rounded-5" onclick="toggleTextAlign('left')">
                <i class="bi bi-text-left"></i>
            </button>
            <button type="button" class="btn btn-dark rounded-5" onclick="toggleTextAlign('center')">
                <i class="bi bi-text-center"></i>
            </button>
            <button type="button" class="btn btn-dark rounded-5" onclick="toggleTextAlign('right')">
                <i class="bi bi-text-right"></i>
            </button>
            <button type="button" class="btn btn-dark rounded-5" onclick="toggleTextAlign('justify')">
                <i class="bi bi-justify"></i>
            </button>
        </div>
        <div class="bg-dark rounded-4 d-none d-md-flex">
            <button type="button" class="btn btn-dark rounded-5" onclick="toggleFormat('b')">
                <i class="bi bi-type-bold"></i>
            </button>
            <button type="button" class="btn btn-dark rounded-5" onclick="toggleFormat('i')">
                <i class="bi bi-type-italic"></i>
            </button>
            <button type="button" class="btn btn-dark rounded-5" onclick="toggleFormat('s')">
                <i class="bi bi-type-strikethrough"></i>
            </button>
            <button type="button" class="btn btn-dark rounded-5" onclick="toggleFormat('u')">
                <i class="bi bi-type-underline"></i>
            </button>
        </div>
        <div class="d-flex gap-3 px-3">
            <button id="stopMoveBlock" onclick="moveMode()" class="btn rounded-4 text-danger d-none">
                <i class="bi bi-x-octagon me-2"></i>Stop move
            </button>
            <div class="dropdown">
                <button id="moreButton" class="btn dropdown-toggle rounded-4" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    More
                </button>
                <ul class="dropdown-menu mt-1 fs-6 rounded-4" data-bs-theme="dark">
                    <li><button id="modeBlock" onclick="modeBlock('default','default')" data-bs-toggle="modal" data-bs-target="#moreInstrumentModal" class="dropdown-item rounded-4"><i class="bi bi-window-plus me-3"></i>Add</button></li>
                    <li><button onclick="moveMode()" class="dropdown-item rounded-4"><i class="bi bi-arrows-move"></i><span class="ms-3">Start move</span></li>
                    <li><button onclick="fetchArticleInfo()" class="dropdown-item rounded-4"><i class="bi bi-info-circle"></i><span class="ms-3">Article info</span></li>
                </ul>
            </div>
            <button onclick="buildArticle()" type="btn" class="btn btn-primary rounded-4">Сохранить<i class="bi bi-floppy2-fill ms-3"></i></button>
        </div>
    </div>
    <main class="container my-5" style="padding-bottom:64px;">
        <div class="row none preview">
            <div class="col-12">
                <textarea oninput="inputTextArea(event)" placeholder="Title of theme" data-component-uuid="111" name="title-page" id="title-page"
                    class="form-control fs-1 fw-bold border-0" rows="1" style="resize:none;"></textarea>
            </div>
        </div>
        <div class="row d-none preview">
            <div class="col-12">
                <h1 id="render-111"></h1>
            </div>
        </div>
        <div class="mt-4 d-flex flex-column gap-3" id="render">

        </div>
        <div id="addBlockOnBottom" class="row justify-content-center mt-5">
            <div class="col-md-8 col-10">
                <div onclick="modeBlock('default','default')" class="p-5 border-dark-subtle rounded-4 d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#moreInstrumentModal" style="cursor: pointer; border-style:dashed">
                    <i class="bi bi-plus-circle-dotted display-1 text-secondary"></i>
                </div>
            </div>
        </div>
    </main>
    <div id="tooltip" class="position-absolute bg-dark text-white rounded-3 d-none gap-1 p-1" data-bs-theme="dark">
        <button type="button" class="btn text-white" onclick="applyStyle('bold')"><i class="bi bi-type-bold"></i></button>
        <button type="button" class="btn text-white" onclick="applyStyle('italic')"><i class="bi bi-type-italic"></i></button>
        <button type="button" class="btn text-white" onclick="applyStyle('strikeThrough')"><i class="bi bi-type-strikethrough"></i></button>
        <button type="button" class="btn text-white" onclick="applyStyle('underline')"><i class="bi bi-type-underline"></i></button>
        <div class="border-start border-secondary"></div>
        <button type="button" class="btn text-white" onclick="applyStyle('justifyLeft')"><i class="bi bi-text-left"></i></button>
        <button type="button" class="btn text-white" onclick="applyStyle('justifyCenter')"><i class="bi bi-text-center"></i></button>
        <button type="button" class="btn text-white" onclick="applyStyle('justifyRight')"><i class="bi bi-text-right"></i></button>
        <div class="border-end border-secondary"></div>
        <select onchange="applyStyle('fontSize', this.value)" class="form-select bg-secondary">
            <option value="1">7.5 pt</option>
            <option value="2">10 pt</option>
            <option value="3" selected>12 pt</option>
            <option value="4">18 pt</option>
            <option value="5">24 pt</option>
            <option value="6">36 pt</option>
            <option value="7">54 pt</option>
        </select>
    </div>
    <?php include_once "cropper-js.html"; ?>
    <?php include_once "panel-js.html"; ?>
    <?php include_once "contenteditable-js.html"; ?>
    <?php include_once "markdown.html";?>
    <?php include_once "js.html"; ?>
    <?php include_once "build-js.html"; ?>
    <?php include_once "render-js.html"; ?>
</body>

</html>