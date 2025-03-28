<div class="sidebar border border-right col-md-3 col-lg-2 p-0 bg-body-tertiary">
    <div class="offcanvas-md offcanvas-end bg-body-tertiary" tabindex="-1" id="sidebarMenu"
        aria-labelledby="sidebarMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarMenuLabel">Cash Manager</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">

            <button
                class="accordion-button sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase"
                type="button" data-bs-toggle="collapse" data-bs-target="#panel-home" aria-expanded="true"
                aria-controls="panelsStayOpen-collapseOne">
                <span><?= $translate["home"]; ?></span>
            </button>
            <div id="panel-home" class="accordion-collapse collapse <?php if ($_SESSION['page'] == "expenses summary" || $_SESSION['page'] == "home" || $_SESSION['page'] == "monthly comparison" || $_SESSION['page'] == "year comparison" || $_SESSION['page'] == "monthly summary")
                                                                        echo "show"; ?>">
                <ul class="nav flex-column">

                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2  " href="/CashManager/home">
                            <svg class="bi">
                                <use xlink:href="#house<?= $_SESSION['page'] == "home" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["home"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2  " href="/CashManager/monthly-comparison">
                            <svg class="bi">
                                <use
                                    xlink:href="#graph<?= $_SESSION['page'] == "monthly comparison" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["monthly_comparison"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2  " href="/CashManager/year-comparison">
                            <svg class="bi">
                                <use xlink:href="#graph<?= $_SESSION['page'] == "year comparison" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["year_comparison"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2  " href="/CashManager/monthly-summary">
                            <svg class="bi">
                                <use
                                    xlink:href="#graph-pie<?= $_SESSION['page'] == "monthly summary" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["monthly_summary"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2  " href="/CashManager/expenses-summary">
                            <svg class="bi">
                                <use
                                    xlink:href="#graph-pie<?= $_SESSION['page'] == "expenses summary" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["expenses_summary"]; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <button
                class="accordion-button sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase"
                type="button" data-bs-toggle="collapse" data-bs-target="#panel-account" aria-expanded="true"
                aria-controls="panelsStayOpen-collapseOne">
                <span><?= $translate["accounts"]; ?></span>
            </button>
            <div id="panel-account" class="accordion-collapse collapse <?php if ($_SESSION['page'] == "edit account" || $_SESSION['page'] == "account comparison" || $_SESSION['page'] == "create account" || $_SESSION['page'] == "manage accounts")
                                                                            echo "show"; ?>">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/accounts">
                            <svg class="bi">
                                <use
                                    xlink:href="#file-earmark-text<?= $_SESSION['page'] == "manage accounts" || $_SESSION['page'] == "create account" || $_SESSION['page'] == "edit account" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["manage_accounts"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/accounts/comparison">
                            <svg class="bi">
                                <use
                                    xlink:href="#graph<?= $_SESSION['page'] == "account comparison" ? "-fill" : ""  ?>" />
                            </svg>
                            <?= $translate["account_comparison"]; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <button
                class="accordion-button sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase"
                type="button" data-bs-toggle="collapse" data-bs-target="#panel-goals" aria-expanded="true"
                aria-controls="panelsStayOpen-collapseOne">
                <span><?= $translate["objectives"]; ?></span>
            </button>
            <div id="panel-goals" class="accordion-collapse collapse <?php if ($_SESSION['page'] == "create objective" || $_SESSION['page'] == "investing" || $_SESSION['page'] == "goals" || $_SESSION['page'] == "claimed goals" || $_SESSION['page'] == "manage goals")
                                                                            echo "show"; ?>">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/objectives/invest">
                            <svg class="bi">
                                <use xlink:href="#line-graph<?= $_SESSION['page'] == "investing" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["investing_in_the_objective"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/objectives/view">
                            <svg class="bi">
                                <use xlink:href="#goals<?= $_SESSION['page'] == "goals" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["view_objectives"];; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/objectives/completed">
                            <svg class="bi">
                                <use xlink:href="#star<?= $_SESSION['page'] == "claimed goals" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["completed_objectives"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/objectives">
                            <svg class="bi">
                                <use
                                    xlink:href="#file-earmark-text<?= $_SESSION['page'] == "manage goals" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["objectives"]; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <button
                class="accordion-button sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase"
                type="button" data-bs-toggle="collapse" data-bs-target="#panel-transactions" aria-expanded="true"
                aria-controls="panelsStayOpen-collapseOne">
                <span><?= $translate["transactions"]; ?></span>
            </button>
            <div id="panel-transactions" class="accordion-collapse collapse <?php if ($_SESSION['page'] == "view transaction" || $_SESSION['page'] == "edit transaction" || $_SESSION['page'] == "scheduled expenses" || $_SESSION['page'] == "schedule expense" || $_SESSION['page'] == "expense" || $_SESSION['page'] == "revenue" || $_SESSION['page'] == "manage transactions")
                                                                                echo "show"; ?>">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2"
                            href="/CashManager/transactions/add/expense">
                            <svg class="bi">
                                <use xlink:href="#expense-icon<?= $_SESSION['page'] == "expense" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["add_expense"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2"
                            href="/CashManager/transactions/add/revenue">
                            <svg class="bi">
                                <use xlink:href="#revenue-icon<?= $_SESSION['page'] == "revenue" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["add_revenue"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/transactions">
                            <svg class="bi">
                                <use
                                    xlink:href="#file-earmark-text<?= $_SESSION['page'] == "manage transactions" || $_SESSION["page"] == "view transaction" || $_SESSION["page"] == "edit transaction" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["transactions"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/scheduled-expenses/add">
                            <svg class="bi">
                                <use
                                    xlink:href="#calendar-plus<?= $_SESSION['page'] == "schedule expense" ? "-fill" : ""; ?>" />
                            </svg>
                            <?php echo $schedule_expense; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/scheduled-expenses">
                            <svg class="bi">
                                <use
                                    xlink:href="#calendar3<?= $_SESSION['page'] == "scheduled expenses" ? "-fill" : ""; ?>" />
                            </svg>
                            <?php echo $scheduled_expenses; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <button
                class="accordion-button sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase"
                type="button" data-bs-toggle="collapse" data-bs-target="#panel-debts" aria-expanded="true"
                aria-controls="panelsStayOpen-collapseOne">
                <span><?= $translate["debts"]; ?></span>
            </button>
            <div id="panel-debts" class="accordion-collapse collapse <?php if ($_SESSION['page'] == "add debt" || $_SESSION['page'] == "manage debts" ||  $_SESSION['page'] == "summary debts" || $_SESSION['page'] == "edit debt")
                                                                            echo "show"; ?>">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/debts">
                            <svg class="bi">
                                <use
                                    xlink:href="#file-earmark-text<?= $_SESSION['page'] == "manage debts" || $_SESSION["page"] == "view transaction" || $_SESSION["page"] == "edit transaction" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["debts"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/debts/payment-control">
                            <svg class="bi">
                                <use xlink:href="#cash" />
                            </svg>
                            <?= $translate["payment_control"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/debts/summary">
                            <svg class="bi">
                                <use xlink:href="#wallet<?= $_SESSION['page'] == "summary debts" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["summary_debts"]; ?>
                        </a>
                    </li>
                </ul>
            </div>
            <button
                class="accordion-button sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase"
                type="button" data-bs-toggle="collapse" data-bs-target="#panel-loans" aria-expanded="true"
                aria-controls="panelsStayOpen-collapseOne">
                <span><?= $translate["loans"]; ?></span>
            </button>
            <div id="panel-loans" class="accordion-collapse collapse <?php if ($_SESSION['page'] == "add loan" || $_SESSION['page'] == "manage loans" || $_SESSION['page'] == "edit loan" || $_SESSION['page'] == "simulate loan")
                                                                            echo "show"; ?>">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/loans/add">
                            <svg class="bi">
                                <use xlink:href="#revenue-icon<?= $_SESSION['page'] == "add loan" ? "-fill" : ""; ?>" />
                            </svg>
                            <?php echo $add_loan; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/loans">
                            <svg class="bi">
                                <use
                                    xlink:href="#file-earmark-text<?= $_SESSION['page'] == "manage loans" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $manage_loans; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/loans/payment-control">
                            <svg class="bi">
                                <use xlink:href="#cash" />
                            </svg>
                            <?= $payment_control; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <button
                class="accordion-button sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase"
                type="button" data-bs-toggle="collapse" data-bs-target="#panel-limits" aria-expanded="true"
                aria-controls="panelsStayOpen-collapseOne">
                <span><?= $translate["budgets"]; ?></span>
            </button>
            <div id="panel-limits" class="accordion-collapse collapse <?php if ($_SESSION['page'] == "add limits" || $_SESSION['page'] == "manage limits")
                                                                            echo "show"; ?>">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/budgets/add">
                            <svg class="bi">
                                <use
                                    xlink:href="#revenue-icon<?= $_SESSION['page'] == "add limits" ? "-fill" : ""; ?>" />
                            </svg>
                            <?php echo $add_limit; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/budgets">
                            <svg class="bi">
                                <use
                                    xlink:href="#file-earmark-text<?= $_SESSION['page'] == "manage limits" ? "-fill" : ""; ?>" />
                            </svg>
                            <?php echo $manage_limits; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <button
                class="accordion-button sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase"
                type="button" data-bs-toggle="collapse" data-bs-target="#panel-financial-goals" aria-expanded="true"
                aria-controls="panelsStayOpen-collapseOne">
                <span><?= $translate["financial_goals"]; ?></span>
            </button>
            <div id="panel-financial-goals" class="accordion-collapse collapse <?php if ($_SESSION['page'] == "add financial goal" || $_SESSION['page'] == "financial goals" || $_SESSION['page'] == "manage financial goals")
                                                                                    echo "show"; ?>">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/financial-goals">
                            <svg class="bi">
                                <use
                                    xlink:href="#file-earmark-text<?= $_SESSION['page'] == "manage financial goals" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["financial_goals"]; ?>
                        </a>
                    </li>
                </ul>
            </div>

            <hr class="my-3">

            <button
                class="accordion-button sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase"
                type="button" data-bs-toggle="collapse" data-bs-target="#panel-social" aria-expanded="true"
                aria-controls="panelsStayOpen-collapseOne">
                <span><?= $translate["social"]; ?></span>
            </button>
            <div id="panel-social"
                class="accordion-collapse collapse <?php if ($_SESSION['page'] == "shares" || $_SESSION['page'] == "share" || $_SESSION['page'] == "sent requests" || $_SESSION['page'] == "requests" || $_SESSION['page'] == "friends" || $_SESSION['page'] == "add friend")                                                                           echo "show"; ?>">
                <ul class="nav flex-column mb-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/shares">
                            <svg class="bi">
                                <use xlink:href="#share<?= $_SESSION['page'] == "shares" ? "-fill" : ""; ?>" />
                            </svg>
                            <?php echo $shares; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/share">
                            <svg class="bi">
                                <use xlink:href="#send<?= $_SESSION['page'] == "share" ? "-fill" : ""; ?>" />
                            </svg>
                            <?php echo $share; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/share/sent">
                            <svg class="bi">
                                <use
                                    xlink:href="#envelope-arrow-up<?= $_SESSION['page'] == "sent requests" ? "-fill" : ""; ?>" />
                            </svg>
                            <?= $translate["sent_requests"]; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" id="requests"
                            href="/CashManager/shares/requests">
                            <svg class="bi">
                                <use xlink:href="#mailbox<?= $_SESSION['page'] == "requests" ? "-fill" : ""; ?>" />
                            </svg>
                            <?php echo $share_requests; ?>
                            <?php //$numRequest = get_share_request($conn, $user_id,);
                            if ($numRequest->num_rows > 0) { ?>
                            <span class="badge text-bg-secondary" id="requests-counter"><?= $numRequest->num_rows; ?>
                            </span>
                            <?php } ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/friends">
                            <svg class="bi">
                                <use xlink:href="#people<?= $_SESSION['page'] == "friends" ? "-fill" : ""; ?>" />
                            </svg>
                            <?php echo $friends; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" id="add-friend"
                            href="/CashManager/friends/add">
                            <svg class="bi">
                                <use
                                    xlink:href="#people-plus<?= $_SESSION['page'] == "add friend" ? "-fill" : ""; ?>" />
                            </svg>
                            <?php echo $add_friend; ?>
                            <?php //$numRequest = get_friend_request($conn, $user_id, 0, 0);
                            if ($numRequest->num_rows > 0) { ?>
                            <span class="badge text-bg-secondary"
                                id="friend-request-counter"><?= $numRequest->num_rows; ?>
                            </span>
                            <?php } ?>
                        </a>
                    </li>
                </ul>
            </div>
            <ul class="nav flex-column mb-auto">
                <li class="nav-item ">
                    <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/tools">
                        <svg class="bi">
                            <use xlink:href="#tools" />
                        </svg>
                        <?= $translate["tools"]; ?>
                    </a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/create-reminder">
                        <svg class="bi">
                            <use xlink:href="#bell<?= $_SESSION['page'] == "create reminder" ? "-fill" : ""; ?>" />
                        </svg>
                        <?= $translate["create_reminder"]; ?>
                    </a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/settings">
                        <svg class="bi">
                            <use xlink:href="#gear-wide<?= $_SESSION['page'] == "settings" ? "-connected" : ""; ?>" />
                        </svg>
                        <?= $translate["settings"]; ?>

                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" href="/CashManager/logout">
                        <svg class="bi">
                            <use xlink:href="#door-closed" />
                        </svg>
                        <?= $translate["logout"]; ?>
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