<?php $this->layout("master"); ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $translate["debts"]; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?= $translate["add_expense"]; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?= $translate["add_revenue"]; ?></button>
            </div>
        </div>
    </div>
    <button class="btn btn-outline-primary mb-4" id="add-debt"><?= $translate["add_debt"]; ?></button>
    <div class="table-responsive center">
        <table class="table table-bordered border align-middle text-center">
            <thead>
                <tr>
                    <th scope="col" class="col-3"><?= $translate["due_date"]; ?></th>
                    <th scope="col" class="col-3"><?= $translate["creditor"]; ?></th>
                    <th scope="col" class="col-2"><?= $translate["paid_value"]; ?></th>
                    <th scope="col" class="col-2"><?= $translate["total_value"]; ?></th>
                    <th scope="col" class="col-1"><?= $translate["actions"]; ?></th>
                </tr>
            </thead>
            <tbody id="table">
                <?php foreach ($userDebts as $debt) { ?>
                <tr id="tr-<?= $debt['id'] ?>"
                    class="<?php if ($_COOKIE['mode'] == "light") { ?>table-primary <?php } ?>primary">
                    <td><?= $debt['date']; ?></td>
                    <td><?= $debt['creditor']; ?></td>
                    <td><?= $debt['paid']; ?><?= $userCoin; ?></td>
                    <td><?php echo $debt['total_value']; ?><?= $userCoin; ?></td>
                    <td>
                        <?php if (hasPerm($debt["role_id"], "edit_debt")) { ?>
                        <button style="background: none; border: none;" onclick="goToEdit(<?php echo $debt['id']; ?>)">
                            <svg class="bi">
                                <use xlink:href="#edit" />
                            </svg>
                        </button>
                        <?php }
                            if (hasPerm($debt["role_id"], "delete_debt")) { ?>
                        <button data-bs-toggle="modal" data-bs-target="#delete-modal"
                            style="background: none; border: none;" onclick="modal(<?php echo $debt['id']; ?>)">
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

<script src="public/assets/js/debts/manage-debts.js"></script>