<?php

use app\Models\Account;

$this->layout("master");
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "view transaction";
if (!isset($_COOKIE['user'])) {
    header("location: /CashManager/public/sign-up");
}
require_once "../backend/querys.php";
require_once "../backend/language.php";


$accountInstance = new Account();
$transaction_id = $_GET['i'];
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-sm-2">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $transactions; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?php echo $add_revenue ?></button>
            </div>
        </div>
    </div>
    <?php
    $result_transaction = get_transactions($conn, $user_id, $transaction_id, 1);
    $i = $result_transaction->num_rows;
    while ($row = mysqli_fetch_assoc($result_transaction)) {
    ?>
    <div class="table-responsive">
        <table class="table table-bordered <?php if ($row['type'] == 1)
                                                    echo "success";
                                                else
                                                    echo "danger";
                                                if ($_COOKIE['mode'] == "light") { ?>  table-<?php if ($row['type'] == 1)
                                                                                                    echo "success";
                                                                                                else
                                                                                                    echo "danger";
                                                                                            } ?>">
            <thead>
                <tr>
                    <th scope="col" class="col-3"><?php echo $date; ?></th>
                    <th scope="col" class="col-2"><?php echo $type_translate; ?></th>
                    <th scope="col" class="col-2"><?php echo $account; ?></th>
                    <th scope="col" class="col-2"><?= $row['type'] == 0 ? $to : $from; ?></th>
                    <th scope="col" class="col-1"><?php echo $value; ?></th>
                    <?php if ($row['type'] == 0) { ?>
                    <th scope="col" class="col-2"><?php echo $category ?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody id="table">
                <?php


                    if ($row['type'] == 1) {
                        $color = "#195905";
                    } else
                        $color = "#e30022;";

                    ?>
                <tr>
                    <td style="color: <?php echo $color; ?>"><?php echo $row['date']; ?></td>
                    <td style="color: <?php echo $color; ?>"><?php echo $type[$row['type']]; ?></td>
                    <td style="color: <?php echo $color; ?>">
                        <?php echo $accountInstance->getNameAccount($row["account_id"]); ?>
                    </td>
                    <td style="color: <?php echo $color; ?>"><?php echo $row['to_p']; ?></td>
                    <td style="color: <?php echo $color; ?>"><?php echo $row['value']; ?><?= $coin ?></td>
                    <?php if ($row['type'] == 0) { ?>
                    <td style="color: <?php echo $color; ?>">
                        <?= get_category_name($conn, $row['cat_id']); ?>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td colspan="7">
                        <table class="table <?php if ($row['type'] == 1)
                                                    echo "success";
                                                else
                                                    echo "danger";
                                                if ($_COOKIE['mode'] == "light") { ?>  table-<?php if ($row['type'] == 1)
                                                                                                    echo "success";
                                                                                                else
                                                                                                    echo "danger";
                                                                                            } ?>">
                            <thead>
                                <th><?= $description; ?></th>
                            </thead>
                            <tbody>
                                <tr">
                                    <td style="color: <?php echo $color; ?>">
                                        <?php echo $row['description']; ?>
                                    </td>
                </tr>
            </tbody>
        </table>
        </td>
        </tr>
        <?php if ($row['proof'] != null) { ?>
        <tr>
            <td colspan="7">
                <table class="table <?php if ($row['type'] == 1)
                                                echo "success";
                                            else
                                                echo "danger";
                                            if ($_COOKIE['mode'] == "light") { ?>  table-<?php if ($row['type'] == 1)
                                                                                                echo "success";
                                                                                            else
                                                                                                echo "danger";
                                                                                        } ?>">
                    <thead>
                        <th><?= $proof_of_payment; ?></th>
                    </thead>
                    <tbody>
                        <tr class="d-flex">
                            <td class="mx-auto p-2" style="color: <?php echo $color; ?>">
                                <img width="100%" src="assets/images/proofs/<?= $row['proof']; ?>" alt="">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php }
            $last_transaction = $row['value'];
            $i--;
        } ?>
        </tbody>
        </table>
    </div>
</main>