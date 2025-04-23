<?php if($user_auth_uuid!==null && $user_auth_uuid === $user['uuid']): ?>
<div data-bs-theme="dark" class="modal fade" id="articalEditModal" tabindex="-1"
    aria-labelledby="articalEditModallLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header border-0 py-2">
                <h1 class="modal-title fs-5 text-white" id="articalEditModallLabel"><i
                        class="bi bi-pencil-square me-3"></i>Editor Mode</h1>
                <div class="ms-4">
                    <button type="button" class="btn btn-outline-light border-0 d-flex gap-2 rounded-5" data-bs-toggle="modal"
                        data-bs-target="#articalPreviewModal" onclick="switchArticleMode('Preview')">
                        <i class="bi bi-easel2"></i>
                        <span class="d-none d-md-block">Preview</span>
                    </button>
                </div>
                <div class="ms-3">
                    <button type="button" class="btn btn-outline-light border-0 d-flex gap-2 justify-content-center rounded-5"
                        onclick="toggleFullscreen()">
                        <i class="bi bi-fullscreen"></i>
                        <span class="d-none d-md-block">Full screen<span>
                    </button>
                </div>
                <button onclick="showTopic()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 m-0 position-relative">
                <div id="preloaderEditModalRender" class="d-none w-100 rounded-4 bg-body-secondary position-absolute d-flex align-items-center justify-content-center" style="height:calc(100% - 8px);">
                    <div class="spinner-grow text-light" role="status" style="width: 6rem; height: 6rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <iframe id="arcticalEditModalRender" class="w-100 rounded-4" style="height: calc(100% - 8px);"></iframe>
                <input type="hidden" id="article-edit-uuid" value="" />
            </div>
        </div>
    </div>
</div>
<div data-bs-theme="dark" class="modal fade" id="articalPreviewModal" tabindex="-1"
    aria-labelledby="articalPreviewModallLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-secondary">
            <div class="modal-header border-0 py-2">
                <h1 class="modal-title fs-5 text-white" id="articalPreviewModallLabel"><i
                        class="bi bi-easel2 me-3"></i>Preview Mode</h1>
                <div class="ms-4">
                    <button type="button" class="btn btn-outline-light border-0 d-flex gap-2 rounded-5" data-bs-toggle="modal"
                        data-bs-target="#articalEditModal" onclick="switchArticleMode('Edit')">
                        <i class="bi bi-pencil-square"></i>
                        <span class="d-none d-md-block">Edit</span>
                    </button>
                </div>
                <div class="ms-3">
                    <button type="button" class="btn btn-outline-light border-0 d-flex gap-2 justify-content-center rounded-5"
                        onclick="toggleFullscreen()">
                        <i class="bi bi-fullscreen"></i>
                        <span class="d-none d-md-block">Full screen<span>
                    </button>
                </div>
                <button onclick="showTopic()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 m-0">
                <div id="preloaderPreviewModalRender" class="w-100 rounded-4 bg-body-secondary d-none" style="height:calc(100% - 8px);"></div>
                <iframe id="arcticalPreviewModalRender" class="w-100 rounded-4"
                    style="height: calc(100% - 8px);"></iframe>
                <input type="hidden" id="article-preview-uuid" value="" />
            </div>
        </div>
    </div>
</div>
<div data-bs-theme="dark" class="modal fade" id="deleteTopicModal" tabindex="-1" aria-labelledby="deleteTopicModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered px-md-5">
        <div class="modal-content text-white border-0 rounded-5 p-2">
            <div class="modal-header pb-2 border-0">
                <h1 class="modal-title fs-3" id="deleteTopicModalLabel">Delete</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Do you confirm delete the topic?
                <div class="w-100 mt-4">
                    <div class="row">
                        <div class="col-6">
                            <input id="delete-topic-uuid" type="hidden" name="article-uuid" value="" />
                            <button data-bs-dismiss="modal" onclick="deleteTopic()" type="button"
                                class="btn btn-danger w-100">Delete</button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn text-light w-100">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const deleteTopic = async (event) => {
        const uuid = document.getElementById("delete-topic-uuid").value;
        // Проверка на пустое значение
        if (!uuid) return;
        try {
            // Отправка DELETE-запроса на сервер
            const deleteReq = await fetch('/backend/delete-topic.php', {
                method: 'DELETE', // Указываем метод DELETE
                headers: {
                    'Content-Type': 'application/json' // Отправляем данные как JSON
                },
                body: JSON.stringify({
                    article: uuid
                }) // Отправляем UUID в теле запроса
            });
            // Проверка статуса ответа
            if (!deleteReq.ok) {
                throw new Error('Ошибка при удалении темы');
            }
            // Ожидание и преобразование ответа в формат JSON
            const deleteRes = await deleteReq.json();
            // Логирование результата
            console.log(deleteRes);
            if (deleteRes.status === "success") {
                showTopic(); // Обновление темы после удаления
            }
        } catch (error) {
            // Обработка ошибок
            console.error('Ошибка при удалении:', error);
        }
    };
    const deleteTopicUUID = (uuid) => document.getElementById("delete-topic-uuid").value = uuid;
</script>
<script>
    const addNewTopic = async (event) => {
        event.preventDefault();
        try {
            const loadingModal = new bootstrap.Modal(document.getElementById("loadingModal"));
            const articleCreateModal = bootstrap.Modal.getOrCreateInstance(document.getElementById("articleCreateBlockModal"));
            articleCreateModal.hide();
            loadingModal.show();
            const formData = new FormData();
            formData.append("uuid", crypto.randomUUID());
            formData.append("title", event.target[0].value);
            formData.append("status", event.target[1].value);
            formData.append("description", event.target[2].value);
            const res = await fetch("/backend/add-topic.php", {
                method: "POST",
                body: formData
            });
            const data = await res.json();
            if (data.status === "success") {
                countStatistics();
                showTopic();
                loadPage("Edit", data.uuid);
                // Закрываем модальное окно корректно
                loadingModal.hide();
                // Открываем другое окно
                new bootstrap.Modal(document.getElementById("articalEditModal")).show();
            }
        } catch (error) {
            console.error(error);
        }
    };
    const loadPage = (type, url) => {
        const articlePage = document.getElementById(`arctical${type}ModalRender`);
        const preloader = document.getElementById(`preloader${type}ModalRender`);
        preloader.classList.remove("d-none");
        articlePage.src =
            `/user/${type === "Preview" ? "show" : "write"}?article=${url}`;
        document.getElementById(`article-${type.toLowerCase()}-uuid`).value = url;
        articlePage.addEventListener("load",()=>{
            preloader.classList.add("d-none");
        });
    };
    const toggleFullscreen = () => {
        if (document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement ||
            document.msFullscreenElement) {
            // Если в полноэкранном режиме, выходим
            document.exitFullscreen ?.() ||
                document.mozCancelFullScreen ?.() ||
                document.webkitExitFullscreen ?.() ||
                document.msExitFullscreen ?.();
        } else {
            // Если не в полноэкранном режиме, входим
            document.documentElement.requestFullscreen ?.() ||
                document.documentElement.mozRequestFullScreen ?.() ||
                document.documentElement.webkitRequestFullscreen ?.() ||
                document.documentElement.msRequestFullscreen ?.();
        }
    };
    const switchArticleMode = (mode) => {
        const uuid = document.getElementById(`article-${mode === "Preview" ? "edit" : "preview"}-uuid`).value;
        loadPage(mode, uuid);
        showTopic();
    };
    const hashChanged = () => {
        if (window.location.hash === "#createArticle") {
            history.replaceState(null, "", window.location.pathname);
            const modal = new bootstrap.Modal(document.getElementById("articleCreateBlockModal"));
            modal.show();
        }
    }
    window.addEventListener("load", hashChanged);
    window.addEventListener("hashchange", hashChanged);
</script>
<div data-bs-theme="dark" class="modal fade" id="articleCreateBlockModal" tabindex="-1" aria-labelledby="articleInfoBlockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered px-md-5">
        <div class="modal-content border-0 rounded-5 p-2 text-white">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-3" id="articleInfoBlockModalLabel">Create article</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form onsubmit="addNewTopic(event)" class="modal-body">
                <div class="mb-4">
                    <label for="articleInfoTitle" class="form-label">Title</label>
                    <input type="text" class="form-control rounded-4 bg-body-secondary" id="articleInfoTitle" placeholder="New title" required/>
                </div>
                <div class="mb-4">
                    <label for="articleInfoStatus" class="form-label">View mode</label>
                    <select class="form-select rounded-4 bg-body-secondary" id="articleInfoStatus">
                        <option value="0">Public</option>
                        <option value="1">Private</option>
                        <option value="2">Link</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="articleInfoDesc" class="form-label">Description</label>
                    <textarea class="form-control bg-body-secondary rounded-4" placeholder="New description" id="articleInfoDesc" style="height: 100px"></textarea>
                </div>
                <button type="submit" class="w-100 btn btn-primary py-2 rounded-4">Save</button>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?php include_once "components/follow-modal.html"; ?>