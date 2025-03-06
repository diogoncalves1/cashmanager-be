<?php
$this->layout("master");
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "monthly summary";
if (!isset($_COOKIE['user'])) {
    header("location: /CashManager/public/sign-up");
}
require_once("../backend/querys.php");
require_once "../backend/language.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $summary; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?php echo $add_revenue; ?></button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <?php for ($i = 0; $i <= $maxMonths; $i++) { ?>
                <div class="col-6 col-md-4 col-xl-3">
                    <h5>
                        <?php echo $month_name[$i]; ?>
                    </h5>
                    <canvas class="<?php if ($revenues[$i] > $expenses[$i]) {
                                        if ($_COOKIE['lights'] == 1)
                                            echo "drop-shadow-success";
                                    } else {
                                        if ($_COOKIE['lights'] == 1)
                                            echo "drop-shadow-danger";
                                    } ?>" id="myChart<?php echo $i; ?>"></canvas>
                </div>
            <?php } ?>
        </div>
    </div>
</main>
<?php
include("../assets/js/charts/monthly-sumary-doughnut.php");
?>