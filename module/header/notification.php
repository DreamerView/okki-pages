<div class="offcanvas offcanvas-end m-md-5 p-md-3 rounded-5" data-bs-theme="dark" tabindex="-1" id="notificationBlock"
    aria-labelledby="notificationBlockLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fs-3" id="notificationBlockLabel">Notification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="renderNotificationContents">
    </div>
</div>
<script>
    let offset = 0;
    let isLoading = false;
    let hasMoreData = true;
    const renderHTML = document.getElementById("renderNotificationContents");
    // Создаем прелоадер
    const preloader = document.createElement("div");
    preloader.className = "text-center my-3";
    preloader.innerHTML = `
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;
    renderHTML.appendChild(preloader); // Добавляем его в конец контейнера
    const showNotification = async () => {
        if (isLoading || !hasMoreData) return;
        isLoading = true;
        preloader.style.display = "block"; // Показываем прелоадер
        try {
            const req = await fetch(`/backend/notification/list.php?offset=${offset}`);
            const res = await req.json();
            if (res['status'] !== "success") return;
            if (res['list'].length === 0) {
                hasMoreData = false;
                preloader.style.display = "none"; // Убираем прелоадер, если данных больше нет
                return;
            }
            res['list'].forEach(list => {
                const div = document.createElement("div");
                div.className = "row mb-4";
                div.innerHTML = `
                <div class="col-2">
                    <div class="w-100 h-auto rounded-circle bg-body-secondary" style="aspect-ratio: 1/1;">
                        <img src="${list['image']}" class="w-100 h-auto rounded-circle" style="aspect-ratio: 1/1; object-fit:cover;"/>
                    </div>
                </div>
                <a href="/@${list['login']}" class="link-body-emphasis text-decoration-none col-10">
                    <h6>${list['fullname']}</h6>
                    <p class="p-0 m-0 text-break" style="font-size:13px;">${list['placeholder']} <small class="ms-2 text-secondary">${list['date']}</small></p>
                </a>
            `;
                renderHTML.insertBefore(div, preloader);
            });
            offset += 7;
        } catch (error) {
            console.warn(error);
        } finally {
            isLoading = false;
            preloader.style.display = "none"; // Скрываем прелоадер после загрузки
        }
    };
    // Ленивая загрузка по скроллу контейнера
    renderHTML.addEventListener("scroll", () => {
        if (!hasMoreData) return;
        if (renderHTML.scrollTop + renderHTML.clientHeight >= renderHTML.scrollHeight - 50) {
            showNotification();
        }
    });
    // Загружаем первую порцию сразу
    showNotification();
</script>