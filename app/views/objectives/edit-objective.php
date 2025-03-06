<?php
$this->layout("master");
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "manage goals";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
$objetive_id = $_GET['i'];
require_once "../backend/querys.php";
require_once "../backend/language.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $edit; ?> <?php echo $transaction_translate; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense" class="btn btn-sm btn-outline-danger">Add
                    Expense</button>
                <button type="button" id="revenue" class="btn btn-sm btn-outline-success">Add
                    Revenue</button>
            </div>

        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <form class="form-card" action="" method="POST">
                    <?php $result_edit = get_objective($conn, $user_id, $objetive_id, 1);
                    if (isset($result_edit)) {
                        while ($row = mysqli_fetch_assoc($result_edit)) { ?>
                            <div class="row justify-content-between text-left">
                                <div class="form-group col-sm-6 flex-column d-flex"> <label class="form-control-label px-3"><?= $name; ?><span class="text-danger-emphasis"> *</span></label>
                                    <input type="text" class="input" id="fname" name="name" placeholder="<?= $name; ?>..." value="<?php echo $row['name']; ?>" required>
                                </div>
                                <div class="form-group col-sm-6 flex-column d-flex">
                                    <label class="form-control-label px-3">Meta (Min: <?= $row['now']; ?><?= $coin; ?>) <span class="text-danger-emphasis"> *</span></label>
                                    <input type="number" class="input" id="fname" min="<?= $row['now']; ?>" name="value" placeholder="0.00<?= $coin; ?>" value="<?php echo $row['meta']; ?>" required>
                                </div>
                            </div>

                            <div class="row justify-content-end">
                                <div class="form-group col-sm-6"> <button type="submit"
                                        class="button btn btn-sm btn-outline-primary"><?php echo $edit; ?> <?php echo $objetive; ?></button> </div>
                        <?php }
                    } else
                        echo "ERROR"; ?>
                            </div>
                </form>
            </div>
        </div>
    </div>
</main>