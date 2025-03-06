<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "add financial goal";
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
        <h1 class="h2"><?php echo $add_financial_goal; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?Php echo $add_revenue; ?></button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <form class="form-card" method="POST">
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3  "><?php echo $goal; ?>
                                <span class="text-danger-emphasis">*</span>
                            </label>
                            <input type="number" class="input" step="0.01" min="0.01" id="input-value"
                                name="value" placeholder="0.00<?= $coin; ?>" required>
                        </div>
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3"><?php echo $name; ?>
                                <span class="text-danger-emphasis"> *</span>
                            </label>
                            <input type="text" id="lname" class="input" name="name"
                                placeholder="<?= $name; ?>..." required>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3"><?php echo $initial_date_translate; ?>
                                <span class="text-danger-emphasis"> *</span>
                            </label>
                            <input onchange="verifyDate()" type="date" min="<?= $curDate; ?>"
                                id="initial-date" class="input" name="start_date" required>
                        </div>
                        <div class="form-group col-sm-6 flex-column d-flex">
                            <label class="form-control-label px-3"><?php echo $final_date_translate; ?>
                                <span class="text-danger-emphasis"> *</span>
                            </label>
                            <input onchange="verifyDate()" type="date" min="<?= $curDate; ?>"
                                id="final-date" class="input" name="final_date" required>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-12 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $category; ?><span
                                    class="text-danger-emphasis"> *</span></label>
                            <select name="category" required>
                                <?php $result_cats = get_goal_category($conn);
                                while ($row = mysqli_fetch_assoc($result_cats)) {
                                    if ($row['id'] != 33) {
                                        if ($row['sub-category'] == 1) { ?>
                                            </optgroup>
                                            <optgroup label="<?php if ($_COOKIE['lang'] == "EN")
                                                                    echo $row['name'];
                                                                else
                                                                    echo $row['name_pt']; ?>">
                                            <?php } ?>
                                            <option value="<?php echo $row['id']; ?>"><?php if ($_COOKIE['lang'] == "EN")
                                                                                            echo $row['name'];
                                                                                        else
                                                                                            echo $row['name_pt']; ?></option>
                                    <?php }
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $status_translate; ?><span
                                    class="text-danger-emphasis"> *</span></label>
                            <select name="status" required>
                                <?php $result_status = get_status_goal($conn);
                                while ($row = mysqli_fetch_assoc($result_status)) {
                                    if ($row['id'] != 33) { ?>
                                        <option value="<?php echo $row['id']; ?>"><?php if ($_COOKIE['lang'] == "EN")
                                                                                        echo $row['name'];
                                                                                    else
                                                                                        echo $row['name_pt']; ?></option>
                                <?php }
                                } ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $priority_translate; ?><span
                                    class="text-danger-emphasis"> *</span></label>
                            <select name="priority" required>
                                <?php $result_priority = get_priority($conn);
                                while ($row = mysqli_fetch_assoc($result_priority)) {
                                    if ($row['id'] != 33) {
                                        if ($row['sub-category'] == 1) { ?>
                                            </optgroup>
                                            <optgroup label="<?php if ($_COOKIE['lang'] == "EN")
                                                                    echo $row['name'];
                                                                else
                                                                    echo $row['name_pt']; ?>">
                                            <?php } ?>
                                            <option value="<?php echo $row['id']; ?>"><?php if ($_COOKIE['lang'] == "EN")
                                                                                            echo $row['name'];
                                                                                        else
                                                                                            echo $row['name_pt']; ?></option>
                                    <?php }
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="submit"
                                class="button btn btn-sm btn-outline-danger"><?php echo $add_financial_goal; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<script src="../../assets/js/add-financial-goal.js"></script>