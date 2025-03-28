<?php $this->layout("master"); ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-sm-2">
    <?php if (isset($_SESSION["alert"])) { ?>
        <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
            <div id="checkToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="alert alert-success <?= $lightTest == 1 ? "box-shadow-success-alert" : "" ?> d-flex align-items-center mb-0"
                    role="alert"><svg class="bi flex-shrink-0 me-2" role="img" aria-label="Success:">
                        <use xlink:href="#check-circle-fill" />
                    </svg>
                    <div><?= $_SESSION['alert'] ?></div><button type="button" class="btn-close me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php
        unset($_SESSION["alert"]);
    }  ?>
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $translate["transactions"]; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?= $translate["add_expense"] ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?= $translate["add_revenue"] ?></button>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table text-center align-middle">
            <thead>
                <tr>
                    <th scope="col" class="col-2 cur-pointer dropdown-toggle" id="date-ord" onclick="sortDate(1)">
                        <?= $translate["date"]; ?></th>
                    <th scope="col" class="col-2 cur-pointer" id="value-ord" onclick="sortValue(0)">
                        <?= $translate["value"]; ?></th>
                    <th scope="col" class="col-3"><?= $translate["total"]; ?></th>
                    <th scope="col" class="col-1"><?= $translate["actions"]; ?></th>
                </tr>
            </thead>
            <tbody id="table">
                <?php $i = count($userTransactions);
                foreach ($userTransactions as $transaction) {
                    if (!isset($account_id) || isset($account_id) && $account_id == $transaction['account_id']) {
                        if (!isset($type_required) || isset($type_required) && $transaction['type'] == $type_required) {
                            $date = new Datetime($transaction['date']);
                            $curDate = new Datetime("now");
                            if ($curDate->format("Y") == $date->format("Y"))
                                $date = $date->format("m-d");
                            else
                                $date = $date->format("Y-m-d");

                            if ($transaction['type'] == "revenue") {
                                $color = "#195905";
                            } else
                                $color = "#e30022;"; ?>
                            <tr id="tr-<?= $transaction['id']; ?>" class="transaction">
                                <td style="color: <?php echo $color; ?>" aria-valuenow="<?= $transaction['date']; ?>">
                                    <?= $date; ?>
                                </td>
                                <td style="color: <?= $color; ?>" aria-valuenow="<?= $transaction["value"]; ?>">
                                    <?= $transaction['value']; ?><?= $userCoin ?></td>
                                <td style="color: <?php if ($totalUser[$i - 1] > 0)
                                                        echo "#195905;";
                                                    else if ($totalUser[$i - 1] < 0)
                                                        echo "#e30022;" ?>">

                                    <?php
                                    if (hasPerm($transaction["role_id"], "edit_user_cash")) { ?>
                                        <?= $transaction["total_value"]; ?> <?= $userCoin ?>
                                    <?php } ?>
                                </td>

                                <td style="color: <?php echo $color; ?>">
                                    <button style="background: none; border: none;"
                                        onclick="goToView(<?php echo $transaction['id']; ?>)"><svg class="bi"
                                            style="color: <?php echo $color; ?>">
                                            <use xlink:href="#view" />
                                        </svg>
                                    </button>
                                    <?php if (hasPerm($transaction['role_id'], "edit_transactions")) { ?>
                                        <button style="background: none; border: none;"
                                            onclick="goToEdit(<?php echo $transaction['id']; ?>)"><svg class="bi"
                                                style="color: <?php echo $color; ?>">
                                                <use xlink:href="#edit" />
                                            </svg>
                                        </button>
                                    <?php } ?>
                                    <?php if (hasPerm($transaction['role_id'], "delete_transactions")) { ?>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#delete-modal"
                                            style="background: none; border: none;" onclick="modal(<?php echo $transaction['id']; ?>)">
                                            <svg class="bi" style="color: <?php echo $color; ?>">
                                                <use xlink:href="#delete" />
                                            </svg>
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                <?php
                        }
                    }
                    $last_transaction = $transaction['value'];
                    $i--;
                } ?>
            </tbody>
        </table>

    </div>
</main>
<script src="/CashManager/public/assets/js/manage-transactions.js"></script>