<?php
$this->layout("master");
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "add debt";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
require_once "../backend/querys.php";
require_once "../backend/language.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $add_debt; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?Php echo $add_revenue; ?></button>
            </div>
        </div>
    </div>
    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <form class="form-card" method="POST">
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3"><?= $creditor; ?>
                                <span class="text-danger-emphasis"> *</span>
                            </label>
                            <input type="text" id="lname" placeholder="<?= $creditor ?>..." class="input" name="creditor" required>
                        </div>
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3"><?= $total_value; ?>
                                <span class="text-danger-emphasis"> *</span>
                            </label>
                            <input type="number" class="input" step="0.01" max="<?= $maxAcc; ?>" id="input-value"
                                name="total_value" placeholder="1000.00<?= $coin ?>" required>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-12 flex-column d-flex pb-3">
                            <label class="form-control-label px-3"><?= $due_date; ?>
                                <span class="text-danger-emphasis"> *</span>
                            </label>
                            <input type="date" class="input" id="fname" name="due_date" required>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-9 flex-column d-flex">
                            <label class="form-control-label px-3"><?= $rate; ?>
                                <span>(<?php echo $optional; ?>)</span>
                            </label>
                            <input type="number" class="input" id="input-value" name="rate" placeholder="10%">
                        </div>
                        <div class="form-check col-sm-3 mt-5">
                            <input type="checkbox" class="form-check-input p-2" name="compound_interest">
                            <label class="form-check-label"><?= $compound_interest; ?></label>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-check col-sm-1 mt-5">
                            <input type="checkbox" class="form-check-input p-2 " name="installment" onchange="addInputs(this.checked)">
                            <label class="form-check-label"><?= $installment; ?></label>
                        </div>
                        <div class="form-group col-sm-5 flex-column d-none" id="n-inst">
                            <label class="form-control-label px-3"><?= $number_installments; ?>
                                <span class="text-danger-emphasis"> *</span>
                            </label>
                            <input type="number" min="2" class="input" id="input-n" name="n_installments" placeholder="10...">
                        </div>
                        <div class="form-group col-sm-5 flex-column d-none" id="value-inst">
                            <label class="form-control-label px-3"><?= $value_installments; ?>
                                <span class="text-danger-emphasis"> *</span>
                            </label>
                            <input type="number" step="0.01" min="0.01" placeholder="100<?= $coin; ?>..." class="input" id="input-v" name="value_installments">
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col flex-column d-flex pb-3">
                            <label class="form-control-label px-3"><?php echo $description; ?>
                                <span>(<?php echo $optional; ?>)</span>
                            </label>
                            <input type="text" class="input pb-5" id="fname" name="description"
                                placeholder="<?Php echo $description; ?>...">
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="form-group col-sm-6">
                            <button type="submit"
                                class="button btn btn-sm btn-outline-danger"><?php echo $add_debt; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="/CashManager/assets/js/add-debt.js"></script>