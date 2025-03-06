<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "share";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");

require_once "../backend/querys.php";
require_once "../backend/language.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $edit_share; ?></h1>
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
                        <div class="form-group col-sm-12 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $share_question; ?><span
                                    class="text-danger-emphasis"> *</span></label>
                            <select name="type" id="type" onchange="test()" required>
                                <?php $result_cats = get_share_type($conn);
                                while ($row = mysqli_fetch_assoc($result_cats)) { ?>
                                    <option value="<?php echo $row['id']; ?>"><?= $words[$row["name"]] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row justify-content-between text-left">
                        <div class="form-group col-sm-6 flex-column d-flex"> <label
                                class="form-control-label px-3"><?php echo $friend; ?><span
                                    class="text-danger-emphasis"> *</span></label>
                            <select name="friend" id="friend" onchange="test()" required>
                                <?php $result_status = get_friends($conn, $user_id);
                                while ($row = mysqli_fetch_assoc($result_status)) {
                                    if ($row['user_1'] == $user_id)
                                        $userResult = get_user($conn, $row['user_2']);
                                    else
                                        $userResult = get_user($conn, $row['user_1']);
                                    $userSend = $userResult->fetch_assoc();
                                ?>
                                    <option value="<?php echo $userSend['id']; ?>">
                                        <?= $userSend['name']; ?> ID:<?= $userSend["id"]; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-6 flex-column d-flex div-account"> <label
                                class="form-control-label px-3" id="obj-title"><?php echo $account; ?><span
                                    class="text-danger-emphasis"> *</span></label>
                            <select name="obj" id="obj" onchange="test()" required>

                                <?php if (isset($userSend["id"])) {
                                    test_obj("1", $userSend["id"]);
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="form-group col-sm-6"> <button type="submit"
                                class="button btn btn-sm btn-outline-primary"><?php echo $send_request; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</main>
<script src="../assets/js/share.js"></script>