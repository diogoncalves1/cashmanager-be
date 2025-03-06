<?php
$this->layout("master");
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "add limits";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");

require_once "../backend/querys.php";
require_once "../backend/language.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $add_limit; ?></h1>
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
            <div class="col-12 text-center">
                <form class="form-card" method="POST">
                    <div class="row justify-content-between text-left">
                        <div class="row" id="input-container">
                            <div class="form-group col-sm-6 flex-column d-flex"> <label
                                    class="form-control-label px-3  "> Max <span class="text-danger-emphasis">
                                        *</span></label>
                                <input type="number" class="input" min="0.01" step="0.01" id="input-value"
                                    name="value[]" placeholder="Max: 0.00<?= $coin; ?>" required>
                            </div>
                            <div class="form-group col-sm-6 flex-column d-flex"> <label
                                    class="form-control-label px-3  "> <?= $period ?> <span
                                        class="text-danger-emphasis"> *</span></label>
                                <select name="period[]" class="select-type" onchange="catTest()">
                                    <?php foreach ($timePeriods as $timePeriod) { ?>
                                    <option value="<?= $timePeriod['id'] - 1; ?>">
                                        <?php if ($_COOKIE['lang'] == "EN")
                                                echo $timePeriod['name'];
                                            else
                                                echo $timePeriod['name_pt']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group col-12 flex-column d-flex">
                                <label class="form-control-label px-3"><?php echo $category; ?>
                                    <span class="text-danger-emphasis"> *</span>
                                </label>
                                <select class="select" name="category[]" onchange="catTest()">
                                    <?php $result_cats = get_all_category($conn);
                                    while ($row = mysqli_fetch_assoc($result_cats)) {

                                        if ($row['id'] != 33) {
                                            if ($row['sub-category'] == 1) { ?>
                                    </optgroup>
                                    <optgroup label="<?php if ($_COOKIE['lang'] == "EN")
                                                                        echo $row['name'];
                                                                    else
                                                                        echo $row['name_pt']; ?>">
                                        <?php }
                                            if (!get_limit($conn, 0, $row['id'], 0, 0)) { ?>
                                        <option value="<?php echo $row['id']; ?>">
                                            <?php if ($_COOKIE['lang'] == "EN")
                                                            echo $row['name'];
                                                        else
                                                            echo $row['name_pt']; ?></option>
                                        <?php }
                                        }
                                    } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-start">
                        <div class="form-group col-sm-6">
                            <button type="button" id="btn-add"
                                class="button btn btn-sm btn-outline-primary"><?php echo $add_line; ?></button>
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="submit"
                                class="button btn btn-sm btn-outline-danger"
                                id="submit-btn"><?php echo $add_limit; ?></button>
                        </div>
                    </div>
                    <div class="row" id="alert">

                    </div>
                </form>
            </div>
        </div>
    </div>

</main>

<div class="d-none">
    <select id="select-daily">
        <?php $result_cats = get_all_category($conn);
        while ($row = mysqli_fetch_assoc($result_cats)) {

            if ($row['id'] != 33) {
                if ($row['sub-category'] == 1) { ?>
        </optgroup>
        <optgroup label="<?php if ($_COOKIE['lang'] == "EN")
                                            echo $row['name'];
                                        else
                                            echo $row['name_pt']; ?>">
            <?php }
                if (!get_limit($conn, 0, $row['id'], 0, 0)) { ?>
            <option value="<?php echo $row['id']; ?>"><?php if ($_COOKIE['lang'] == "EN")
                                                                        echo $row['name'];
                                                                    else
                                                                        echo $row['name_pt']; ?></option>
            <?php }
            }
        } ?>
    </select>
    <select id="select-weekly">
        <?php $result_cats = get_all_category($conn);
        while ($row = mysqli_fetch_assoc($result_cats)) {

            if ($row['id'] != 33) {
                if ($row['sub-category'] == 1) { ?>
        </optgroup>
        <optgroup label="<?php if ($_COOKIE['lang'] == "EN")
                                            echo $row['name'];
                                        else
                                            echo $row['name_pt']; ?>">
            <?php }
                if (!get_limit($conn, 0, $row['id'], 0, 1)) { ?>
            <option value="<?php echo $row['id']; ?>"><?php if ($_COOKIE['lang'] == "EN")
                                                                        echo $row['name'];
                                                                    else
                                                                        echo $row['name_pt']; ?></option>
            <?php }
            }
        } ?>
    </select>
    <select id="select-monthly">
        <?php $result_cats = get_all_category($conn);
        while ($row = mysqli_fetch_assoc($result_cats)) {

            if ($row['id'] != 33) {
                if ($row['sub-category'] == 1) { ?>
        </optgroup>
        <optgroup label="<?php if ($_COOKIE['lang'] == "EN")
                                            echo $row['name'];
                                        else
                                            echo $row['name_pt']; ?>">
            <?php }
                if (!get_limit($conn, 0, $row['id'], 0, 2)) { ?>
            <option value="<?php echo $row['id']; ?>"><?php if ($_COOKIE['lang'] == "EN")
                                                                        echo $row['name'];
                                                                    else
                                                                        echo $row['name_pt']; ?></option>
            <?php }
            }
        } ?>
    </select>
    <select id="select-annual">
        <?php $result_cats = get_all_category($conn);
        while ($row = mysqli_fetch_assoc($result_cats)) {

            if ($row['id'] != 33) {
                if ($row['sub-category'] == 1) { ?>
        </optgroup>
        <optgroup label="<?php if ($_COOKIE['lang'] == "EN")
                                            echo $row['name'];
                                        else
                                            echo $row['name_pt']; ?>">
            <?php }
                if (!get_limit($conn, 0, $row['id'], 0, 3)) { ?>
            <option value="<?php echo $row['id']; ?>"><?php if ($_COOKIE['lang'] == "EN")
                                                                        echo $row['name'];
                                                                    else
                                                                        echo $row['name_pt']; ?></option>
            <?php }
            }
        } ?>
    </select>
</div>

<script>
const INPUT_CONTAINER = document.getElementById("input-container");
const BTN = document.getElementById("btn-add");
const SUBMIT_BTN = document.getElementById("submit-btn");
const ALERT = document.getElementById("alert");
const SELECT_DAILY = document.getElementById("select-daily");
const SELECT_WEEKLY = document.getElementById("select-weekly");
const SELECT_MONTHLY = document.getElementById("select-monthly");
const SELECT_ANNUAL = document.getElementById("select-annual");

function addLine() {
    var text =
        '<div class="form-group col-sm-6 flex-column d-flex"> <label class="form-control-label px-3  "> Max <span class="text-danger-emphasis"> *</span></label><input type="number" class="input" min="0.01" step="0.01" id="input-value" name="value[]" placeholder="Max: 0.00' +
        '<?= $coin ?>' +
        '" required></div><div class="form-group col-sm-6 flex-column d-flex" > <label class="form-control-label px-3  "> <?= $period ?> <span class="text-danger-emphasis"> *</span></label><select name="period[]" class="select-type" onchange="catTest()">';
    <?php foreach ($timePeriods as $timePeriod) { ?>
    text += '<option value="<?= $timePeriod['id'] - 1; ?>">';
    <?php if ($_COOKIE['lang'] == "EN") { ?>
    text += '<?= $timePeriod['name']; ?>';
    <?php } else { ?>
    text += '<?= $timePeriod['name_pt']; ?>';
    <?php } ?>
    text += '</option >';
    <?php } ?>
    text +=
        '</select></div > <div class="form-group col-12 flex-column d-flex" ><label class="form-control-label px-3"><?= $category; ?><span class="text-danger-emphasis"> *</span ></label ><select class="select" name="category[]" onchange="catTest()">';
    <?php $result_cats = get_all_category($conn);
        while ($row = mysqli_fetch_assoc($result_cats)) {
            if ($row['id'] != 33) {
                if ($row['sub-category'] == 1) { ?>
    text += '</optgroup> <optgroup label="<?php if ($_COOKIE["lang"] == "EN") {
                                                                echo $row["name"];
                                                            } else {
                                                                echo $row["name_pt"];
                                                            } ?>">';
    <?php }
                if (!get_limit($conn, 0, $row["id"], 0, 0)) { ?>
    text += '<option value="<?php echo $row["id"]; ?>" > <?php if ($_COOKIE["lang"] == "EN")
                                                                                echo $row["name"];
                                                                            else
                                                                                echo $row["name_pt"]; ?></option >';
    <?php }
            }
        } ?>
    text += '</select ></div > ';
    INPUT_CONTAINER.innerHTML += text;
    catTest();
}

BTN.addEventListener("click", addLine);

function catTest() {
    let categorys = document.getElementsByClassName("select");
    let type = document.getElementsByClassName("select-type");
    var flag = 0;
    for (i = 0; i < categorys.length; i++) {
        var type_value = type[i].value;
        switch (type_value) {
            case "0": {
                categorys[i].innerHTML = SELECT_DAILY.innerHTML;
                break;
            }
            case "1": {
                categorys[i].innerHTML = SELECT_WEEKLY.innerHTML;
                break;
            }
            case "2": {
                categorys[i].innerHTML = SELECT_MONTHLY.innerHTML;
                break;
            }
            case "3": {
                categorys[i].innerHTML = SELECT_ANNUAL.innerHTML;
                break;
            }
        }

        for (j = i + 1; j < categorys.length; j++) {
            if (j <= categorys.length) {
                if (categorys[i].value == categorys[j].value) {
                    if (type[i].value == type[j].value) {
                        SUBMIT_BTN.setAttribute("disabled", "");
                        ALERT.innerHTML = '<div class="alert alert-warning" ><?= $alert_limits ?></div>';
                        flag = 1;
                    }
                }
            }
        }
    }
    if (flag == 0) {
        SUBMIT_BTN.removeAttribute("disabled");
        ALERT.innerHTML = "";
    }
}
</script>