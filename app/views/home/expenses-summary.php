<link rel="stylesheet" href="../assets/css/expenses-summary.css">
<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = 'expenses summary';
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up.php");

require_once "../backend/querys.php";
require_once "../backend/language.php";
require "../backend/translate.php";

$dateNow = new DateTime("now");
$dateNow = $dateNow->format("Y-m-d");
$j = 0;
$i = 0;
while ($j != 1) {
    $curWeek = new DateTime("now");
    $curWeek = $curWeek->modify("-$i days");
    if ($curWeek->format("D") == "Sun")
        $j = 1;
    $i++;
}
$curWeek = $curWeek->format("Y-m-d");
$curMonth = new DateTime("now");
$curMonth = $curMonth->format("Y-m-01");
$curYear = new DateTime("now");
$curYear = $curYear->format("Y-01-01");
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $words["expenses_summary"]; ?></h1>
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
        <ul class="justify-content-center nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link exp-link active" onclick="makeGraph('<?= $dateNow ?>', 0)"><?= $today; ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link exp-link " onclick="makeGraph('<?= $curWeek ?>', 1)"><?= $this_week; ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link exp-link " onclick="makeGraph('<?= $curMonth ?>', 2)"><?= $this_month; ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link exp-link " onclick="makeGraph('<?= $curYear; ?>', 3)"><?= $this_year; ?></a>
            </li>
            <li class="nav-item">
                <input type="date" class="nav-link exp-link" value="<?= $dateNow ?>" max="<?= $dateNow; ?>"
                    onchange="makeGraph(this.value, 4)">
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-0 col-md-4"></div>
        <div class="col-12 col-md-4">
            <div id="chart">
                <canvas id="result-chart"></canvas>
            </div>
        </div>
        <div class="col-0 col-md-4"></div>
    </div>
    <div class="row">
        <div class="table-responsive">
            <table class="table table-striped table-sm table-hover">
                <thead>
                    <tr>
                        <th col="4"><?= $category; ?></th>
                        <th col="4"><?= $value; ?></th>
                        <th col="4"><?= $percentage; ?></thcol>
                    </tr>
                </thead>
                <tbody id="table">

                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="/CashManager/public/assets/js/expenses-summary.js"></script>
<script>
    makeGraph('<?= $dateNow; ?>', 0);
</script>