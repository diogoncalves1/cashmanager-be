<?php $this->layout("master"); ?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $translate["financial_goals"]; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?= $translate["add_expense"]; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?= $translate["add_revenue"]; ?></button>
            </div>
        </div>
    </div>
    <button class="btn btn-outline-primary mb-4"
        id="add-financial-goal"><?= $translate["add_financial_goal"]; ?></button>
    <div class="table-responsive center">
        <table class="table table-bordered border align-middle text-center">
            <thead>
                <tr>
                    <th scope="col" class="col-3"><?= $translate["name"]; ?></th>
                    <th scope="col" class="col-3"><?= $translate["value"]; ?></th>
                    <th scope="col" class="col-2"><?= $translate["amount_invested"]; ?></th>
                    <th scope="col" class="col-2"><?= $translate["missing_amount"]; ?></th>
                    <th scope="col" class="col-1"><?= $translate["actions"]; ?></th>
                </tr>
            </thead>
            <tbody id="table">
                <?php foreach ($userFinancialGoals as $financialGoal) { ?>
                <tr id="financial-goal-<?= $financialGoal["id"]; ?>">
                    <td><?= $financialGoal['name']; ?></td>
                    <td><?= $financialGoal['value']; ?><?= $userCoin ?></td>
                    <td><?= $financialGoal['amount_invested'] ?><?= $userCoin ?></td>
                    <td><?= $financialGoal['value'] - $financialGoal['amount_invested'] ?><?= $userCoin ?></td>
                    <td>
                        <?php if (hasPerm($financialGoal['role_id'], "edit_financial_goal")) { ?>
                        <button style="background: none; border: none;"
                            onclick="goToEdit(<?= $financialGoal['id']; ?>)">
                            <svg class="bi">
                                <use xlink:href="#edit" />
                            </svg>
                        </button>
                        <?php }
                            if (hasPerm($financialGoal['role_id'], "delete_financial_goal")) { ?>
                        <button data-bs-toggle="modal" data-bs-target="#delete-modal"
                            style="background: none; border: none;" onclick="modal(<?= $financialGoal['id']; ?>)">
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
<script src="public/assets/js/financial-goals/manage-financial-goals.js"></script>