<link rel="stylesheet" href="../assets/css/form.css">
<?php
$this->layout("master");
session_start();
$_SESSION['path'] = $_SERVER['REQUEST_URI'];
$_SESSION['page'] = "friends";
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
        <h1 class="h2"><?php echo $friends; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" id="expense"
                    class="btn btn-sm btn-outline-danger"><?Php echo $add_expense; ?></button>
                <button type="button" id="revenue"
                    class="btn btn-sm btn-outline-success"><?php echo $add_revenue ?></button>
            </div>

        </div>
    </div>

    <div class="container-fluid px-1 py-5 mx-auto">
        <h5><?= $your ?> ID: <?= $user_id ?></h5>
        <div class="row d-flex justify-content-center">
            <div class="col-xl-8 col-lg-9 col-md-10 col-11 text-center">
                <div class="row " id="cards-container">
                    <?php $result = get_friends($conn, $user_id);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            if ($user_id == $row['user_1'])
                                $userResult = get_user($conn, $row["user_2"]);
                            else
                                $userResult = get_user($conn, $row["user_1"]);
                            while ($userSend = $userResult->fetch_assoc()) {
                    ?>
                                <div class="card <?= $_COOKIE['lights'] == 1 ? "box-shadow" : ""; ?> mt-2 mb-2 user<?= $userSend['id']; ?>">
                                    <div class="row">
                                        <div class="col-sm-2 col-5">
                                            <h6><?= $userSend["name"]; ?></h6>
                                        </div>
                                        <div class="col-sm-8 col-5"></div>
                                        <div class="col-2"> <a href="#" id="icon-a" data-bs-toggle="modal"
                                                data-bs-target="#delete-modal"
                                                onclick="modal('<?= $userSend['id'] ?>')"><svg class="bi bi-2">
                                                    <use id="icon-result" xlink:href="#delete" />
                                                </svg></a></div>
                                    </div>
                                </div>
                        <?php }
                        }
                    } else { ?>
                        <p class="text-center"><?= $no; ?> <?= $friends; ?> <?= $added; ?></p>
                    <?Php } ?>
                </div>
            </div>
        </div>
    </div>

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
                    <h5 class="mb-4"><?= $modal_friend; ?>?</h5>
                    <button type="button" id="modal-btn" data-bs-dismiss="modal"
                        class="btn btn-success btn-lg me-2"><?= $confirm; ?></button>
                    <button type="button" class="btn btn-danger btn-lg "
                        data-bs-dismiss="modal"><?= $decline; ?></button>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../assets/js/friends.js"></script>