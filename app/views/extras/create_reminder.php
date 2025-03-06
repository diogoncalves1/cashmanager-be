<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "create reminder";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
require_once "../backend/querys.php";
require_once "../backend/language.php";
$curDate = new DateTime("now");
$curDate = $curDate->format("Y-m-d");
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $create_reminder; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense" class="btn btn-sm btn-outline-danger"><?Php echo $add_expense; ?></button>
                <button type="button" id="revenue" class="btn btn-sm btn-outline-success"><?php echo $add_revenue ?></button>
            </div>

        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <form class="form-card" method="POST">
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-12 flex-column d-flex"> <label class="form-control-label px-3"><?Php echo $date ?><span class="text-danger-emphasis"> *</span></label> <input type="date" id="lname" min="<?php echo $curDate; ?>" class="input" name="date" required> </div>
                    </div>

                    <div class="row justify-content-between text-left">
                        <div class="form-group col flex-column d-flex pb-3"> <label class="form-control-label px-3"><?php echo $mensage; ?></label> <input type="text" class="input pb-5" id="fname" name="mensage" placeholder="<?Php echo $mensage; ?>..."> </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="submit" class="button btn btn-sm btn-outline-primary"><?php echo $create_reminder ?></button> </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>