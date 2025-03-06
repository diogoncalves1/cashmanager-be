<?php
$this->layout("master");
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "edit account";
if (!isset($_COOKIE['user'])) {
    header("location: /Cash_manager/frontend/sing-up");
}
require_once "../backend/querys.php";
require_once "../backend/language.php";
require "../backend/translate.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $words["edit_account"]; ?></h1>
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
                <form class="form-card" method="POST">
                    <?php
                    if (hasPermission($conn, $accountUser["role_id"], "view_accounts") && hasPermission($conn, $accountUser["role_id"], "edit_account")) { ?>
                        <div class="row justify-content-between text-left">
                            <div class="form-group col-12 flex-column d-flex"> <label
                                    class="form-control-label px-3"><?= $name; ?><span class="text-danger-emphasis">
                                        *</span></label>
                                <input type="text" class="input" id="fname" name="name" placeholder="<?= $name; ?>..."
                                    value="<?= $accountUser['name']; ?>" required>
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="form-group col-sm-6"> <button type="submit"
                                    class="button btn btn-sm btn-outline-primary"><?= $words["edit_account"]; ?></button>
                            </div>
                        <?php } else {
                        echo "ERROR";
                    }
                        ?>
                        </div>
                </form>
            </div>
        </div>
    </div>
</main>