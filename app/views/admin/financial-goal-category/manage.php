<?php
session_start();
require "../backend/querys.php";

if (!isset($_COOKIE['user']))
    header("location: /CashManager/public/sign-up");

if (is_admin($conn, $user_id)) {
    $this->layout("master-admin");
    $_SESSION['path'] = $_SERVER['REQUEST_URI'];
    $_SESSION['page'] = "financial goal categories";
    require_once "../backend/language.php";
    require "../backend/translate.php";
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
        <div id="success-notif" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="alert alert-success <?= $lightTest == 1 ? "box-shadow-success-alert" : "" ?> d-flex align-items-center mb-0"
                role="alert">
                <svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:">
                    <use xlink:href="#check-circle-fill" />
                </svg>
                <div><?= $words[$lang]["category_deleted_success"] ?></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="error-notif" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="alert alert-danger <?= $lightTest == 1 ? "box-shadow-danger-alert" : "" ?> d-flex align-items-center mb-0"
                role="alert">
                <svg class="bi flex-shrink-0 me-2" role="img" aria-label="Danger:">
                    <use xlink:href="#check-circle-fill" />
                </svg>
                <div><?= $words[$lang]["category_deleted_error"] ?></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <?php if (isset($_SESSION["alert"])) { ?>
        <div id="success-notif" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="alert alert-success <?= $lightTest == 1 ? "box-shadow-success-alert" : "" ?> d-flex align-items-center mb-0"
                role="alert"><svg class="bi flex-shrink-0 me-2" role="img" aria-label="Success:">
                    <use xlink:href="#check-circle-fill" />
                </svg>
                <div><?= $_SESSION['alert'] ?></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <?php
                unset($_SESSION["alert"]);
            }  ?>
    </div>

    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?php echo $words[$_COOKIE["lang"]]["categorys"]; ?></h1>
    </div>

    <button class="btn btn-outline-primary mb-4" onclick="goToAdd()">
        <?= $words[$_COOKIE["lang"]]["add_category"] ?>
    </button>
    <div class="table-responsive center">
        <table class="table table-sm table-striped align-middle text-center">
            <thead>
                <tr>
                    <th scope="col" class="col-4"><?php echo $name_translate_en; ?></th>
                    <th scope="col" class="col-4"><?php echo $name_translate_pt; ?></th>
                    <th scope="col" class="col-2"><?php echo $edit; ?></th>
                    <th scope="col" class="col-2"><?php echo $delete ?></th>
                </tr>
            </thead>
            <tbody id="table">
                <?php
                    foreach ($categories as $category) { ?>
                <tr id="category-<?= $category['id'] ?>"
                    class="<?php if ($_COOKIE['mode'] == "light") { ?>table-primary <?php } ?>primary">
                    <td><?= $words["EN"][$category["code"]]; ?></td>
                    <td><?= $words["PT"][$category["code"]]; ?></td>
                    <td><button style="background: none; border: none;"
                            onclick="goToEdit(<?php echo $category['id']; ?>)">
                            <svg class="bi">
                                <use xlink:href="#edit" />
                            </svg>
                        </button>
                    </td>
                    <td class="center"><button data-bs-toggle="modal" data-bs-target="#delete-modal"
                            style="background: none; border: none;"
                            onclick="updateModal(<?php echo $category['id']; ?>)">
                            <svg class="bi">
                                <use xlink:href="#delete" />
                            </svg>
                        </button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="delete-modal" tabindex="-1" aria-labelledby="delete-modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="delete-modalLabel"><?= $delete; ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <h5 class="mb-1"><?= $modal_acc; ?>?</h5>
                    <h6 class="mb-4"><?= $modal_acc2; ?></h4>
                        <button type="button" id="btn-delete" data-bs-dismiss="modal"
                            class="btn btn-success btn-lg me-2 liveToastBtn"><?= $confirm; ?></button>
                        <button type="button" class="btn btn-danger btn-lg "
                            data-bs-dismiss="modal"><?= $decline; ?></button>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="/CashManager/public/assets/admin/js/financial-goal-cats.js"></script>
<?php } ?>