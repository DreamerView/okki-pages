<?php 
    require_once $_SERVER["DOCUMENT_ROOT"] . "/backend/auth-on-frontend.php";
    if (empty($_GET['login']) || trim($_GET['login']) === '') {
        http_response_code(404);
        include_once $_SERVER["DOCUMENT_ROOT"] . "/server/error-404.php";
        exit;
    }
    $get = $_GET['login'];
    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    $db = new SimpleDB();
    include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
    $user = $db->fetch(
        "SELECT AES_DECRYPT(`fullname`, ?) AS `fullname`,`login`,`uuid` FROM `users` WHERE `login` = ?", 
        [GLOBAL_ENCRYPT_KEY,$get]
    ); 
    
    if (!$user) {
        http_response_code(404);
        include_once $_SERVER["DOCUMENT_ROOT"] . "/server/error-404.php";
        exit;
    }

    $verify = $db->fetch("SELECT `uuid` FROM `follow_system` WHERE `author`=? AND `follower`=?",[$user['uuid'],$user_auth_uuid]);

    $follow = isset($verify) ? true: false;

    $db->close();
?>
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
    
    <?php include_once "modal.php"; ?>

    <div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="loadingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm d-flex flex-column align-items-center; justify-content-center">
            <div data-bs-theme="dark" class="modal-content rounded-5" style="background: hsla(0, 0%, 0%, 0.5)!important;backdrop-filter: blur(10px);-webkit-backdrop-filter: blur(10px);height:200px;max-width:200px">
                <div class="modal-body" id="startLoading">
                    <div class="d-flex flex-column align-items-center justify-content-center h-100">
                        <div class="spinner-border text-light" style="width: 6rem; height: 6rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container" style="max-width: 1100px;width:100%;">
        <div class="row mt-md-5 mt-4 align-items-center">
            <div class="d-sm-none">
                <p class="fs-4 fw-light text-center">@<?php echo $user['login']; ?><i class="bi bi-patch-check-fill text-primary ms-2"></i></p>  
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                <div class="rounded-circle h-auto bg-body-secondary mx-auto" style="max-width:150px;aspect-ratio:1/1">
                    <img loading="lazy" class="w-100 h-auto rounded-circle" style="object-fit:cover;" src="/backend/image.php?fullname=<?php echo $user['fullname'];?>&&size=150" alt=""/>
                </div>
            </div>
            <div class="col-lg-9 col-md-8 col-sm-6 gap-4 my-sm-0 my-4 col-12 d-flex flex-column">
                <p class="fs-4 fw-light d-sm-block d-none m-0">@<?php echo $user['login']; ?><i class="bi bi-patch-check-fill text-primary ms-2"></i></p>
                <div class="row mx-auto mx-sm-0 order-sm-1 order-2" style="max-width:300px;width:100%">
                    <div class="col-4 px-0">
                        <h6 class="m-0 text-center text-sm-start"><span id="allUserCreatedPages">0</span> pages</h6>
                    </div>
                    <div class="col-4 px-0">
                        <h6 style="cursor:pointer" onclick="viewCountOfFollowSystem('<?php echo $user['uuid'];?>','author')" data-bs-toggle="modal" data-bs-target="#userFollowListModal" class="m-0 text-center text-sm-start"><span id="allUserFollower">0</span> reader</h6>
                    </div>
                    <div class="col-4 px-0">
                        <h6 style="cursor:pointer" onclick="viewCountOfFollowSystem('<?php echo $user['uuid'];?>','follower')" data-bs-toggle="modal" data-bs-target="#userFollowListModal" class="m-0 text-center text-sm-start"><span id="allUserFollowing">0</span> read</h6>
                    </div>
                </div>
                <h5 class="text-center text-sm-start order-sm-2 order-1 m-0"><?php echo $user['fullname'];?></h5>
                <?php if($user_auth_uuid!==null && $user_auth_uuid !== $user['uuid']) : ?>
                <div class="order-3 d-flex justify-content-center justify-content-sm-start">
                    <div id="loadingButton" class="spinner-border d-none" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <button id="followButton" onclick="toggleFollow('<?php echo $user['uuid'];?>','follow')" class="btn btn-primary rounded-5 <?php echo $follow===false?'':'d-none';?>">Follow</button>
                    <button id="unFollowButton" onclick="toggleFollow('<?php echo $user['uuid'];?>','unfollow')" class="btn btn-secondary rounded-5 <?php echo $follow===false?'d-none':'';?>">Following</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        const countStatistics = async () => {
            try {
                const req = await fetch("backend/follow-system/count-user-statistics.php?uuid=<?php echo $user['uuid'];?>");
                const res = await req.json();
                console.log(res);

                if (res.status !== "success") return;

                const stats = {
                    allUserCreatedPages: res.page,
                    allUserFollower: res.follower,
                    allUserFollowing: res.following
                };

                Object.entries(stats).forEach(([id, value]) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = value;
                });

            } catch (error) {
                console.error(error);
            }
        };

        const toggleFollow = async (author, action) => {

            const loadingBtn = document.getElementById("loadingButton");

            try {
                const followBtn = document.getElementById("followButton");
                const unfollowBtn = document.getElementById("unFollowButton");

                if (!followBtn || !unfollowBtn) return;

                const button = action === "follow" ? followBtn : unfollowBtn;
                const buttonReverse = action !== "follow" ? followBtn : unfollowBtn;
                loadingBtn.classList.toggle("d-none");
                button.classList.toggle("d-none");
                await new Promise(resolve => setTimeout(resolve, 500));
                const body = action === "follow" ? { uuid: self.crypto.randomUUID(), author } : { author };
                const req = await fetch(`backend/follow-system/${action}-to-author.php`, {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(body)
                });
                if (!req.ok) throw new Error(`Ошибка запроса: ${req.status} ${req.statusText}`);
                const res = await req.json();
                if (res.status === "success") {
                    countStatistics();
                    buttonReverse.classList.toggle("d-none");
                }

                button.disabled = false;
                console.log(res);
            } catch (error) {
                console.error("Ошибка запроса:", error);
            } finally {
                loadingBtn.classList.toggle("d-none");
            }
        };



    </script>
    <!--  -->
    <ul class="nav nav-underline gap-5 d-flex my-sm-5 my-2 w-100 justify-content-md-center justify-content-around px-4 px-md-0">
        <li class="nav-item">
            <button id="sticky" onclick="userNavChange('sticky')" type="button"  class="nav-link active d-flex gap-2 navFont"><i class="nav-icon bi bi-sticky-fill"></i><span class="d-md-flex d-none">Article</span></button>
        </li>
        <?php if($user_auth_uuid!==null && $user_auth_uuid === $user['uuid']) : ?>
        <li class="nav-item">
            <button id="lock" onclick="userNavChange('lock')" type="button"  class="nav-link d-flex gap-2 navFont"><i class="nav-icon bi bi-lock"></i><span class="d-md-flex d-none">Private</span></button>
        </li>
        <?php endif ;?>
        <li class="nav-item">
            <button id="stickies" onclick="userNavChange('stickies')" type="button" class="nav-link d-flex gap-2 navFont"><i class="nav-icon bi bi-stickies"></i><span class="d-md-block d-none">Grouped</span></button>
        </li>
        <li class="nav-item">
            <button id="reply-all" onclick="userNavChange('reply-all')" type="button" class="nav-link d-flex gap-2 navFont"><i class="nav-icon bi bi-reply-all"></i><span class="d-md-block d-none">Reposted</span></button>
        </li>
        <?php if($user_auth_uuid!==null && $user_auth_uuid === $user['uuid']) : ?>
        <li class="nav-item">
            <button id="bookmark" onclick="userNavChange('bookmark')" type="button" class="nav-link d-flex gap-2 navFont"><i class="nav-icon bi bi-bookmark"></i><span class="d-md-block d-none">Saved</span></button>
        </li>
        <?php endif ;?>
    </ul>
    <script>
        const userNavChange = (nav) => {
            document.querySelectorAll(".userActiveBlocks, .nav-link, .nav-icon").forEach(el => {
                if (el.classList.contains("userActiveBlocks")) {
                    el.classList.add("d-none");
                }
                if (el.classList.contains("nav-link")) {
                    el.classList.remove("active");
                }
                if (el.classList.contains("nav-icon")) {
                    el.className = el.className.replace("-fill", "");
                    if (el.className.includes(nav)) {
                        el.className = el.className.replace(nav, nav + "-fill");
                    }
                }
            });

            document.getElementById(nav)?.classList.add("active");
            document.getElementById(`${nav}-block`)?.classList.remove("d-none");
        };

    </script>
    <!--  -->
    <div class="container userActiveBlocks my-5" id="sticky-block">
        <div class="row" id="renderTopics">
            
        </div>
    </div>
    <div class="container userActiveBlocks my-5 d-none" id="lock-block">
        <div style="max-width:200px;width:100%;" class="my-4 mx-auto">
            <select name="" id="" class="form-select bg-body-secondary rounded-4">
                <option value="private" select>Private</option>
                <option value="link">Link</option>
            </select>
        </div>
        <div class="row" id="renderTopicsPrivate">
            
        </div>
    </div>
    <div class="container userActiveBlocks my-5 d-none" id="reply-all-block">
        <div class="row" id="renderReposts">
            
        </div>
    </div>
    <div class="container userActiveBlocks my-5 d-none" id="bookmark-block">
        <div class="row" id="renderSaved">
            
        </div>
    </div>
    <script>
        const truncateText = (text, limit) => text.length > limit ? `${text.slice(0, limit)}...` : text;

        const showTopic = async () => {
            try {
                const renderTopics = document.getElementById("renderTopics");
                const firstBlock = document.createElement("div");
                firstBlock.id = "firstBlock";

                // Прелоадер
                renderTopics.innerHTML = new Array(8).fill(`
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 position-relative my-3">
                        <div class="rounded-4 bg-body-secondary w-100 h-auto position-relative d-flex align-items-center justify-content-center articleRatioBlock"></div>
                    </div>
                `).join("");

                const res = await fetch("/backend/user-topics.php?login=<?php echo $get;?>");
                const data = await res.json();

                showRepost();
                showSaved();
                showTopicPrivate();
                countStatistics();

                if (data.topic !== 1) return renderTopics.innerHTML = ""; // Если нет тем, очищаем и выходим

                if (data.data.length === 0 && !data.edit) return 
                    renderTopics.innerHTML = `
                        <div class="col-8 mt-5 mx-auto text-center">
                            <h4>No articles</h4>
                            <p>User not published anything</p>
                        </div>
                    `;

                const fragment = document.createDocumentFragment();
                fragment.appendChild(firstBlock);

                data.data.forEach(render => {
                    const article = document.createElement("div");
                    article.className = "col-xl-3 col-lg-4 col-md-6 col-12 position-relative my-3";
                    article.innerHTML = `
                        <div class="position-absolute" style="z-index:4;right:24px;top:12px;">
                            <div class="dropdown">
                                <button class="btn btn-light rounded-circle py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu px-1 py-2 rounded-4" data-bs-theme="dark">
                                    ${data.edit ? `
                                    <li><button class="dropdown-item rounded-4" onclick="loadPage('Edit','${render.uuid}')" data-bs-toggle="modal" data-bs-target="#articalEditModal"><i class="bi bi-pencil opacity-50 me-2"></i>Edit</button></li>
                                    <li><button class="dropdown-item rounded-4" onclick="deleteTopicUUID('${render.uuid}')" data-bs-toggle="modal" data-bs-target="#deleteTopicModal"><i class="bi bi-trash2 opacity-50 me-2"></i>Delete</button></li>
                                    <li><a href="/a/@${render.uuid}" target="_blank" class="dropdown-item rounded-4"><i class="bi bi-eye opacity-50 me-2"></i>Full preview</a></li>` : ``}
                                    <li><button class="dropdown-item rounded-4"><i class="bi bi-share opacity-50 me-2"></i>Share</button></li>
                                </ul>
                            </div>
                        </div>
                        <a ${data.edit ? `onclick="clickPreventDefault(event,'${render.uuid}')"` : ``} href="/a/@${render.uuid}" ${data.edit ?` data-bs-toggle="modal" data-bs-target="#articalEditModal"`:``} data-bs-theme="dark">
                            <div class="rounded-4 bg-body-secondary text-white w-100 h-auto position-relative articleRatioBlock">
                                <div class="w-100 h-auto position-absolute rounded-4 articleRatioBlock" style="z-index:2;background-color: hsla(0,0%,0%,0.6);backdrop-filter:blur(4px);-webkit-backdrop-filter: blur(4px);"></div>
                                <img loading="lazy" class="w-100 h-auto position-absolute rounded-4 articleRatioBlock" style="z-index:1;object-fit:cover;" src="/source/image/placeholder.webp" alt=""/>
                            </div>
                        </a>
                        <h5 class="lh mt-3">${truncateText(render.title,48)}</h5>
                        <p style="font-size:13px;" class="m-0 text-secondary">${render.date}</p>
                    `;
                    fragment.appendChild(article);
                });

                renderTopics.innerHTML = "";
                renderTopics.appendChild(fragment);
            } catch (error) {
                console.error(error);
            }
        };


        const showTopicPrivate = async () => {
            try {
                const renderTopicsPrivate = document.getElementById("renderTopicsPrivate");
                if (!renderTopicsPrivate) return;

                // Показываем прелоадер
                const preloaderHTML = Array.from({ length: 8 }, () => `
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 position-relative my-3">
                        <div class="rounded-4 bg-body-secondary w-100 h-auto position-relative d-flex align-items-center justify-content-center articleRatioBlock">
                        </div>
                    </div>
                `).join("");

                requestAnimationFrame(() => (renderTopicsPrivate.innerHTML = preloaderHTML));

                // Загружаем данные
                const res = await fetch("/backend/user-topics-private.php");
                const data = await res.json();

                if (!data.private) return; // Если нет тем, выходим

                // Генерируем HTML разметку
                const html = data.data.map(render => `
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 position-relative my-3">
                        <div class="position-absolute" style="z-index:4;right:24px;top:12px;">
                            <div class="dropdown">
                                <button class="btn btn-light rounded-circle px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    ${data.edit ? `
                                    <li><button class="dropdown-item" onclick="loadPage('Edit','${render.uuid}')" data-bs-toggle="modal" data-bs-target="#articalEditModal">
                                        <i class="bi bi-pencil me-2"></i>Edit</button></li>
                                    <li><button class="dropdown-item" onclick="deleteTopicUUID('${render.uuid}')" data-bs-toggle="modal" data-bs-target="#deleteTopicModal">
                                        <i class="bi bi-trash2 me-2"></i>Delete</button></li>` : ``}
                                    <li><button class="dropdown-item"><i class="bi bi-share me-2"></i>Share</button></li>
                                </ul>
                            </div>
                        </div>
                        <a ${data.edit ? `onclick="clickPreventDefault(event)"` : ``} href="/a/@${render.uuid}" data-bs-theme="dark">
                            <div class="rounded-4 bg-body-secondary text-white w-100 h-auto position-relative articleRatioBlock">
                                <div class="w-100 h-auto position-absolute rounded-4 articleRatioBlock" style="z-index:2;background-color: hsla(0,0%,0%,0.6);backdrop-filter:blur(4px);-webkit-backdrop-filter: blur(4px);"></div>
                                <img loading="lazy" class="w-100 h-auto position-absolute rounded-4 articleRatioBlock" style="z-index:1;" src="/source/image/placeholder.webp" alt=""/>
                                <div class="position-absolute p-3" style="z-index:3;bottom:0">
                                    <h4>${render.title}</h4>
                                    <p style="font-size:13px;" class="text-light m-0">${render.date}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                `).join("");

                // Если данных нет
                if (data.data.length === 0 && !data.edit) {
                    renderTopicsPrivate.innerHTML = `
                        <div class="col-8 mt-5 mx-auto text-center">
                            <h4>No privates</h4>
                            <p>Privates not found</p>
                        </div>`;
                } else {
                    renderTopicsPrivate.innerHTML = html;
                }

            } catch (error) {
                console.error("Error loading private topics:", error);
            }
        };


        const showRepost = async () => {
            try {
                const renderReposts = document.getElementById("renderReposts");
                renderReposts.innerHTML = Array(8).fill(`
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 position-relative my-3">
                        <div class="rounded-4 bg-body-secondary w-100 h-auto position-relative d-flex align-items-center justify-content-center articleRatioBlock"></div>
                    </div>
                `).join("");

                const res = await fetch("/backend/user-reposts.php?login=<?php echo $get;?>");
                const data = await res.json();
                console.log(data);

                if (!data.repost || !data.data.length) {
                    renderReposts.innerHTML = `
                        <div class="col-8 mt-5 mx-auto text-center">
                            <h4>No reposts</h4>
                            <p>User has not reposted anything</p>
                        </div>
                    `;
                    return;
                }

                renderReposts.innerHTML = data.data.map(render => `
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 position-relative my-3">
                        <a href="/a/@${render.uuid}" data-bs-theme="dark">
                            <div class="rounded-4 bg-body-secondary text-white w-100 h-auto position-relative articleRatioBlock">
                                <div class="w-100 h-auto position-absolute rounded-4 articleRatioBlock" 
                                    style="z-index:2;background-color: hsla(0,0%,0%,0.6);backdrop-filter:blur(4px);"></div>
                                <img loading="lazy" class="w-100 h-auto position-absolute rounded-4 articleRatioBlock" 
                                    style="z-index:1;" src="/source/image/placeholder.webp" alt=""/>
                                <div class="position-absolute p-3" style="z-index:3;bottom:0">
                                    <h4>${render.title}</h4>
                                    <p style="font-size:13px;" class="text-light m-0">${render.date}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                `).join("");
            } catch (error) {
                console.error(error);
            }
        };


        const showSaved = async () => {
            try {
                const renderSaved = document.getElementById("renderSaved");
                renderSaved.innerHTML = Array(8).fill(`
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 position-relative my-3">
                        <div class="rounded-4 bg-body-secondary w-100 h-auto position-relative d-flex align-items-center justify-content-center articleRatioBlock"></div>
                    </div>
                `).join("");

                const res = await fetch("/backend/user-saved.php");
                const data = await res.json();
                console.log(data);

                if (!data.saved || !data.data.length) {
                    renderSaved.innerHTML = `
                        <div class="col-8 mt-5 mx-auto text-center">
                            <h4>No saved</h4>
                            <p>You haven't saved anything</p>
                        </div>
                    `;
                    return;
                }

                renderSaved.innerHTML = data.data.map(render => `
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 position-relative my-3">
                        <a ${data.edit ? `onclick="clickPreventDefault(event)"` : ""} href="/a/@${render.uuid}" data-bs-theme="dark">
                            <div class="rounded-4 bg-body-secondary text-white w-100 h-auto position-relative articleRatioBlock">
                                <div class="w-100 h-auto position-absolute rounded-4 articleRatioBlock" 
                                    style="z-index:2;background-color: hsla(0,0%,0%,0.6);backdrop-filter:blur(4px);"></div>
                                <img loading="lazy" class="w-100 h-auto position-absolute rounded-4 articleRatioBlock" 
                                    style="z-index:1;" src="/source/image/placeholder.webp" alt=""/>
                                <div class="position-absolute p-3" style="z-index:3;bottom:0">
                                    <h4>${render.title}</h4>
                                    <p style="font-size:13px;" class="text-light m-0">${render.date}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                `).join("");
            } catch (error) {
                console.error(error);
            }
        };


        const clickPreventDefault = (event,uuid) => {
            loadPage("Edit",uuid);
            event.preventDefault();
        };

        window.addEventListener('load', () => showTopic());
    </script>
</body>

</html>