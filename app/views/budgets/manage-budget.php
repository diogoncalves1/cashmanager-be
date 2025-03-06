<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "manage limits";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
require_once "../backend/querys.php";
require_once "../backend/language.php";
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-sm-2">
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="alert alert-success <?= $lightTest == 1 ? "box-shadow-success-alert" : "" ?> d-flex align-items-center mb-0" role="alert"><svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:">
                    <use xlink:href="#check-circle-fill" />
                </svg>
                <div><?= $account_deleted ?></div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="liveToastWarning" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="alert alert-danger <?= $lightTest == 1 ? "box-shadow-danger" : "" ?> d-flex align-items-center mb-0" role="alert"><svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:">
                    <use xlink:href="#exclamation-triangle-fill" />
                </svg>
                <div><?= $only_owner_can_delete; ?></div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <?php if (isset($_SESSION["alert"])) { ?>
            <div id="checkToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="alert alert-success <?= $lightTest == 1 ? "box-shadow-success-alert" : "" ?> d-flex align-items-center mb-0" role="alert"><svg class="bi flex-shrink-0 me-2" role="img" aria-label="Success:">
                        <use xlink:href="#check-circle-fill" />
                    </svg>
                    <div><?= $_SESSION['alert'] ?></div><button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php
            unset($_SESSION["alert"]);
        }  ?>
    </div>
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $limits; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?php echo $add_revenue ?></button>
            </div>
        </div>
    </div>
    <h3 class="text-center"><?= $daily_budgets ?></h3>
    <div id="daily-budget">
        <?php $result_limits = get_limit($conn, 0, 0, $user_id, 0);
        if (isset($result_limits)) { ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered text-center align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="col-3"><?php echo $category; ?></th>
                            <th scope="col" class="col-3"><?= $max_translate; ?></th>
                            <th scope="col" class="col-2"><?php echo $expense_translate; ?></th>
                            <th scope="col" class="col-1"><?php echo $status_translate; ?></th>
                            <th scope="col" class="col-1"><?php echo $edit ?></th>
                            <th scope="col" class="col-1"><?php echo $delete ?></th>
                        </tr>
                    </thead>
                    <tbody id="table-daily">
                        <?php

                        while ($row = mysqli_fetch_assoc($result_limits)) { ?>
                            <tr class="<?php if ($_COOKIE['mode'] == "light") { ?>table-primary<?php } ?> primary">
                                <td class="td-daily"><?= get_category_name($conn, $row['cat_id']) ?></td>
                                <td class="td-daily"><?php echo $row['max']; ?><?= $coin ?></td>
                                <td class="td-daily"><?php echo $row['current']; ?><?= $coin ?></td>
                                <td class="td-daily">
                                    <?php if ($row['max'] - $row['max'] * 0.2 > $row['current']) { ?>
                                        <svg class="bi" style="color:rgb(33, 141, 1);">
                                            <use xlink:href="#emoji-smile" />
                                        </svg>
                                    <?php } elseif ($row['max'] <= $row['current']) { ?>
                                        <svg class="bi" style="color:rgb(179, 26, 26);">
                                            <use xlink:href="#emoji-frown" />
                                        </svg>
                                    <?php } else { ?>
                                        <svg class="bi" style="color:rgb(224, 221, 0);">
                                            <use xlink:href="#emoji-neutral" />
                                        </svg>
                                    <?php } ?>
                                </td>
                                <td class="td-daily">
                                    <button style="background: none; border: none;"
                                        onclick="goToEdit(<?php echo $row['id']; ?>)"><svg class="bi">
                                            <use xlink:href="#edit" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="td-daily">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#delete-modal"
                                        style="background: none; border: none;"
                                        onclick="modal(<?php echo $row['id']; ?>, <?= $row['type']; ?>)"><svg
                                            class="bi">
                                            <use xlink:href="#delete" />
                                        </svg></button>
                                </td>
                            </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>

            </div>
        <?php } else { ?>
            <p class="text-center"><?= $no . " " . $daily_budgets ?></p>

        <?php } ?>
    </div>
    <h3 class="text-center"><?= $weekly_budgets ?></h3>
    <div id="weekly-budget">
        <?php $result_limits = get_limit($conn, 0, 0, $user_id, 1);
        if (isset($result_limits)) { ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered text-center align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="col-3"><?php echo $category; ?></th>
                            <th scope="col" class="col-3"><?= $max_translate; ?></th>
                            <th scope="col" class="col-2"><?php echo $expense_translate; ?></th>
                            <th scope="col" class="col-1"><?php echo $status_translate; ?></th>
                            <th scope="col" class="col-1"><?php echo $edit ?></th>
                            <th scope="col" class="col-1"><?php echo $delete ?></th>
                        </tr>
                    </thead>
                    <tbody id="table-weekly">
                        <?php

                        while ($row = mysqli_fetch_assoc($result_limits)) { ?>
                            <tr class="<?php if ($_COOKIE['mode'] == "light") { ?>table-primary<?php } ?> primary">
                                <td class="td-weekly"><?= get_category_name($conn, $row['cat_id']) ?></td>
                                <td class="td-weekly"><?php echo $row['max']; ?><?= $coin ?></td>
                                <td class="td-weekly"><?php echo $row['current']; ?><?= $coin ?></td>
                                <td class="td-weekly">
                                    <?php if ($row['max'] - $row['max'] * 0.2 > $row['current']) { ?>
                                        <svg class="bi" style="color:rgb(33, 141, 1);">
                                            <use xlink:href="#emoji-smile" />
                                        </svg>
                                    <?php } elseif ($row['max'] <= $row['current']) { ?>
                                        <svg class="bi" style="color:rgb(179, 26, 26);">
                                            <use xlink:href="#emoji-frown" />
                                        </svg>
                                    <?php } else { ?>
                                        <svg class="bi" style="color:rgb(224, 221, 0);">
                                            <use xlink:href="#emoji-neutral" />
                                        </svg>
                                    <?php } ?>
                                </td>
                                <td class="td-weekly">
                                    <button style="background: none; border: none;"
                                        onclick="goToEdit(<?php echo $row['id']; ?>)"><svg class="bi">
                                            <use xlink:href="#edit" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="td-weekly">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#delete-modal"
                                        style="background: none; border: none;"
                                        onclick="modal(<?php echo $row['id']; ?>, <?= $row['type']; ?>)"><svg
                                            class="bi">
                                            <use xlink:href="#delete" />
                                        </svg></button>
                                </td>
                            </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>

            </div>
        <?php } else { ?>
            <p class="text-center"><?= $no . " " . $weekly_budgets ?></p>

        <?php } ?>
    </div>
    <h3 class="text-center"><?= $monthly_budgets ?></h3>
    <div id="monthly-budget">
        <?php $result_limits = get_limit($conn, 0, 0, $user_id, 2);
        if (isset($result_limits)) { ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered text-center align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="col-3"><?php echo $category; ?></th>
                            <th scope="col" class="col-3"><?= $max_translate; ?></th>
                            <th scope="col" class="col-2"><?php echo $expense_translate; ?></th>
                            <th scope="col" class="col-1"><?php echo $status_translate; ?></th>
                            <th scope="col" class="col-1"><?php echo $edit ?></th>
                            <th scope="col" class="col-1"><?php echo $delete ?></th>
                        </tr>
                    </thead>
                    <tbody id="table-monthly">
                        <?php

                        while ($row = mysqli_fetch_assoc($result_limits)) { ?>
                            <tr class="<?php if ($_COOKIE['mode'] == "light") { ?>table-primary<?php } ?> primary">
                                <td class="td-monthly"><?= get_category_name($conn, $row['cat_id']) ?></td>
                                <td class="td-monthly"><?php echo $row['max']; ?><?= $coin ?></td>
                                <td class="td-monthly"><?php echo $row['current']; ?><?= $coin ?></td>
                                <td class="td-monthly">
                                    <?php if ($row['max'] - $row['max'] * 0.2 > $row['current']) { ?>
                                        <svg class="bi" style="color:rgb(33, 141, 1);">
                                            <use xlink:href="#emoji-smile" />
                                        </svg>
                                    <?php } elseif ($row['max'] <= $row['current']) { ?>
                                        <svg class="bi" style="color:rgb(179, 26, 26);">
                                            <use xlink:href="#emoji-frown" />
                                        </svg>
                                    <?php } else { ?>
                                        <svg class="bi" style="color:rgb(224, 221, 0);">
                                            <use xlink:href="#emoji-neutral" />
                                        </svg>
                                    <?php } ?>
                                </td>
                                <td class="td-monthly">
                                    <button style="background: none; border: none;"
                                        onclick="goToEdit(<?php echo $row['id']; ?>)"><svg class="bi">
                                            <use xlink:href="#edit" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="td-monthly">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#delete-modal"
                                        style="background: none; border: none;"
                                        onclick="modal(<?php echo $row['id']; ?>, <?= $row['type']; ?>)"><svg class="bi">
                                            <use xlink:href="#delete" />
                                        </svg></button>
                                </td>
                            </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>

            </div>
        <?php } else { ?>
            <p class="text-center"><?= $no . " " . $monthly_budgets ?></p>
        <?php } ?>
    </div>
    <h3 class="text-center"><?= $annual_budgets ?></h3>
    <div id="annual-budget">
        <?php $result_limits = get_limit($conn, 0, 0, $user_id, 3);
        if (isset($result_limits)) { ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered text-center align-middle">
                    <thead>
                        <tr>
                            <th scope="col" class="col-3"><?php echo $category; ?></th>
                            <th scope="col" class="col-3"><?= $max_translate; ?></th>
                            <th scope="col" class="col-2"><?php echo $expense_translate; ?></th>
                            <th scope="col" class="col-1"><?php echo $status_translate; ?></th>
                            <th scope="col" class="col-1"><?php echo $edit ?></th>
                            <th scope="col" class="col-1"><?php echo $delete ?></th>
                        </tr>
                    </thead>
                    <tbody id="table-annual">
                        <?php

                        while ($row = mysqli_fetch_assoc($result_limits)) { ?>
                            <tr class="<?php if ($_COOKIE['mode'] == "light") { ?>table-primary<?php } ?> primary">
                                <td class="td-annual"><?= get_category_name($conn, $row['cat_id']) ?></td>
                                <td class="td-annual"><?php echo $row['max']; ?><?= $coin ?></td>
                                <td class="td-annual"><?php echo $row['current']; ?><?= $coin ?></td>
                                <td class="td-annual"><?php if ($row['max'] - $row['max'] * 0.2 > $row['current']) { ?>
                                        <svg class="bi" style="color:rgb(33, 141, 1);">
                                            <use xlink:href="#emoji-smile" />
                                        </svg>
                                    <?php } elseif ($row['max'] <= $row['current']) { ?>
                                        <svg class="bi" style="color:rgb(179, 26, 26);">
                                            <use xlink:href="#emoji-frown" />
                                        </svg>
                                    <?php } else { ?>
                                        <svg class="bi" style="color:rgb(224, 221, 0);">
                                            <use xlink:href="#emoji-neutral" />
                                        </svg>
                                    <?php } ?>
                                </td>
                                <td class="td-annual">
                                    <button style="background: none; border: none;"
                                        onclick="goToEdit(<?php echo $row['id']; ?>)"><svg class="bi">
                                            <use xlink:href="#edit" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="td-annual">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#delete-modal"
                                        style="background: none; border: none;"
                                        onclick="modal(<?php echo $row['id']; ?>, <?= $row['type']; ?>)"><svg class="bi">
                                            <use xlink:href="#delete" />
                                        </svg></button>
                                </td>
                            </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>

            </div>
        <?php } else { ?>
            <p class="text-center"><?= $no . " " . $annual_budgets ?></p>
        <?php } ?>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="delete-modal" tabindex="-1" aria-labelledby="delete-modalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="delete-modalLabel"><?= $delete; ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h5 class="mb-4"><?= $modal_limit; ?>?</h5>
                    <button type="button" id="modal-btn" data-bs-dismiss="modal"
                        class="btn btn-success btn-lg me-2"><?= $confirm; ?></button>
                    <button type="button" class="btn btn-danger btn-lg "
                        data-bs-dismiss="modal"><?= $decline; ?></button>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../assets/js/manage-limits.js"></script>