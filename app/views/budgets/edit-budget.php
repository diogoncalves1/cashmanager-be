<?php
$this->layout("master");
if (isset($_GET['i']))
    $limit_id = $_GET['i'];
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "manage limits";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
require_once "../backend/querys.php";
require_once "../backend/language.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $edit; ?> <?php echo $limits; ?></h1>
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
                    <?php $result_edit = get_limit($conn, $limit_id, 0, $user_id);
                    if (isset($result_edit)) {
                        while ($row = mysqli_fetch_assoc($result_edit)) { ?>
                            <div class="row justify-content-between text-left">
                                <div class="form-group col-12 flex-column d-flex"> <label
                                        class="form-control-label px-3"><?= $max_translate; ?>: <span
                                            class="text-danger-emphasis"> *</span></label>
                                    <input type="text" class="input" id="fname" name="max"
                                        placeholder="<?= $max_translate; ?>: 10<?= $coin; ?>..."
                                        value="<?php echo $row['max']; ?>" required>
                                </div>
                            </div>

                            <div class="row justify-content-end">
                                <div class="form-group col-sm-6"> <button type="submit"
                                        class="button btn btn-sm btn-outline-primary"><?php echo $edit; ?>
                                        <?php echo $limit_translate; ?></button> </div>
                        <?php }
                    } else
                        echo "ERROR"; ?>
                            </div>
                </form>
            </div>
        </div>
    </div>
</main>