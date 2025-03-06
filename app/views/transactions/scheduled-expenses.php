<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "scheduled expenses";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");

require_once "../backend/querys.php";
require_once "../backend/language.php";
require "../backend/translate.php";
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-sm-2">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $words["scheduled_expenses"]; ?></h1>
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
        <table class="table table-bordered text-center align-middle">
            <thead>
                <tr>
                    <th scope="col" class="col-4"><?php echo $scheduled_date; ?></th>
                    <th scope="col" class="col-4"><?php echo $value; ?></th>
                    <th scope="col" class="col-1"><?php echo $view ?></th>
                    <th scope="col" class="col-1"><?php echo $edit ?></th>
                    <th scope="col" class="col-1"><?php echo $confirm ?></th>
                    <th scope="col" class="col-1"><?php echo $delete ?></th>
                </tr>
            </thead>
            <tbody id="table">
                <?php
                $result_transactions = get_scheduled_expenses($conn, $user_id);
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
                            $curDate = $curDate->format("Y-m-d");
                            if ($row['type'] == 1) {
                                $color = "#195905";
                            } else
                                $color = "#e30022;";

                ?>
                            <tr id="tr-<?= $row['id']; ?>" class="transaction <?php if ($row['type'] == 1)
                                                                                    echo "success ";
                                                                                else
                                                                                    echo "danger ";
                                                                                if ($_COOKIE['mode'] == "light") { ?>table-<?php if ($row['type'] == 1)
                                                                                                                                echo "success";
                                                                                                                            else
                                                                                                                                echo "danger";
                                                                                                                        } ?>">
                                <td style="color: <?php echo $color; ?>"><?php echo $date; ?></td>
                                <td style="color: <?php echo $color; ?>"><?php echo $row['value']; ?><?= $coin ?></td>
                                <td style="color: <?php echo $color; ?>">
                                    <button style="background: none; border: none;"
                                        onclick="goToView(<?php echo $row['id']; ?>)"><svg class="bi"
                                            style="color: <?php echo $color; ?>">
                                            <use xlink:href="#view" />
                                        </svg>
                                    </button>
                                </td>
                                <td style="color: <?php echo $color; ?>">
                                    <button style="background: none; border: none;"
                                        onclick="goToEdit(<?php echo $row['id']; ?>)"><svg class="bi"
                                            style="color: <?php echo $color; ?>">
                                            <use xlink:href="#edit" />
                                        </svg>
                                    </button>
                                </td>
                                <td style="color: <?php echo $color; ?>">
                                    <?php if ($curDate >= $row['date']) { ?>
                                        <button style="background: none; border: none;"
                                            onclick="confirm(<?php echo $row['id']; ?>)"><svg class="bi"
                                                style="color: <?php echo $color; ?>">
                                                <use xlink:href="#check" />
                                            </svg>
                                        </button>
                                    <?php } ?>
                                </td>
                                <td class="center" style="color: <?php echo $color; ?>"><button type="button" data-bs-toggle="modal"
                                        data-bs-target="#delete-modal" style="background: none; border: none;"
                                        onclick="modal(<?php echo $row['id']; ?>)"><svg class="bi"
                                            style="color: <?php echo $color; ?>">
                                            <use xlink:href="#delete" />
                                        </svg></button></td>
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
    <div class="modal fade" id="delete-modal" tabindex="-1" aria-labelledby="delete-modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="delete-modalLabel"><?= $delete; ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
<script src="/CashManager/public/assets/js/scheduled-expenses.js"></script>