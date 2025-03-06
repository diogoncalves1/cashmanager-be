<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "";
if (isset($_GET['id']))
    $expense_id = $_GET['id'];
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
                <button type="button" id="expense" class="btn btn-sm btn-outline-danger">Add
                    Expense</button>
                <button type="button" id="revenue" class="btn btn-sm btn-outline-success">Add
                    Revenue</button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <form class="form-card" method="POST">
                    <?php $result_edit = get_scheduled_expenses($conn, $user_id, $expense_id);
                    $result_cats = get_all_category($conn);
                    if (isset($result_edit)) {
                        while ($row = mysqli_fetch_assoc($result_edit)) { ?>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?= $value; ?><span class="text-danger-emphasis">
                                    *</span></label> <input type="number" class="input" step="0.01" id="fname"
                                name="value" placeholder="0.00<?= $coin; ?>" value="<?php echo $row['value']; ?>"
                                required> </div>
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?= $scheduled_date; ?><span
                                    class="text-danger-emphasis"> *</span></label> <input min="<?php echo $curDate; ?>"
                                type="date" id="lname" class="input" name="date" value="<?php echo $row['date']; ?>"
                                required>
                        </div>
                    </div>

                    <div class="row justify-content-between text-left mb-3">
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?= $row['type'] == 0 ? $to : $from; ?><span>
                                    (<?= $optional; ?>)</span></label> <input type="text" class="input" step="0.01"
                                id="fname" name="to" placeholder="<?= $row['type'] == 0 ? $to : $from; ?>..."
                                value="<?php echo $row['to_p']; ?>"> </div>

                        <?php if ($row['type'] == 0) { ?>
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?= $category; ?><span class="text-danger-emphasis">
                                    *</span></label>
                            <select name="category">
                                <?php while ($rowW = mysqli_fetch_assoc($result_cats)) {
                                                if ($rowW['sub-category'] == 1) { ?>
                                </optgroup>
                                <optgroup label="<?php if ($_COOKIE['lang'] == "EN")
                                                                            echo $rowW['name'];
                                                                        else
                                                                            echo $rowW['name_pt']; ?>">
                                    <?php } ?>
                                    <option value="<?php echo $rowW['id']; ?>"
                                        <?php if ($rowW['id'] == $row['cat_id'])
                                                                                                    echo "selected"; ?>>
                                        <?php if ($_COOKIE['lang'] == "EN")
                                                            echo $rowW['name'];
                                                        else
                                                            echo $rowW['name_pt'] ?>
                                    </option>

                                    <?php } ?>
                            </select>
                        </div>
                        <?php } ?>
                    </div>


                    <div class="row justify-content-between text-left">
                        <div class="form-group col-12 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $description; ?><span>
                                    (<?= $optional; ?>)</span></label> <input type="text" id="lname" class="input pb-5"
                                name="description" value="<?php echo $row['description']; ?>"
                                placeholder="<?php echo $description; ?>...">
                        </div>


                    </div>

                    <div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="submit"
                                class="button btn btn-sm btn-outline-<?php if ($row['type'] == 0)
                                                                                    echo "danger";
                                                                                else
                                                                                    echo "success"; ?>"><?php echo $edit; ?>
                                <?php echo $transaction_translate; ?></button> </div>
                        <?php }
                    } else
                        echo "ERROR"; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>