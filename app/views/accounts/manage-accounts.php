<?php
session_start();
$this->layout("master");
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "manage accounts";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");

require_once "../backend/querys.php";
require_once "../backend/language.php";
require "../backend/translate.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="alert alert-success <?= $lightTest == 1 ? "box-shadow-success-alert" : "" ?> d-flex align-items-center mb-0"
                role="alert"><svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:">
                    <use xlink:href="#check-circle-fill" />
                </svg>
                <div><?= $account_deleted ?></div><button type="button" class="btn-close me-2 m-auto"
                    data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="liveToastWarning" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="alert alert-danger <?= $lightTest == 1 ? "box-shadow-danger" : "" ?> d-flex align-items-center mb-0"
                role="alert"><svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:">
                    <use xlink:href="#exclamation-triangle-fill" />
                </svg>
                <div><?= $only_owner_can_delete; ?></div><button type="button" class="btn-close me-2 m-auto"
                    data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <?php if (isset($_SESSION["alert"])) { ?>
            <div id="checkToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="alert alert-success <?= $lightTest == 1 ? "box-shadow-success-alert" : "" ?> d-flex align-items-center mb-0"
                    role="alert"><svg class="bi flex-shrink-0 me-2" role="img" aria-label="Success:">
                        <use xlink:href="#check-circle-fill" />
                    </svg>
                    <div><?= $_SESSION['alert'] ?></div><button type="button" class="btn-close me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php
            unset($_SESSION["alert"]);
        }  ?>
    </div>


    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $accounts; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?php echo $add_revenue; ?></button>
            </div>
        </div>
    </div>
    <button class="btn btn-outline-primary mb-4" id="add-account"><?= $words["create_account"]; ?></button>
    <div class="table-responsive center">
        <table class="table table-bordered border align-middle text-center">
            <thead>
                <tr>
                    <th scope="col" class="col-6"><?php echo $name; ?></th>
                    <th scope="col" class="col-3"><?php echo $cash_translate; ?></th>
                    <th scope="col" class="col-1"><?= $transactions; ?></th>
                    <th scope="col" class="col-1"><?php echo $edit; ?></th>
                    <th scope="col" class="col-1"><?php echo $delete ?></th>
                </tr>
            </thead>
            <tbody id="table">
                <?php
                foreach ($userAccounts as $account) {
                    if (hasPermission($conn, $account["role_id"], "view_accounts")) { ?>
                        <tr id="tr-<?= $account['id'] ?>"
                            class="<?php if ($_COOKIE['mode'] == "light") { ?>table-primary <?php } ?>primary">
                            <td><?= $account['name']; ?></td>
                            <td><?= $account['cash']; ?><?= $coin; ?></td>
                            <td>
                                <button style="background: none; border: none;" onclick="goToView(<?= $account['id']; ?>)"><svg
                                        class="bi">
                                        <use xlink:href="#view" />
                                    </svg>
                                </button>
                            </td>
                            <td>
                                <?php if (hasPermission($conn, $account["role_id"], "edit_account")) { ?>
                                    <button style="background: none; border: none;" onclick="goToEdit(<?= $account['id']; ?>)">
                                        <svg class="bi">
                                            <use xlink:href="#edit" />
                                        </svg>
                                    </button>
                                <?php } ?>
                            </td>
                            <td class="center">
                                <?php if (hasPermission($conn, $account["role_id"], "delete_account")) { ?>
                                    <button data-bs-toggle="modal" data-bs-target="#delete-modal"
                                        style="background: none; border: none;" onclick="modal(<?= $account['id']; ?>)">
                                        <svg class="bi">
                                            <use xlink:href="#delete" />
                                        </svg>
                                    </button>
                                <?php } ?>
                            </td>
                        </tr>
                <?php }
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
                    <h5 class="mb-1"><?= $modal_acc; ?>?</h5>
                    <h6 class="mb-4"><?= $modal_acc2; ?></h4>
                        <button type="button" id="modal-btn" data-bs-dismiss="modal"
                            class="btn btn-success btn-lg me-2 liveToastBtn"><?= $confirm; ?></button>
                        <button type="button" class="btn btn-danger btn-lg "
                            data-bs-dismiss="modal"><?= $decline; ?></button>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="/CashManager/public/assets/js/manage-accounts.js"></script>