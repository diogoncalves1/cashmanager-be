<?php
require "../backend/querys.php";

if (!isset($_COOKIE['user']))
    header("location: /CashManager/sign-up");

if (is_admin($conn, $user_id)) {
    $this->layout("master-admin");
    $_SESSION['path'] = $_SERVER['REQUEST_URI'];
    $_SESSION['page'] = "home";
    require_once "../backend/language.php";
    require "../backend/translate.php";
?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <h1 class="h2 mt-3"><?php echo $welcome; ?> <?= $user_name; ?></h1>
        <div class="row pt-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card zoom cur-pointer border-left-primary <?= $lightTest == 1 ? "box-shadow-primary" : "" ?> h-100 py-2"
                    onclick="goToViewUsers()">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    <?= $words[$lang]["total_users"]; ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $numTotalUsers; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <b class="bi w-auto euro">
                                    <svg class="bi-3 opacity-50" width="1em" height="1em">
                                        <use href="#people-fill"></use>
                                    </svg>
                                </b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
    <script src="/CashManager/public/admin/js/home.js"></script>
<?php } ?>