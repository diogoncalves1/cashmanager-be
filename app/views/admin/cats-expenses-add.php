<?php
require "../backend/querys.php";

if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");

if (is_admin($conn, $user_id)) {
    $this->layout("master-admin");
    $_SESSION['path'] = $_SERVER['REQUEST_URI'];
    $_SESSION['page'] = "add category expense";
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
                        <div class="row justify-content-between text-left">
                            <div class="form-group col-sm-12 flex-column d-flex">
                                <label class="form-control-label px-3"><?= $words[$lang]["code"]; ?>
                                    <span class="text-danger-emphasis"> *</span>
                                </label>
                                <input type="text" class="input" id="fname" name="code"
                                    placeholder="<?= $words[$lang]["code"]; ?>..." required>
                            </div>
                        </div>
                        <div class="row justify-content-between text-left">
                            <div class="form-group col-sm-4 flex-column d-flex">
                                <label class="form-control-label px-3"><?= $name_translate_en; ?>
                                    <span class="text-danger-emphasis"> *</span>
                                </label>
                                <input type="text" class="input" id="fname" name="EN"
                                    placeholder="<?= $name_translate_en; ?>..." required>
                            </div>
                            <div class="form-group col-sm-4 flex-column d-flex">
                                <label class="form-control-label px-3"><?= $name_translate_pt; ?></label>
                                <input type="text" class="input" id="fname" name="PT"
                                    placeholder="<?= $name_translate_pt; ?>..." required>
                            </div>
                            <div class="form-group col-sm-4 flex-column d-flex">
                                <label
                                    class="form-control-label px-3"><?= $words[$_COOKIE["lang"]]["sub_category"]; ?></label>
                                <input type="checkbox" class="input" id="fname" name="sub_cat" value="1"
                                    placeholder="<?= $name; ?>...">
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="form-group col-sm-6">
                                <button type="submit"
                                    class="button btn btn-sm btn-outline-primary"><?= $words[$_COOKIE["lang"]]["add_category_expense"] ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

<?php } ?>