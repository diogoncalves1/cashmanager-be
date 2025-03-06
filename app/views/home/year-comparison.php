<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = 'year comparison';
if (!isset($_COOKIE['user'])) {
    header("location: /CashManager/public/sign-up");
}
require_once "../backend/querys.php";
require_once "../backend/language.php";
require "../backend/translate.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $words["year_comparison"]; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?php echo $add_revenue; ?></button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <canvas class="my-4 w-100" id="myChart" width="900" height="380"></canvas>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered text-center align-middle">
            <thead>
                <tr>
                    <th scope="col" class="col-3"><?php echo $date; ?></th>
                    <th scope="col" class="col-3"><?php echo $revenue; ?></th>
                    <th scope="col" class="col-3"><?php echo $expense; ?></th>
                    <th scope="col" class="col-3">Total</th>
                </tr>
            </thead>
            <tbody id="table">
                <?php if (isset($month)) {
                    $max = count($month);
                    for ($i = 0; $i < $max; $i++) { ?>
                <tr class="<?php if ($_COOKIE['mode'] == "light") { ?>table-primary<?php } ?> primary">
                    <td><?php echo $month_name[$i]; ?></td>
                    <td style="color: green;"><?php if (!isset($revenues[$i]))
                                                            $revenues[$i] = 0;
                                                        echo $revenues[$i]; ?><?= $coin ?></td>
                    <td style="color: red;"><?php if (!isset($expenses[$i]))
                                                        $expenses[$i] = 0;
                                                    echo $expenses[$i]; ?><?= $coin ?></td>
                    <td style="<?php if ($revenues[$i] - $expenses[$i] <= 0) {
                                            echo "color: red";
                                        } else
                                            echo "color: green;"; ?>"><?php echo $month[$i] ?><?= $coin; ?></td>
                </tr>
                <?php }
                } ?>
            </tbody>
        </table>
    </div>
</main>

<?php
include("../assets/js/charts/year-comparison-bar.php");
?>