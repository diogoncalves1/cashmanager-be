<?php
session_start();
$this->layout("master");
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "manage loans";
if (!isset($_COOKIE['user'])) {
    header("location: /CashManager/public/sign-up");
}
require_once "../backend/querys.php";
require_once "../backend/language.php";
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
        <h1 class="h2"><?php echo $loans; ?></h1>
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
                    <th scope="col" class="col-3"><?= $due_date; ?></th>
                    <th scope="col" class="col-3"><?php echo $creditor; ?></th>
                    <th scope="col" class="col-2"><?php echo $paid_value; ?></th>
                    <th scope="col" class="col-2"><?php echo $total_value; ?></th>
                    <th scope="col" class="col-1"><?php echo $edit; ?></th>
                    <th scope="col" class="col-1"><?php echo $delete ?></th>
                </tr>
            </thead>
            <tbody id="table">
                <?php foreach ($userLoans as $loan) { ?>
                <tr id="tr-<?= $loan['id'] ?>"
                    class="<?php if ($_COOKIE['mode'] == "light") { ?>table-primary <?php } ?>primary">
                    <td><?php echo $loan['date']; ?></td>
                    <td><?php echo $loan['creditor']; ?></td>
                    <td><?php echo $loan['paid']; ?><?= $coin; ?></td>
                    <td><?php echo $loan['total_value']; ?><?= $coin; ?></td>
                    <td><button style="background: none; border: none;"
                            onclick="goToEdit(<?php echo $loan['id']; ?>)"><svg class="bi">
                                <use xlink:href="#edit" />
                            </svg></button></td>
                    <td class="center"><button data-bs-toggle="modal" data-bs-target="#delete-modal"
                            style="background: none; border: none;" onclick="modal(<?php echo $loan['id']; ?>)"><svg
                                class="bi">
                                <use xlink:href="#delete" />
                            </svg></button></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

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
<script src="../assets/js/manage-loans.js"></script>