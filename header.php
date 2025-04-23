<div data-bs-theme="dark" class="modal fade" id="signProcessModal" tabindex="-1" aria-labelledby="signProcessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered px-md-5">
        <div class="modal-content border-0 rounded-5 p-3 text-body">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-3" id="signProcessModalLabel">Sign In<i
                        class="bi bi-box-arrow-in-right ms-2"></i></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <ul class="nav nav-underline w-100 mb-4">
                    <li class="nav-item">
                        <button id="signActiveIn" onclick="signType('in')" class="nav-link active"><i
                                class="bi bi-box-arrow-in-right me-2"></i>Sign In</button>
                    </li>
                    <li class="nav-item">
                        <button id="signActiveUp" onclick="signType('up')" class="nav-link"><i
                                class="bi bi-door-open me-2"></i>Sign Up</button>
                    </li>
                </ul>
                <form onsubmit="signInToSystem(event)" method="POST" action="/backend/sign-in.php" id="signInType">
                    <div id="alertSignIn">

                    </div>
                    <div class="mb-4">
                        <input class="form-control bg-body-secondary rounded-4 px-4 py-2" placeholder="Email" type="email" name=""
                            id="sign-in-email" />
                            
                    </div>
                    <div class="mb-3">
                        <input class="form-control bg-body-secondary show-password rounded-4 px-4 py-2" placeholder="Password"
                            type="password" name="" id="sign-in-pass" />
                    </div>
                    <div class="row mb-3">
                        <div class="col-6 d-flex align-items-center justify-content-center"><a href="#"
                                class="text-center">Forget password?</a></div>
                        <div class="col-6 d-flex align-items-center justify-content-center"><button type="button"
                                onclick="showPassword()" class="btn text-center text-secondary">Show password</button>
                        </div>
                    </div>
                    <button class="w-100 btn btn-primary rounded-4">Sign in</button>
                </form>
                <form onsubmit="signUpToSystem(event)" method="POST" action="/backend/sign-up.php" id="signUpType"
                    class="d-none">
                    <div id="alertSignUp">

                    </div>
                    <div class="mb-4">
                        <input class="form-control bg-body-secondary rounded-4 px-4 py-2" placeholder="Full name" type="text" name=""
                            id="sign-up-fullname" required />
                    </div>
                    <div class="mb-4">
                        <input class="form-control bg-body-secondary rounded-4 px-4 py-2" placeholder="Email" type="email" name=""
                            id="sign-up-email" required />
                    </div>
                    <div class="mb-3">
                        <input class="form-control bg-body-secondary show-password rounded-4 px-4 py-2" placeholder="Password"
                            type="password" name="" id="sign-up-pass" required />
                    </div>
                    <div class="mb-3">
                        <button type="button" onclick="showPassword()" class="btn w-100 text-center text-secondary">Show
                            password</button>
                    </div>
                    <button type="submit" class="w-100 btn btn-primary rounded-4">Sign up</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div data-bs-theme="dark" class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-body px-md-5">
        <div class="modal-content border-0 rounded-5 p-3">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-3" id="editUserModalLabel">Edit info</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                <div class="mb-4 d-flex justify-content-center">
                    <div class="rounded-circle bg-body-secondary" style="width:100px;height:100px;">
                        <img id="editUserAvatar" loading="lazy" class="w-100 h-100 rounded-cicle object-fit-cover" alt=""/>
                    </div>
                </div>
                <ul class="nav nav-underline w-100 mb-4">
                    <li class="nav-item">
                        <button type="button" id="editMainUserBtn" onclick="userEditType('main')" class="nav-link active"><i
                                class="bi bi-person-bounding-box me-2"></i>Main</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" id="editMainPasswordBtn" onclick="userEditType('password')" class="nav-link"><i
                                class="bi bi-key me-2"></i>Password</button>
                    </li>
                </ul>
                <form id="editMainUserUI" onsubmit="signUpToSystem(event)" method="POST" action="/backend/sign-up.php">
                    <div id="alertSignUp">

                    </div>
                    <div id="renderEditUserContent">

                    </div>
                    <button type="submit" class="w-100 btn btn-primary rounded-4 py-2">Edit</button>
                </form>
                <form class="d-none" id="editPasswordUserUI" onsubmit="changePassword(event)" method="POST" action="/backend/sign-up.php">
                    <div id="alertEditPassword">

                    </div>
                    <div id="renderPasswordUserContent">

                    </div>
                    <button type="submit" class="w-100 btn btn-primary rounded-4 py-2">Change</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    const signType = (type) => {
        const isIn = type === "in";
        document.getElementById("signProcessModalLabel").innerHTML = 
            `Sign ${isIn ? "In" : "Up"}<i class="bi ${isIn ? "bi-box-arrow-in-right" : "bi-door-open"} ms-2"></i>`;

        ["signActiveUp", "signActiveIn"].forEach(id => 
            document.getElementById(id).classList.toggle("active", id === `signActive${isIn ? "In" : "Up"}`)
        );

        ["signUpType", "signInType"].forEach(id => 
            document.getElementById(id).classList.toggle("d-none", id !== `sign${isIn ? "In" : "Up"}Type`)
        );
    };

    const userEditType = (type) => {
        const isMain = type === "main";
        const mainBtn = document.getElementById("editMainUserBtn");
        const passwordBtn = document.getElementById("editMainPasswordBtn");
        const mainUI = document.getElementById("editMainUserUI");
        const passwordUI = document.getElementById("editPasswordUserUI");

        // Кеширование DOM элементов и минимизация вызовов classList.toggle
        mainBtn.classList.toggle("active", isMain);
        passwordBtn.classList.toggle("active", !isMain);
        mainUI.classList.toggle("d-none", !isMain);
        passwordUI.classList.toggle("d-none", isMain);
    };


    const signInToSystem = async (event) => {
        event.preventDefault();

        const email = document.getElementById("sign-in-email")?.value.trim();
        const password = document.getElementById("sign-in-pass")?.value;

        if (!email || !password) return;

        try {
            const response = await fetch('/backend/sign-in.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });

            if (!response.ok) throw new Error(`Ошибка запроса: ${response.status} ${response.statusText}`);

            const data = await response.json();
            console.log(data);

            if (data.status === "error") {
                const errorMessages = {
                    "email-not-exist": "Email not exist! Try again!",
                    "password-error": "Password error! Try again!"
                };

                if (data.error in errorMessages) {
                    document.getElementById("alertSignIn").innerHTML = `
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>${errorMessages[data.error]}</strong> 
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                }
                return;
            }

            if (data.status === "success") {
                checkAuth();
                window.location.reload();
            }
        } catch (error) {
            console.error('Ошибка:', error);
            alert('Ошибка отправки данных');
        }
    };


    const signUpToSystem = async (event) => {
        event.preventDefault();

        const fullname = document.getElementById("sign-up-fullname")?.value.trim();
        const email = document.getElementById("sign-up-email")?.value.trim();
        const password = document.getElementById("sign-up-pass")?.value;

        if (!fullname || !email || !password) return;

        try {
            const response = await fetch('/backend/sign-up.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ fullname, email, password })
            });

            if (!response.ok) throw new Error(`Ошибка запроса: ${response.status} ${response.statusText}`);

            const data = await response.json();
            const alertContainer = document.getElementById("alertSignUp");

            if (data.status === "error") {
                const errorMessages = {
                    "email-exist": "Email exists! Please enter another!"
                };

                alertContainer.innerHTML = `
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>${errorMessages[data.error] || "Unknown error!"}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                return;
            }

            alertContainer.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success! You are signed up to system!</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;

            checkAuth();
            window.location.reload();
        } catch (error) {
            console.error('Ошибка:', error);
            alert('Ошибка отправки данных');
        }
    };



    const checkAuth = async () => {
        try {
            const response = await fetch("/backend/auth.php");
            const data = await response.json();

            const authBlock = document.getElementById("renderAuthBlock");
            const editUserContent = document.getElementById("renderEditUserContent");
            const passwordUserContent = document.getElementById("renderPasswordUserContent");

            // Кеширование данных, которые не меняются при каждом запросе
            const { auth, fullname, uuid, login, email, notification } = data;

            console.log(notification);

            // Используем template strings для минимизации повторений кода
            if (auth === 1) {
                // HTML-контент для авторизованного пользователя
                const avatarUrl = `/backend/image.php?fullname=${fullname}&&size=40`;
                const avatarUrlEdit = `/backend/image.php?fullname=${fullname}&&size=100`;

                document.getElementById("editUserAvatar").src = avatarUrlEdit;

                document.getElementById("renderUserInterface").innerHTML = `
                    <a href="/@${login}#createArticle" class="btn bg-body-tertiary border rounded-circle m-0 fs-6"><i class="bi bi-plus-lg"></i></a>
                    <button onclick="showNotification()" class="btn bg-body-tertiary border rounded-circle m-0 fs-6 position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#notificationBlock" aria-controls="notificationBlock">
                        <i class="bi bi-heart"></i>
                        <span class="position-absolute ${notification!==1&&"d-none"} top-0 start-100 translate-middle bg-danger border border-light rounded-circle">
                            <span class="visually-hidden">New alerts</span>
                        </span>
                    </button>
                `;

                authBlock.innerHTML = `
                    <div class="dropdown" data-bs-theme="dark">
                        <div class="d-flex gap-2 align-items-center rounded-4 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="rounded-circle" loading="lazy" style="width:40px;height:40px;" src="${avatarUrl}" />
                            <input type="hidden" id="user_uuid" value="${uuid}" />
                        </div>
                        <ul class="dropdown-menu mt-3 rounded-4 px-1 py-2">
                            <li><button data-bs-toggle="modal" data-bs-target="#editUserModal" class="dropdown-item rounded-4"><i class="bi bi-person-badge opacity-50 me-2"></i>Edit info</button></li>
                            <li><a class="dropdown-item rounded-4" href="/@${login}"><i class="bi bi-person-circle opacity-50 me-2"></i>My profile</a></li>
                            <li><button onclick="logOut()" type="button" class="dropdown-item rounded-4"><i class="bi bi-box-arrow-left opacity-50 me-2"></i>Logout</button></li>
                        </ul>
                    </div>
                `;

                // Рендеринг данных пользователя в форме редактирования
                editUserContent.innerHTML = `
                    <div class="mb-3"><label for="edit-login" class="form-label">Login</label><input class="form-control bg-body-secondary rounded-4 px-4 py-2" placeholder="Login" type="text" id="edit-login" value="${login}" required /></div>
                    <div class="mb-3"><label for="edit-fullname" class="form-label">Full name</label><input class="form-control bg-body-secondary rounded-4 px-4 py-2" placeholder="Full name" type="text" id="edit-fullname" value="${fullname}" required /></div>
                    <div class="mb-4"><label for="edit-email" class="form-label">Email</label><input class="form-control bg-body-secondary rounded-4 px-4 py-2" placeholder="Email" type="email" id="edit-email" value="${email}" required /></div>
                `;

                // Рендеринг формы изменения пароля
                passwordUserContent.innerHTML = `
                    <div class="mb-3"><label for="edit-old-password" class="form-label">Old password</label><input class="form-control bg-body-secondary rounded-4 px-4 py-2" placeholder="Old password" type="password" id="edit-old-password" required /></div>
                    <div class="mb-3"><label for="edit-new-password" class="form-label">New password</label><input class="form-control bg-body-secondary rounded-4 px-4 py-2" placeholder="New password" type="password" id="edit-new-password" required /></div>
                    <div class="mb-4"><label for="edit-verify-password" class="form-label">Verify password</label><input class="form-control bg-body-secondary rounded-4 px-4 py-2" placeholder="Verify password" type="password" id="edit-verify-password" required /></div>
                `;
            } else {
                // HTML-контент для неавторизованного пользователя
                authBlock.innerHTML = `
                    <button type="button" class="btn btn-primary rounded-4" data-bs-toggle="modal" data-bs-target="#signProcessModal">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                `;
                document.getElementById("renderUserInterface").innerHTML = ``;
            }
        } catch (error) {
            console.error(error);
        }
    };

    window.addEventListener("load",()=>checkAuth());
    
    const logOut = async () => {
        try {
            const response = await fetch("/backend/logout.php");
            const data = await response.json();
            if (data.status === "success") {
                checkAuth();
                window.location.reload();
            }
        } catch (error) {
            console.error(error);
        }
    };

    const changePassword = async (event) => {
        event.preventDefault();
        
        const formData = Object.fromEntries(["old", "new", "verify"].map(key => [
            `${key}-password`, document.getElementById(`edit-${key}-password`).value
        ]));
        formData["user-uuid"] = document.getElementById("user_uuid").value;

        try {
            const res = await fetch("/backend/change-password.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(formData),
            });
            const data = await res.json();
            const alertMessage = document.getElementById("alertEditPassword");

            alertMessage.innerHTML = `
                <div class="alert alert-${data.status === "success" ? "success" : "warning"} alert-dismissible fade show" role="alert">
                    <strong>${data.status === "success" ? "Congratulations!" : (data.error === "old-password-error" ? "Old password error!" : "Verify password error!")}</strong> 
                    ${data.status === "success" ? "Password changed!" : "Try again!"} 
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        } catch (error) {
            console.error("Error:", error);
            alert("Error sending data");
        }
    };


    const showPassword = () => {
        document.querySelectorAll(".show-password").forEach(pass => {
            if (pass.type === "password") {
                pass.type = "text";
            } else {
                pass.type = "password";
            }
        });
    };
</script>
<header class="position-sticky top-0" style="z-index: 3;">
    <nav class="container-xxl py-3"
        style="background-color: rgba(var(--bs-body-bg-rgb),0.8);backdrop-filter: blur(10px);" aria-label="Eighth navbar example">
        <div class="row">
            <div class="col-md-3 col-3 d-flex align-items-center order-md-1 order-1">
                <a class="navbar-brand h-auto d-md-block d-none" href="/">
                    <img id="globalMainLogo" style="width: 100px;" src="/source/image/folio-light.svg" loading="lazy" title="To home" alt="Folio logo" />
                </a>
                <a class="navbar-brand h-auto d-md-none d-block" href="/">
                    <img style="width: 40px;" src="/source/image/folio-single.svg" loading="lazy" title="To home" alt="Folio logo" />
                </a>
            </div>
            <div class="col-md-6 col-12 order-md-2 d-none d-md-block">
                <div class="dropdown">
                    <div class="input-group">
                        <input oninput="globalSearchFromDatabase(this)" id="globalSearchFolio" style="border-radius:24px 0 0 24px;" type="search" class="form-control px-4" placeholder="Search" aria-label="Search">
                        <button style="border-radius:0 24px 24px 0;" class="btn bg-body-tertiary border px-4" type="button"><i class="bi bi-search"></i></button>
                    </div>
                    <ul class="dropdown-menu w-100 mt-2 rounded-4 px-2 py-3" id="globalSearchDropdown" data-bs-theme="dark">
                    </ul>
                </div>
                <script>
                    window.addEventListener("load",()=>{
                        const searchInput = document.getElementById('globalSearchFolio');
                        const dropdownMenu = document.getElementById('globalSearchDropdown');

                        searchInput.addEventListener('focus', () => dropdownMenu.classList.add('show') );
                        searchInput.addEventListener('blur', () => setTimeout(() => dropdownMenu.classList.remove('show'), 200) );
                        dropdownMenu.addEventListener('mousedown', (e) => e.preventDefault() );
                    });
                    const globalSearchFromDatabase = async(e) => {
                        const value = e.value;
                        const dropdownMenu = document.getElementById('globalSearchDropdown');
                        const req = await fetch("/backend/search.php?search="+value);
                        const res = await req.json();
                        let render = `<li><a class="dropdown-item rounded-4 text-truncate" href="#">Nothing found</a></li>`;
                        if(res['status']==="error") render = "";
                        if(res['status']==="success") render = res["data"].map(html=>`<li><a class="dropdown-item rounded-4 text-truncate" href="/a/@${html['uuid']}"><i class="bi bi-search me-3 opacity-50"></i>${html['title']}</a></li>`).join("");
                        dropdownMenu.innerHTML = render;
                    }
                </script>
            </div>

            <div class="col-md-3 col-9 order-md-3 order-2">
                <div class="d-flex gap-3 justify-content-end">
                    <div id="renderUserInterface" class="d-flex gap-3">
                        
                    </div>
                    <div class="d-md-none">
                        <button class="btn bg-body-tertiary border rounded-circle m-0 fs-6">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="dropdown">
                        <button class="btn bg-body-tertiary border rounded-circle m-0 fs-6" title="Change theme" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-moon-stars"></i>
                        </button>
                        <ul data-bs-theme="dark" class="dropdown-menu mt-2 rounded-4 px-1 py-2">
                            <li><button id="lightTheme" onclick="toggleGlobalTheme('light')" class="dropdown-item rounded-4 themeSelect" type="button"><i class="bi bi-brightness-high opacity-50 me-2"></i>Light</button></li>
                            <li><button id="darkTheme" onclick="toggleGlobalTheme('dark')" class="dropdown-item rounded-4 themeSelect" type="button"><i class="bi bi-moon-stars opacity-50 me-2"></i>Dark</button></li>
                            <li><button id="autoTheme" onclick="toggleGlobalTheme('auto')" class="dropdown-item rounded-4 themeSelect" type="button"><i class="bi bi-circle-half opacity-50 me-2"></i>Auto</button></li>
                        </ul>
                    </div>
                    <script>
                        (() => {
                            const html = document.documentElement,
                                    logo = document.getElementById("globalMainLogo");

                            // Функция для установки cookie
                            const setCookie = (name, value, days) => {
                                const expires = days ? `; expires=${new Date(Date.now() + days * 864e5).toUTCString()}` : "";
                                document.cookie = `${name}=${value}${expires}; path=/`;
                            };

                            // Функция для удаления cookie
                            const deleteCookie = name => document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/`;

                            // Функция для получения cookie
                            const getCookie = name => {
                                const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
                                return match ? match[1] : null;
                            };

                            // Применяет тему и обновляет атрибуты
                            const applyTheme = (theme,auto=null) => {
                                logo.src = `/source/image/folio-${theme}.svg`;
                                html.setAttribute("data-bs-theme", theme);
                                auto ? html.removeAttribute("data-bs-toggle") : html.setAttribute("data-bs-toggle", "selected");
                                document.querySelectorAll(".themeSelect").forEach((theme)=>{
                                    theme.classList.contains("active") && theme.classList.remove("active");
                                })
                                auto ? document.getElementById("autoTheme").classList.toggle("active"): document.getElementById(theme+"Theme").classList.toggle("active");
                            };

                            // Переключение темы
                            const toggleGlobalTheme = theme => {
                                if (theme === "auto") {
                                    deleteCookie("theme");
                                    const sysTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? "dark" : "light";
                                    applyTheme(sysTheme,true);
                                    html.removeAttribute("data-bs-toggle");
                                } else {
                                    applyTheme(theme);
                                    setCookie("theme", theme, 30);
                                }
                            };

                            // Автоматическое обновление при изменении системной темы, если пользователь не выбрал вручную
                            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', ({matches}) => {
                                !html.hasAttribute("data-bs-toggle") && applyTheme(matches ? "dark" : "light",true);
                                !html.hasAttribute("data-bs-toggle") && setCookie("theme", matches ? "dark" : "light", 30);
                            });

                            // При загрузке страницы: если cookie есть, применяем его, иначе выбираем по системным настройкам
                            const themeCookie = getCookie("theme");
                            if (themeCookie) {
                                applyTheme(themeCookie);
                            } else {
                                const sysTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? "dark" : "light";
                                applyTheme(sysTheme,true);
                            }

                            // Делаем toggleGlobalTheme доступной глобально, если требуется вызов из вне
                            window.toggleGlobalTheme = toggleGlobalTheme;
                        })();
                    </script>
                    <div id="renderAuthBlock">
                        <button type="button" class="btn btn-primary rounded-4" data-bs-toggle="modal"
                            data-bs-target="#signProcessModal">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
<?php include_once $_SERVER["DOCUMENT_ROOT"] . "/module/header/notification.php"; ?>