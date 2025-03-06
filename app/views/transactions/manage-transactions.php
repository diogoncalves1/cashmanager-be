<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "manage transactions";
if (!isset($_COOKIE['user'])) {
    header("location: /CashManager/public/sign-up");
}
require_once "../backend/querys.php";
require_once "../backend/language.php";
if (isset($_GET['id']))
    $account_id = $_GET['id'];
if (isset($_GET['type']))
    $type_required = $_GET['type'];
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-sm-2">
    <?php if (isset($_SESSION["alert"])) { ?>
        <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
            <div id="checkToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="alert alert-success <?= $lightTest == 1 ? "box-shadow-success-alert" : "" ?> d-flex align-items-center mb-0" role="alert"><svg class="bi flex-shrink-0 me-2" role="img" aria-label="Success:">
                        <use xlink:href="#check-circle-fill" />
                    </svg>
                    <div><?= $_SESSION['alert'] ?></div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php
        unset($_SESSION["alert"]);
    }  ?>
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

    <div class="table-responsive">
        <table class="table text-center align-middle">
            <thead>
                <tr>
                    <th scope="col" class="col-2 cur-pointer dropdown-toggle" id="date-ord" onclick="sortDate(1)"><?php echo $date; ?></th>
                    <th scope="col" class="col-2 cur-pointer" id="value-ord" onclick="sortValue(0)"><?php echo $value; ?></th>
                    <th scope="col" class="col-3">Total</th>
                    <th scope="col" class="col-1"><?php echo $view ?></th>
                    <th scope="col" class="col-1"><?php echo $edit ?></th>
                    <th scope="col" class="col-1"><?php echo $delete ?></th>
                </tr>
            </thead>
            <tbody id="table">
                <?php
                $result_transactions = get_transactions($conn, $user_id, 0, 1);
                $i = $result_transactions->num_rows;
                $num_rows = 0;
                while ($row = mysqli_fetch_assoc($result_transactions)) {
                    if (!isset($account_id) || isset($account_id) && $account_id == $row['account_id']) {
                        if (!isset($type_required) || isset($type_required) && $row['type'] == $type_required) {
                            $date = new Datetime($row['date']);
                            $curDate = new Datetime("now");
                            if ($curDate->format("Y") == $date->format("Y"))
                                $date = $date->format("m-d");
                            else
                                $date = $date->format("Y-m-d");

                            if ($row['type'] == 1) {
                                $color = "#195905";
                            } else
                                $color = "#e30022;";
                ?>
                            <tr id="tr-<?= $row['id']; ?>" class=" transaction <?php if ($row['type'] == 1)
                                                                                    echo "success ";
                                                                                else
                                                                                    echo "danger ";
                                                                                if ($_COOKIE['mode'] == "light") { ?>table-<?php if ($row['type'] == 1)
                                                                                                                                echo "success";
                                                                                                                            else
                                                                                                                                echo "danger";
                                                                                                                        } ?>">
                                <td style="color: <?php echo $color; ?>" aria-valuenow="<?= $row['date']; ?>"><?php echo $date; ?></td>
                                <td style="color: <?php echo $color; ?>" aria-valuenow="<?= $row["value"]; ?>"><?php echo $row['value']; ?><?= $coin ?></td>
                                <td style="color: <?php if ($total_transactions_table[$i - 1] > 0)
                                                        echo "#195905;";
                                                    else if ($total_transactions_table[$i - 1] < 0)
                                                        echo "#e30022;" ?>">

                                    <?php
                                    if (hasPermission($conn, $row["role_id"], "edit_user_cash")) { ?>
                                        <?= $total_transactions_table[$i - 1]; ?> <?= $coin ?>
                                    <?php } ?>
                                </td>

                                <td style="color: <?php echo $color; ?>">
                                    <button style="background: none; border: none;"
                                        onclick="goToView(<?php echo $row['id']; ?>)"><svg class="bi"
                                            style="color: <?php echo $color; ?>">
                                            <use xlink:href="#view" />
                                        </svg>
                                    </button>
                                </td>
                                <td style="color: <?= $color; ?>">
                                    <?php if (hasPermission($conn, $row['role_id'], "edit_transactions")) { ?>
                                        <button style="background: none; border: none;"
                                            onclick="goToEdit(<?php echo $row['id']; ?>)"><svg class="bi"
                                                style="color: <?php echo $color; ?>">
                                                <use xlink:href="#edit" />
                                            </svg>
                                        </button>
                                    <?php } ?>
                                </td>
                                <td class="center" style="color: <?= $color; ?>">
                                    <?php if (hasPermission($conn, $row['role_id'], "delete_transactions")) { ?>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#delete-modal" style="background: none; border: none;"
                                            onclick="modal(<?php echo $row['id']; ?>)">
                                            <svg class="bi" style="color: <?php echo $color; ?>">
                                                <use xlink:href="#delete" />
                                            </svg>
                                        </button>
                                    <?php } ?>
                                </td>
                            </tr>
                <?php
                            $num_rows += 1;
                        }
                    }
                    $last_transaction = $row['value'];
                    $i--;
                } ?>
            </tbody>
        </table>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="delete-modal" tabindex="-1" aria-labelledby="delete-modalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="delete-modalLabel"><?= $delete; ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h5 class="mb-4"><?= $modal; ?>?</h5>
                    <button type="button" id="modal-btn" data-bs-dismiss="modal"
                        class="btn btn-success btn-lg me-2"><?= $confirm; ?></button>
                    <button type="button" class="btn btn-danger btn-lg "
                        data-bs-dismiss="modal"><?= $decline; ?></button>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="/CashManager/public/assets/js/manage-transactions.js"></script>