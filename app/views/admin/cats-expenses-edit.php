<?php
require "../backend/querys.php";

if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");

if (is_admin($conn, $user_id)) {
    $this->layout("master-admin");
    $_SESSION['path'] = $_SERVER['REQUEST_URI'];
    $_SESSION['page'] = "edit category expense";
    $id = $_GET['i'];
    require_once "../backend/language.php";
    require "../backend/translate.php";
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
                    <form class="form-card" method="POST">
                        <?php $result_edit = get_all_category($conn, $id);
                        if (isset($result_edit)) {
                            while ($row = mysqli_fetch_assoc($result_edit)) { ?>
                                <div class="row justify-content-between text-left">
                                    <div class="form-group col-sm-4 flex-column d-flex">
                                        <label class="form-control-label px-3"><?= $name_translate_en; ?>
                                            <span class="text-danger-emphasis"> *</span>
                                        </label>
                                        <input type="text" class="input" id="fname" name="EN"
                                            placeholder="<?= $name_translate_en; ?>..." value="<?= $words["EN"][$row["code"]]; ?>"
                                            required>
                                    </div>
                                    <div class="form-group col-sm-4 flex-column d-flex">
                                        <label class="form-control-label px-3"><?= $name_translate_pt; ?></label>
                                        <input type="text" class="input" id="fname" name="PT"
                                            placeholder="<?= $name_translate_pt; ?>..." value="<?= $words["PT"][$row["code"]]; ?>"
                                            required>
                                    </div>
                                    <div class="form-group col-sm-4 flex-column d-flex">
                                        <label
                                            class="form-control-label px-3"><?= $words[$_COOKIE["lang"]]["sub_category"]; ?></label>
                                        <input type="checkbox" class="input" id="fname" name="sub_cat" value="1"
                                            placeholder="<?= $name; ?>..." <?php if ($row["sub-category"]) { ?> checked <?php } ?>>
                                    </div>
                                </div>

                                <div class="row justify-content-end">
                                    <div class="form-group col-sm-6">
                                        <button type="submit"
                                            class="button btn btn-sm btn-outline-primary"><?= $words[$_COOKIE["lang"]]["edit_category_expense"] ?>
                                        </button>
                                    </div>
                            <?php }
                        } else
                            echo "ERROR"; ?>
                                </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

<?php } ?>