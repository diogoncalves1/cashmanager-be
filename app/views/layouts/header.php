<div class="loader-div" id="loader-div">
    <div class="loader">
    </div>
</div>

<?php

use app\Models\Alert;

$alertInstance = new Alert();
$alertDate = new DateTime("now");
$alertDate->modify("+5 days");
$curDate = new DateTime("now");
$alertDate = $alertDate->format("Y-m-d");
?>
<header class="navbar sticky-top bg-dark flex-md-nowrap p-0 shadow" data-bs-theme="dark">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 text-white" href="/CashManager/home">
        <img src="/CashManager/assets/logos/logo-transparent.png" alt="" height="50px">
        Cash Manager | <?= $userName; ?></a>
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
                            <?= $light; ?>
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

<?php /*if ($_COOKIE['notifications'] == 1) { ?>
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <?php
        $alerts = $alertInstance->getUserAlerts($user_id, $curDate->format("Y-m-01"));
        foreach ($alerts as $alert) { ?>
    <div id="liveToast" class="toast fade hide show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="alert <?php if ($alert['type'] == 1) {
                                        echo "alert-danger ";
                                        echo $lightTest == 1 ? "box-shadow-danger-alert" : "";
                                    } else {
                                        echo "alert-warning ";
                                        echo $lightTest == 1 ? "box-shadow-warning" : "";
                                    } ?> d-flex align-items-center mb-0" role="alert">
            <svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:">
                <use xlink:href="#exclamation-triangle-fill" />
            </svg>
            <div>
                <?= $alert['mensage']; ?>
            </div>
            <button type="button" class="btn-close me-2 m-auto" onclick="readAlert(<?= $alert['id']; ?>)"
                data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <?php /*}
        $result = get_alert_scheduled($conn, $user_id);
        while ($alert = $result->fetch_assoc()) {
            if ($alertDate >= $alert['date']) { ?>
    <div id="alert-scheduled-<?= $alert['scheduled_expenses_id']; ?>" class="toast fade hide show" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="alert alert-warning <?= $lightTest == 1 ? "box-shadow-warning" : "" ?> d-flex align-items-center mb-0"
            role="alert">
            <svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:">
                <use xlink:href="#exclamation-triangle-fill" />
            </svg>
            <div>
                <?php

                            $resultE = get_scheduled_expenses($conn, 0, $alert['scheduled_expenses_id']);
                            $row = $resultE->fetch_assoc();
                            $xDate = new DateTime($alert['date']);
                            $diff = date_diff($curDate, $xDate);
                            if ($alertDate >= $alert['date'] && $curDate->format("Y-m-d") < $alert['date']) {
                                if ($_COOKIE['lang'] == "PT")
                                    echo "Faltam " . $diff->d + 1 . " dia" . ($diff->d > 0 ? "s" : "") . " para a sua despesa agendada da categoria ";
                                else
                                    echo "There are " . $diff->d + 1 . " day" . ($diff->d > 0 ? "s" : "") . " left for your scheduled expense. Category: ";
                            } else if ($curDate->format("Y-m-d") == $alert['date']) {
                                if ($_COOKIE['lang'] == "PT")
                                    echo "Tem uma despesa agendada para hoje da categoria ";
                                else
                                    echo "You have an expense scheduled for today. Category: ";
                            } else {
                                if ($_COOKIE['lang'] == "PT")
                                    echo "Tem uma despesa agendada atrasada " . $diff->d . " dia" . ($diff->d > 1 ? "s" : "") . " da categoria ";
                                else
                                    echo "Has a " . $diff->d . " day" . ($diff->d > 0 ? "s" : "") . " late scheduled expense. Category: ";
                            }

                            //echo get_category_name($conn, $row['cat_id']) . "!"
                            ?>
            </div>
            <button type="button" class="btn-close me-2 m-auto" onclick="readScheduledAlert(<?= $alert['id']; ?>)"
                data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <?php }
        }
        $result = get_alert_user($conn, $user_id);
        while ($alert = $result->fetch_assoc()) { ?>
    <div id="liveToast" class="toast fade hide show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="alert alert-warning <?= $lightTest == 1 ? "box-shadow-warning" : "" ?> d-flex align-items-center mb-0"
            role="alert">
            <svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:">
                <use xlink:href="#exclamation-triangle-fill" />
            </svg>
            <div>
                <?= $alert['mensage']; ?>
            </div>
            <button type="button" class="btn-close me-2 m-auto" onclick="deleteAlertUser(<?= $alert['id']; ?>)"
                data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <?php } ?>
</div>
<?php }*/ ?>