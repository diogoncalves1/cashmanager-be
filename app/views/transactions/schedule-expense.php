<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "schedule expense";
if (!isset($_COOKIE['user'])) {
    header("location: /CashManager/public/sign-up");
}
require_once "../backend/querys.php";
require_once "../backend/language.php";
require "../backend/translate.php";
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $words["schedule_expense"]; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense; ?></button>
                <button type="button" id="revenue" class="btn btn-sm btn-outline-success">
                    <?Php echo $add_revenue; ?>
                </button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <form class="form-card" action="" method="POST">
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3  "><?php echo $value; ?> <span
                                    class="text-danger-emphasis">
                                    *</span></label> <input type="number" class="input" step="0.01" id="input-value"
                                name="value" placeholder="0.00<?= $coin ?>" required> </div>
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $scheduled_date; ?><span
                                    class="text-danger-emphasis"> *</span></label> <input type="date"
                                min="<?= $curDate; ?>" id="lname" class="input" name="date" required> </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-12 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $category; ?><span
                                    class="text-danger-emphasis"> *</span></label>
                            <select name="category" required>
                                <?php $result_cats = get_all_category($conn);
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
                                                                                            echo $row['name_pt']; ?>
                                            </option>
                                    <?php }
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $account; ?><span
                                    class="text-danger-emphasis"> *</span></label>
                            <select onchange="changeMax(this.value)" name="account" required>
                                <?php foreach ($accountsUser as $account) {
                                    if (hasPermission($conn, $account["role_id"], "add_transactions")) { ?>
                                        <option role="<?= $account['cash']; ?>" id="<?= $account['id']; ?>"
                                            value="<?= $account['id']; ?>"><?= $account['name']; ?></option>
                                <?php }
                                } ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-6 flex-column d-flex pb-3"> <label
                                class="form-control-label px-3"><?php echo $to; ?>:<span>
                                    (<?php echo $optional; ?>)</span></label> <input type="text" class="input"
                                id="fname" name="to" placeholder="<?Php echo $to; ?>...">
                        </div>
                    </div>

                    <div class="row justify-content-between text-left">
                        <div class="form-group col flex-column d-flex pb-3"> <label
                                class="form-control-label px-3"><?php echo $description; ?><span>
                                    (<?php echo $optional; ?>)</span></label> <input type="text" class="input pb-5"
                                id="fname" name="description" placeholder="<?Php echo $description; ?>..."> </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="submit"
                                class="button btn btn-sm btn-outline-danger"><?php echo $schedule_expense; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>