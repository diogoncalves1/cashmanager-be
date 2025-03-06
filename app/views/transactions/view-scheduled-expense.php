<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "scheduled expenses";
if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");
require_once "../backend/querys.php";
require_once "../backend/language.php";
$expense_id = $_GET['id'];
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-sm-2">
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $transactions; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?php echo $add_expense ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?php echo $add_revenue ?></button>
            </div>
        </div>
    </div>
    <?php
    $result_transaction = get_scheduled_expenses($conn, $user_id, $expense_id);
    $i = $result_transaction->num_rows;
    while ($row = mysqli_fetch_assoc($result_transaction)) {
    ?>
        <div class="table-responsive">
            <table class="table table-bordered <?php if ($row['type'] == 1)
                                                    echo "success";
                                                else
                                                    echo "danger";
                                                if ($_COOKIE['mode'] == "light") { ?>  table-<?php if ($row['type'] == 1)
                                                                                                    echo "success";
                                                                                                else
                                                                                                    echo "danger";
                                                                                            } ?>">
                <thead>
                    <tr>
                        <th scope="col" class="col-3"><?php echo $scheduled_date; ?></th>
                        <th scope="col" class="col-2"><?php echo $type_translate; ?></th>
                        <th scope="col" class="col-2"><?php echo $account; ?></th>
                        <th scope="col" class="col-2"><?= $row['type'] == 0 ? $to : $from; ?></th>
                        <th scope="col" class="col-1"><?php echo $value; ?></th>
                        <?php if ($row['type'] == 0) { ?>
                            <th scope="col" class="col-2"><?php echo $category ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody id="table">
                    <?php


                    if ($row['type'] == 1) {
                        $color = "#195905";
                    } else
                        $color = "#e30022;";

                    ?>
                    <tr>
                        <td style="color: <?php echo $color; ?>"><?php echo $row['date']; ?></td>
                        <td style="color: <?php echo $color; ?>"><?php echo $type[$row['type']]; ?></td>
                        <td style="color: <?php echo $color; ?>">
                            <?php echo get_name_account($conn, $row['account_id']); ?>
                        </td>
                        <td style="color: <?php echo $color; ?>"><?php echo $row['to_p']; ?></td>
                        <td style="color: <?php echo $color; ?>"><?php echo $row['value']; ?><?= $coin ?></td>
                        <?php if ($row['type'] == 0) { ?>
                            <td style="color: <?php echo $color; ?>">
                                <?= get_category_name($conn, $row['cat_id']); ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="7">
                            <table class="table <?php if ($row['type'] == 1)
                                                    echo "success";
                                                else
                                                    echo "danger";
                                                if ($_COOKIE['mode'] == "light") { ?>  table-<?php if ($row['type'] == 1)
                                                                                                    echo "success";
                                                                                                else
                                                                                                    echo "danger";
                                                                                            } ?>">
                                <thead>
                                    <th>Description</th>
                                </thead>
                                <tbody>
                                    <tr">
                                        <td style="color: <?php echo $color; ?>">
                                            <?php echo $row['description']; ?>
                                        </td>
                    </tr>
                </tbody>
            </table>
            </td>
            </tr>
        <?php
        $last_transaction = $row['value'];
        $i--;
    } ?>
        </tbody>
        </table>
        </div>
</main>