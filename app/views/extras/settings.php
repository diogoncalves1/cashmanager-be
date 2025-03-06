<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "settings";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
require_once "../backend/querys.php";
$coin = $_COOKIE['coin_id'];
require_once "../backend/language.php";
?>
<main class="mt-5 col-md-9 ms-sm-auto col-lg-10 px-sm-2">
    <div class="container mt-2">
        <div class="row gutters">
            <div class="col-12">
                <div class="card-body">
                    <form method="POST">
                        <div class="row gutters">

                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <h6 class="mb-3 text-primary"><?= $personal_details; ?></h6>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label for="fullName"><?= $name; ?></label>
                                    <input type="text" class="form-control" id="fullName" name="name"
                                        placeholder="<?= $name; ?>..." value="<?= $user_name; ?>">
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label for="phone"><?= $password_translate_set; ?></label>
                                    <input type="password" class="form-control" name="password" id="password"
                                        placeholder="<?= $password_translate_set ?>...">
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label for="eMail">E-Mail</label>
                                    <input type="email" class="form-control" name="email" id="eMail"
                                        value="<?= $user_email; ?>" placeholder="E-Mail...">
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label for="website"><?= $verify; ?>
                                        <?= $password_translate_set; ?></label>
                                    <input type="password" class="form-control" id="password-verify"
                                        placeholder="<?= $password_translate_set; ?>...">
                                </div>
                            </div>


                            <div class="col-12">
                                <div class="form-group flex-column d-flex">
                                    <label for="website"><?= $coin_translate; ?></label>
                                    <select name="coin" class="form-select">
                                        <?php $result = get_coin($conn);
                                        while ($row = $result->fetch_assoc()) { ?>
                                        <option value="<?= $row['id']; ?>" <?php if ($coin == $row['id'])
                                                                                    echo "selected"; ?>>
                                            <?= $_COOKIE['lang'] == "EN" ? $row['name'] : $row['name_pt']; ?>
                                            (<?= $row['code']; ?>, <?= $row['symbol']; ?>)
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="form-check form-switch">
                                    <label class="form-check-label" for="flexSwitchCheckChecked"><?= $lights; ?></label>
                                    <input class="form-check-input" type="checkbox" role="switch" name="light"
                                        id="lights" onchange="lightTest(this.checked)" <?php if ($lightTest == 1) {
                                            echo "checked";
                                        } ?>>

                                </div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="form-check form-switch">
                                    <label class="form-check-label"
                                        for="flexSwitchCheckChecked"><?= $notifications; ?></label>
                                    <input class="form-check-input" type="checkbox" role="switch" name="notifications"
                                        id="notifications" <?php if ($notificationsTest == 1) {
                                            echo "checked";
                                        } ?>>

                                </div>
                            </div>
                        </div>
                        <div class="row gutters mt-3">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="text-right">
                                    <button type="submit" id="btn" name="submit" class="btn btn-success">
                                        <?= $update_translate; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php if (isset($_SESSION['update']) && $_SESSION['update']) { ?>
                    <div class="row gutters mt-3">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                            <div class="alert alert-success" role="alert">
                                <?= $update_check; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                        unset($_SESSION['update']);
                    } ?>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="/CashManager/public/assets/js/settings.js"></script>