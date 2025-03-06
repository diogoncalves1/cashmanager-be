<div class="loader-div" id="loader-div">
    <div class="loader">
    </div>
</div>

<?php $alertDate = new DateTime("now");
$alertDate->modify("+5 days");
$curDate = new DateTime("now");
$alertDate = $alertDate->format("Y-m-d"); ?>
<header class="navbar sticky-top bg-dark flex-md-nowrap p-0 shadow" data-bs-theme="dark">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 text-white" href="/CashManager/admin">
        <img src="/CashManager/public/assets/logos/logo-transparent.png" alt="" height="50px">
        Cash Manager | <?= $user_name; ?></a>
    <div class="d-none d-md-flex">
        <div class="btn-group me-3 col-lg-2">
            <div class="me-5">
                <button
                    class="btn btn-outline-success <?= $lightTest == 1 ? "box-shadow-success-btn" : "" ?> py-2 dropdown-toggle d-flex align-items-center"
                    id="bd-theme" type="button" aria-expanded="true" data-bs-toggle="dropdown"
                    aria-label="Toggle theme (dark)">
                    <svg class="bi my-1 theme-icon-active" width="1em" height="1em">
                        <use id="icon-mode" href="#moon-stars-fill"></use>
                    </svg>
                </button>
                <ul class="dropdown-menu dropdown-menu shadow" aria-labelledby="bd-theme-text"
                    data-popper-placement="bottom">
                    <li>
                        <button type="button" id="light-btn" onclick="changeMode(this.value)"
                            class="dropdown-item d-flex align-items-center <?php if (isset($_COOKIE['mode'])) { ?> <?= $_COOKIE['mode'] == "light" ? "active" : ""; ?><?php } ?>"
                            value="light" aria-pressed="false">
                            <svg class="bi me-2 opacity-50" width="1em" height="1em">
                                <use href="#sun-fill"></use>
                            </svg>
                            <?= $words[$lang]["light"]; ?>
                        </button>
                    </li>
                    <li>
                        <button type="button" id="dark-btn" onclick="changeMode(this.value)"
                            class="dropdown-item d-flex align-items-center <?php if (isset($_COOKIE['mode'])) { ?><?= $_COOKIE['mode'] == "dark" ? "active" : ""; ?><?php } ?>"
                            value="dark" aria-pressed="true">
                            <svg class="bi me-2 opacity-50" width="1em" height="1em">
                                <use href="#moon-stars-fill"></use>
                            </svg>
                            <?= $dark; ?>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="">
                <button type="button" id="bd-lang"
                    class="h-100 <?= $lightTest == 1 ? "box-shadow-success-btn" : "" ?> btn btn-sm btn-outline-success dropdown-toggle  align-items-center"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    disable><?= $_COOKIE['lang'] == "PT" ? "Linguagem" : "Language"; ?></button>
                <ul class="dropdown-menu">
                    <li><button class="dropdown-item" type="button" onclick="language('EN')">English</button></li>
                    <li><button class="dropdown-item" type="button" onclick="language('PT')">Português</button></li>
                </ul>
            </div>
        </div>
    </div>
    <ul class="navbar-nav flex-row d-md-none">
        <li class="nav-item text-nowrap">
            <button class="nav-link px-3 text-white" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false"
                aria-label="Toggle navigation">
                <svg class="bi">
                    <use xlink:href="#list" />
                </svg>
            </button>
        </li>
    </ul>
</header>

<script src="../assets/js/all.js"></script>
<script src="../../assets/js/all.js"></script>