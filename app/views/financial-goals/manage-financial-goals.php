<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "manage financial goals";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
require_once "../backend/querys.php";
require_once "../backend/language.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $financial_goals; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?php echo $add_revenue; ?></button>
            </div>
        </div>
    </div>
    <div class="table-responsive center">
        <table class="table table-bordered border align-middle text-center">
            <thead>
                <tr>
                    <th scope="col" class="col-4"><?php echo $name; ?></th>
                    <th scope="col" class="col-2"><?php echo $cash_translate; ?></th>
                    <th scope="col" class="col-2"><?= $now; ?></th>
                    <th scope="col" class="col-2"><?= $total_remaining; ?></th>
                    <th scope="col" class="col-1"><?php echo $edit; ?></th>
                    <th scope="col" class="col-1"><?php echo $delete ?></th>
                </tr>
            </thead>
            <tbody id="table">
                <?php
                $result_accounts = get_financial_goal($conn, $user_id, 0, 1);
                while ($row = mysqli_fetch_assoc($result_accounts)) { ?>
                <tr class="<?php if ($_COOKIE['mode'] == "light") { ?>table-primary <?php } ?>primary">
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['value']; ?><?= $coin ?></td>
                    <td><?= $row['earned_value'] ?><?= $coin ?></td>
                    <td><?= $row['value'] - $row['earned_value'] ?><?= $coin ?></td>
                    <td>
                        <?php if (hasPermission($conn, $row['role_id'], "edit_financial_goal")) { ?>
                        <button style="background: none; border: none;" onclick="goToEdit(<?= $row['id']; ?>)">
                            <svg class="bi">
                                <use xlink:href="#edit" />
                            </svg>
                        </button>
                        <?php } ?>
                    </td>
                    <td class="center">
                        <?php if (hasPermission($conn, $row['role_id'], "delete_financial_goal")) { ?>
                        <button data-bs-toggle="modal" data-bs-target="#delete-modal"
                            style="background: none; border: none;" onclick="modal(<?= $row['id']; ?>)">
                            <svg class="bi">
                                <use xlink:href="#delete" />
                            </svg>
                        </button>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
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
                    <h5 class="mb-1"><?= $modal_financial_goal; ?>?</h5>
                    <button type="button" id="modal-btn" data-bs-dismiss="modal"
                        class="btn btn-success btn-lg me-2"><?= $confirm; ?></button>
                    <button type="button" class="btn btn-danger btn-lg "
                        data-bs-dismiss="modal"><?= $decline; ?></button>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="/CashManager/public/assets/js/manage-financial-goals.js"></script>