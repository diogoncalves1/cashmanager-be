<?php
session_start();
$this->layout("master");
if (isset($_GET['i']))
    $financial_goal_id = $_GET['i'];
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "manage financial goals";
if (!isset($_COOKIE['user'])) {
    header("location: /CashManager/public/sign-up");
}
require_once "../backend/querys.php";
require_once "../backend/language.php";
$curDate = new DateTime("now");
$curDate = $curDate->format("Y-m-d");
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $edit; ?> <?php echo $transaction_translate; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?php echo $add_revenue; ?></button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <form class="form-card" method="POST">
                    <?php $result_edit = get_financial_goal($conn, $user_id, $financial_goal_id);
                    if (isset($result_edit)) {
                        while ($row = mysqli_fetch_assoc($result_edit)) { ?>

                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3  "><?php echo $goal; ?>
                                <span class="text-danger-emphasis">*</span>
                            </label>
                            <input type="number" class="input" step="0.01" min="0.01" id="input-value" name="value"
                                value="<?= $row['value']; ?>" placeholder="0.00<?= $coin; ?>" required>
                        </div>
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3"><?php echo $name; ?>
                                <span class="text-danger-emphasis"> *</span>
                            </label>
                            <input type="text" id="lname" class="input" name="name" value="<?= $row['name']; ?>"
                                placeholder="<?= $name; ?>..." required>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3"><?php echo $initial_date_translate; ?>
                                <span class="text-danger-emphasis"> *</span>
                            </label>
                            <input onchange="verifyDate()" type="date" min="<?= $curDate; ?>" id="initial-date"
                                class="input" max="<?= $row['final_date']; ?>" value="<?= $row['start_date']; ?>"
                                name="start_date" required>
                        </div>
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3"><?php echo $final_date_translate; ?>
                                <span class="text-danger-emphasis"> *</span>
                            </label>
                            <input onchange="verifyDate()" type="date" min="<?= $row['start_date']; ?>" id="final-date"
                                class="input" value="<?= $row['final_date']; ?>" name="final_date" required>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-12 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $category; ?><span
                                    class="text-danger-emphasis"> *</span></label>
                            <select name="category" required>
                                <?php foreach ($financialGoalCategorys as $financialGoalCategory) { ?>
                                <option value="<?php echo $financialGoalCategory['id']; ?>" <?php if ($financialGoalCategory['id'] == $row['cat_id'])
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
                                class="form-control-label px-3"><?php echo $status_translate; ?><span
                                    class="text-danger-emphasis"> *</span></label>
                            <select name="status" required>
                                <?php foreach ($statusGoals as $statusGoal) { ?>
                                <option value="<?php echo $statusGoal['id']; ?>" <?php if ($statusGoal['id'] == $row['status_id'])
                                                    echo "selected";  ?>>
                                    <?php if ($_COOKIE['lang'] == "EN")
                                                    echo $statusGoal['name'];
                                                else
                                                    echo $statusGoal['name_pt']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $priority_translate; ?><span
                                    class="text-danger-emphasis"> *</span></label>
                            <select name="priority" required>
                                <?php foreach ($priorities as $priority) { ?>
                                <option value="<?php echo $priority['id']; ?>" <?php if ($priority['id'] == $row['priority_id'])
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
                        <div class="form-group col-sm-6"> <button type="submit"
                                class="button btn btn-sm btn-outline-danger"><?php echo $edit_financial_goal; ?></button>
                        </div>
                    </div>
                    <?php }
                    } else
                        echo "ERROR"; ?>
            </div>
            </form>
        </div>
</main>