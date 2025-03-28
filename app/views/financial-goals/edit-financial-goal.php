<?php $this->layout("master"); ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $translate["edit_financial_goal"]; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?= $translate["add_expense"]; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?= $translate["add_revenue"]; ?></button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <?php if ($financialGoal) { ?>
                <div class="row justify-content-between text-left">
                    <div class="form-group col-sm-6 flex-column d-flex">
                        <label class="form-control-label px-3  "><?= $translate["value"]; ?>
                            <span class="text-danger-emphasis">*</span>
                        </label>
                        <input type="number" class="input" step="0.01" min="0.01" id="input-value" name="value"
                            value="<?= $financialGoal['value']; ?>" placeholder="0.00<?= $userCoin; ?>" required>
                    </div>
                    <div class="form-group col-sm-6 flex-column d-flex">
                        <label class="form-control-label px-3"><?= $translate["name"]; ?>
                            <span class="text-danger-emphasis"> *</span>
                        </label>
                        <input type="text" id="name-ipt" class="input" name="name"
                            value="<?= $financialGoal['name']; ?>" placeholder="<?= $translate["name"]; ?>..." required>
                    </div>
                </div>
                <div class="row justify-content-between text-left">
                    <div class="form-group col-sm-6 flex-column d-flex">
                        <label class="form-control-label px-3"><?= $translate["initial_date"]; ?>
                            <span class="text-danger-emphasis"> *</span>
                        </label>
                        <input onchange="verifyDate()" type="date" min="<?= $curDate; ?>" id="initial-date"
                            class="input" max="<?= $financialGoal['final_date']; ?>"
                            value="<?= $financialGoal['start_date']; ?>" name="start_date" required>
                    </div>
                    <div class="form-group col-sm-6 flex-column d-flex">
                        <label class="form-control-label px-3"><?= $translate["final_date"]; ?>
                            <span class="text-danger-emphasis"> *</span>
                        </label>
                        <input onchange="verifyDate()" type="date" min="<?= $financialGoal['start_date']; ?>"
                            id="final-date" class="input" value="<?= $financialGoal['final_date']; ?>" name="final_date"
                            required>
                    </div>
                </div>
                <div class="row justify-content-between text-left">
                    <div class="form-group col-sm-12 flex-column d-flex"> <label
                            class="form-control-label px-3"><?= $translate["category"]; ?><span
                                class="text-danger-emphasis"> *</span></label>
                        <select name="category" id="category-select" required>
                            <?php foreach ($financialGoalCategorys as $financialGoalCategory) { ?>
                            <option value="<?php echo $financialGoalCategory['id']; ?>"
                                <?php if ($financialGoalCategory['id'] == $financialGoal['cat_id'])
                                                                                                    echo "selected"; ?>>
                                <?php if ($_COOKIE['lang'] == "EN")
                                            echo $financialGoalCategory['name'];
                                        else
                                            echo $financialGoalCategory['name_pt']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row justify-content-between text-left">
                    <div class="form-group col-sm-6 flex-column d-flex"> <label
                            class="form-control-label px-3"><?= $translate["status"]; ?><span
                                class="text-danger-emphasis"> *</span></label>
                        <select name="status" id="status-select" required>
                            <?php foreach ($statusGoals as $statusGoal) { ?>
                            <option value="<?php echo $statusGoal['id']; ?>" <?php if ($statusGoal['id'] == $financialGoal['status_id'])
                                                                                            echo "selected";  ?>>
                                <?php if ($_COOKIE['lang'] == "EN")
                                            echo $statusGoal['name'];
                                        else
                                            echo $statusGoal['name_pt']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-6 flex-column d-flex"> <label
                            class="form-control-label px-3"><?= $translate["priority"]; ?><span
                                class="text-danger-emphasis"> *</span></label>
                        <select name="priority" id="priority-select" required>
                            <?php foreach ($priorities as $priority) { ?>
                            <option value="<?php echo $priority['id']; ?>" <?php if ($priority['id'] == $financialGoal['priority_id'])
                                                                                        echo "selected"; ?>>
                                <?php if ($_COOKIE['lang'] == "EN")
                                            echo $priority['name'];
                                        else
                                            echo $priority['name_pt']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row justify-content-end">
                    <div class="form-group col-sm-6">
                        <button type="submit" id="submit-btn"
                            class="button btn btn-sm btn-outline-danger"><?= $translate["edit_financial_goal"]; ?></button>
                    </div>
                </div>
                <?php } else
                    echo "ERROR"; ?>
            </div>
        </div>
</main>

<script src="../../public/assets/js/financial-goals/edit-financial-goal.js"></script>