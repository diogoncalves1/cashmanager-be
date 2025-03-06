<?php
$this->layout("master");

use app\Models\Account;

$accountInstance = new Account();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "home";
if (!isset($_COOKIE['user'])) {
    header("location: /CashManager/public/sign-up");
}
require_once "../backend/querys.php";
require_once "../backend/language.php";
require "../backend/translate.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h1 class="h2 mt-3"><?= $words["welcome"]; ?> <?= $user_name; ?></h1>

    <div class="row pt-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card zoom cur-pointer border-left-success <?= $lightTest == 1 ? "box-shadow-success" : "" ?> h-100 py-2"
                onclick="goToManageTransactions(1)">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?= $words["earnings"]; ?> (<?= $words["ever"]; ?>)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $totalRevenues; ?><?= $coin; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <b class="bi w-auto euro"><?= $coin; ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card cur-pointer zoom border-left-danger <?= $lightTest == 1 ? "box-shadow-danger" : "" ?> h-100 py-2"
                onclick="goToManageTransactions(0)">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                <?= $words["expenses"]; ?> (<?= $words["ever"]; ?>)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $totalExpense; ?><?= $coin; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <b class="bi w-auto euro"><?= $coin; ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card zoom border-left-primary <?= $lightTest == 1 ? "box-shadow-primary" : "" ?> h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?= $words["total"]; ?> (<?= $words["current"]; ?>)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $cash; ?><?= $coin; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <b class="bi w-auto euro"><?= $coin; ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card zoom border-left-violet <?= $lightTest == 1 ? "box-shadow-violet" : "" ?> h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-violet text-uppercase mb-1">
                                <?= $words["earnings"]; ?> (<?= $words["this"] . " " . $words["year"]; ?>)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $cashThisYear; ?><?= $coin; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <b class="bi w-auto euro"><?= $coin; ?></b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $words["summary"]; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2 mb-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger "><?php echo $add_expense; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success "><?php echo $add_revenue; ?></button>
            </div>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1"
                    type="button" id="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo $this_week; ?>
                </button>
                <ul class="dropdown-menu">
                    <li class="cur-pointer"><a class="dropdown-item" id="week"><?php echo $this_week; ?></a>
                    </li>
                    <li class="cur-pointer"><a class="dropdown-item" id="month"><?php echo $this_month; ?></a></li>
                    <li class="cur-pointer"><a class="dropdown-item" id="year"><?php echo $this_year; ?></a>
                    </li>
                </ul>
            </div>

        </div>
    </div>
    <canvas class="my-4 w-100" id="myChart" width="900" height="380"></canvas>
    <canvas class="my-4 w-100" id="myChart2" width="900" height="380" style="display: none;"></canvas>
    <canvas class="my-4 w-100" id="myChart3" width="900" height="380" style="display: none;"></canvas>


    <h2><?php echo $last_15_transactions; ?></h2>
    <div class="table-responsive small">
        <table class="table table-striped table-sm table-hover cur-pointer">
            <thead>
                <tr>
                    <th scope="col"><?php echo $date; ?></th>
                    <th scope="col"><?php echo $type_translate; ?></th>
                    <th scope="col"><?php echo $account; ?></th>
                    <th scope="col"><?php echo $value; ?></th>
                    <th scope="col">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result_transactions = get_transactions($conn, $user_id, 0, 0, 0, 1, 0, -1, 0);
                $i = $result_transactions->num_rows;
                $max = 15;
                while ($row = mysqli_fetch_assoc($result_transactions)) {

                    if ($row['type'] == 1) {
                        $color = "#195905";
                    } else
                        $color = "#e30022;";
                ?>
                    <tr onclick="goToviewTransaction(<?= $row['id']; ?>)" class="<?php if ($row['type'] == 1)
                                                                                        echo "success";
                                                                                    else
                                                                                        echo "danger";
                                                                                    if ($_COOKIE['mode'] == "light") {
                                                                                        if ($row['type'] == 1)
                                                                                            echo " table-success";
                                                                                        else
                                                                                            echo " table-danger";
                                                                                    }
                                                                                    ?>">
                        <td style="color: <?php echo $color; ?>"><?php echo $row['date']; ?></td>
                        <td style="color: <?php echo $color; ?>"><?php echo $type[$row['type']]; ?></td>
                        <td style="color: <?php echo $color; ?>">
                            <?php echo $accountInstance->getNameAccount($row['account_id']); ?>
                        </td>
                        <td style="color: <?php echo $color; ?>"><?php echo $row['value']; ?><?= $coin; ?></td>
                        <td style="color: <?php if ($total_transactions_table[$i - 1] > 0)
                                                echo "#195905;";
                                            else if ($total_transactions_table[$i - 1] < 0)
                                                echo "#e30022;" ?>">
                            <?php echo $total_transactions_table[$i - 1]; ?> <?= $coin; ?>
                        </td>
                    </tr>
                <?php
                    $i--;
                    $max--;
                    if ($max == 0)
                        break;
                } ?>
            </tbody>
        </table>
    </div>
</main>

<?php
include("../assets/js/charts/week-bar.php");
include("../assets/js/charts/month-bar.php");
include("../assets/js/charts/year-bar.php"); ?>