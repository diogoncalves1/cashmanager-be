<div class="sidebar border border-right col-md-3 col-lg-2 p-0 bg-body-tertiary">
    <div class="offcanvas-md offcanvas-end bg-body-tertiary" tabindex="-1" id="sidebarMenu"
        aria-labelledby="sidebarMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarMenuLabel">Cash Manager</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">

            <ul class="nav flex-column">

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2  " href="/CashManager/admin">
                        <svg class="bi">
                            <use xlink:href="#house<?= $_SESSION['page'] == "home" ? "-fill" : ""; ?>" />
                        </svg>
                        <?php echo $home; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2  " href="/CashManager/admin/users">
                        <svg class="bi">
                            <use xlink:href="#people<?= $_SESSION['page'] == "users" ? "-fill" : ""; ?>" />
                        </svg>
                        <?php echo $words[$lang]["users"]; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2  " href="/CashManager/admin/categories-expenses">
                        <svg class="bi">
                            <use
                                xlink:href="#tags<?= $_SESSION['page'] == "categories expenses" || $_SESSION['page'] == "edit category expense" || $_SESSION['page'] == "add category expense" ? "-fill" : ""; ?>" />
                        </svg>
                        <?= $categories_expenses; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2"
                        href="/CashManager/admin/financial-goal-categories">
                        <svg class="bi">
                            <use
                                xlink:href="#tags<?= $_SESSION['page'] == "financial goal categories" || $_SESSION['page'] == "edit category expense" || $_SESSION['page'] == "add category expense" ? "-fill" : ""; ?>" />
                        </svg>
                        <?= $words[$lang]["financial_goal_categories"]; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/admin/coins">
                        <svg class="bi">
                            <use xlink:href="#coin" />
                        </svg>
                        <?= $words[$lang]["coins"]; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/admin/permissions">
                        <svg class="bi">
                            <use
                                xlink:href="#shield-lock<?= $_SESSION["page"] == "manage permissions" || $_SESSION["page"] == "add permission" || $_SESSION["page"] == "edit permission" ? "-fill" : ""; ?>" />
                        </svg>
                        <?= $words[$lang]["permissions"]; ?>
                    </a>
                </li>
            </ul>

            <hr class="my-3">

            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/logout">
                        <svg class="bi">
                            <use xlink:href="#door-closed" />
                        </svg>
                        Sign out
                    </a>
                </li>
            </ul>

            <div class="row navbar-nav flex-row d-md-none mt-4">
                <div class="btn-group me-3 col-lg-2 justify-content-between">
                    <div class="ms-4 ">
                        <button
                            class="btn btn-outline-success <?= $lightTest == 1 ? "box-shadow-success-btn" : "" ?> py-2 dropdown-toggle d-flex align-items-center"
                            id="bd-theme" type="button" aria-expanded="true" data-bs-toggle="dropdown"
                            aria-label="Toggle theme (dark)">
                            <svg class="bi my-1 theme-icon-active" width="1em" height="1em">
                                <use href="#moon-stars-fill"></use>
                            </svg>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text"
                            data-popper-placement="bottom">
                            <li>
                                <button type="button" id="light-btn" onclick="changeMode(this.value)"
                                    class="dropdown-item d-flex align-items-center <?php if (isset($_COOKIE['mode'])) { ?> <?= $_COOKIE['mode'] == "light" ? "active" : ""; ?><?php } ?>"
                                    value="light" aria-pressed="false">
                                    <svg class="bi me-2 opacity-50" width="1em" height="1em">
                                        <use href="#sun-fill"></use>
                                    </svg>
                                    Light
                                </button>
                            </li>
                            <li>
                                <button type="button" id="dark-btn" onclick="changeMode(this.value)"
                                    class="dropdown-item d-flex align-items-center <?php if (isset($_COOKIE['mode'])) { ?><?= $_COOKIE['mode'] == "dark" ? "active" : ""; ?><?php } ?>"
                                    value="dark" aria-pressed="true">
                                    <svg class="bi me-2 opacity-50" width="1em" height="1em">
                                        <use href="#moon-stars-fill"></use>
                                    </svg>
                                    Dark
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="me-4">
                        <button type="button"
                            class="h-100 <?= $lightTest == 1 ? "box-shadow-success-btn" : "" ?> btn btn-sm btn-outline-success dropdown-toggle  align-items-center"
                            data-bs-toggle="dropdown" aria-expanded="false"
                            disable><?= $_COOKIE['lang'] == "PT" ? "Linguagem" : "Language"; ?></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button class="dropdown-item" type="button" onclick="language('EN')">English</button>
                            </li>
                            <li><button class="dropdown-item" type="button" onclick="language('PT')">Português</button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>