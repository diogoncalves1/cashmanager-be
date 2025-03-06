<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "tools";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
require_once "../backend/querys.php";
require_once "../backend/language.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $tools; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense; ?></button>
                <button type="button" id="revenue" class="btn btn-sm btn-outline-success">
                    <?Php echo $add_revenue; ?>
                </button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <h3><?= $currency_converter ?></h3>
            <div class="col-12 text-center">
                <div class="row justify-content-between text-left">
                    <div class="form-group col-6 flex-column d-flex"> <label class="form-control-label px-3  ">
                            <?= $from ?>:</label>
                        <select id="from-currency">
                            <?php foreach ($coins as $coin) { ?>
                            <option value="<?= $coin['code'] ?>">
                                <?= $_COOKIE['lang'] == "EN" ? $coin['name'] : $coin['name_pt']; ?>
                                <?= $coin['symbol']; ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group col-6 flex-column d-flex">
                        <label class="form-control-label px-3"><?php echo $to; ?>:</label>
                        <select id="to-currency">
                            <?php foreach ($coins as $coin) { ?>
                            <option value="<?= $coin['code'] ?>">
                                <?= $_COOKIE['lang'] == "EN" ? $coin['name'] : $coin['name_pt']; ?>
                                <?= $coin['symbol']; ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row justify-content-between text-left">
                    <div class="form-group col-6 flex-column d-flex"> <label class="form-control-label px-3">
                            <?= $value ?></label>
                        <input type="number" step="0.01" id="amount">
                    </div>
                    <div class="col-6">
                        <label for=""><?= $convertValue ?></label>
                        <div class="card">
                            <div class="card-body" id="result">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row justify-content-end">
                    <div class="form-group col-sm-6"> <button type="button"
                            class="button btn btn-sm btn-outline-primary"
                            onclick="convert()"><?php echo $convert; ?></button>
                    </div>
                </div>
            </div>
            <h3><?= $compound_interest; ?></h3>
            <div class="col-12 text-center">
                <div class="row justify-content-between text-left">
                    <div class="form-group col-6 flex-column d-flex"> <label class="form-control-label px-3  ">
                            <?= $initial_investment ?></label>
                        <input type="number" step="0.01" id="initial-amount" placeholder="0.00€" required>
                    </div>

                    <div class="form-group col-6 flex-column d-flex">
                        <label class="form-control-label px-3"><?php echo $rate; ?></label>
                        <input type="number" step="0.01" id="rate" placeholder="10%">
                    </div>
                </div>
                <div class="row justify-content-between text-left">
                    <div class="form-group col-6 flex-column d-flex"> <label class="form-control-label px-3">
                            <?= $years ?></label>
                        <input type="number" step="0.01" id="years">
                    </div>
                    <div class="form-group col-6 flex-column d-flex"> <label class="form-control-label px-3">
                            <?= $reinforcement ?></label>
                        <input type="number" step="0.01" id="reinforcement">
                    </div>
                    <div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="button"
                                class="button btn btn-sm btn-outline-primary"
                                onclick="getInterest()"><?php echo $calculate; ?></button>
                        </div>
                    </div>
                    <div class="col-12" id="result-interest-body">
                        <div class="col-12">
                            <label for=""><?= $values ?></label>
                            <div class="card">
                                <div class="card-body" id="result-interest">
                                </div>
                            </div>
                        </div>
                        <div id="chart">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="../assets/js/tools.js"> </script>