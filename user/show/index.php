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
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Document</title>
    <link rel="stylesheet" href="/source/style/bootstrap.min.css" />
    <link rel="stylesheet" href="/source/style/bootstrap-icons.min.css" />
    <script src="/source/script/bootstrap.min.js" defer></script>
</head>
<body style="max-width:1200px; width:100%; margin:0 auto;">    
    <?php 
        if(isset($_GET['article'])) {
            $get = htmlspecialchars($_GET['article']);
            $fullPath = $_SERVER["DOCUMENT_ROOT"] . "/store/cache/" . $get . ".html";
            if(file_exists($fullPath)){
                $html = file_get_contents($fullPath);
                echo $html;
            } else {
                echo "Not found";
            }
        } else {
            echo "Article not found";
        }
    ?>
</body>
</html>