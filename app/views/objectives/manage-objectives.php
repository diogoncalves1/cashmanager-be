<?php $this->layout("master"); ?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-sm-2">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $translate["objectives"]; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?= $translate["add_expense"] ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?= $translate["add_revenue"] ?></button>
            </div>
        </div>
    </div>
    <button class="btn btn-outline-primary mb-4" id="add-objective"><?= $translate["create_objective"]; ?></button>
    <div class="table-responsive">
        <table class="table table-sm table-bordered text-center align-middle">
            <thead>
                <tr>
                    <th scope="col" class="col-2"><?= $translate["name"]; ?></th>
                    <th scope="col" class="col-3"><?= $translate["objective_value"]; ?></th>
                    <th scope="col" class="col-2"><?= $translate["amount_invested"]; ?></th>
                    <th scope="col" class="col-2"><?= $translate["missing_amount"]; ?></th>
                    <th scope="col" class="col-1"><?= $translate["actions"] ?></th>
                </tr>
            </thead>
            <tbody id="table">
                <?php foreach ($userObjectives as $objective) {  ?>
                <tr id="tr-<?= $objective['id']; ?>">
                    <td><?= $objective['name']; ?></td>
                    <td><?= $objective['objective_value']; ?><?= $userCoin ?></td>
                    <td><?= $objective['amount_invested']; ?><?= $userCoin ?></td>
                    <td><?= $objective['objective_value'] - $objective['amount_invested']; ?><?= $userCoin ?></td>
                    <td>
                        <?php if (hasPerm($objective["role_id"], "edit_objective")) { ?>
                        <button style="background: none; border: none;"
                            onclick="goToEdit(<?php echo $objective['id']; ?>)"><svg class="bi">
                                <use xlink:href="#edit" />
                            </svg>
                        </button>
                        <?php }
                            if (hasPerm($objective["role_id"], "delete_objective")) { ?>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#delete-modal"
                            style="background: none; border: none;" onclick="modal(<?= $objective['id']; ?>)">
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
</main>
<script src="/CashManager/public/assets/js/manage-objectives.js"></script>