<?php
require "../backend/querys.php";

if (!isset($_COOKIE['user']))
    header("location: /CashManager/sign-up");

if (is_admin($conn, $user_id)) {
    $this->layout("master-admin");
    $_SESSION['path'] = $_SERVER['REQUEST_URI'];
    $_SESSION['page'] = "users";
    require_once "../backend/language.php";
    require "../backend/translate.php";
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h1 class="h2 mt-3"><?= $words[$lang]["users"]; ?></h1>
    <div class="table-responsive center">
        <table class="table table-sm table-striped align-middle text-center">
            <thead>
                <tr>
                    <th scope="col" class="col-1">ID</th>
                    <th scope="col" class="col-9"><?= $name; ?></th>
                    <th scope="col" class="col-1"><?= $edit; ?></th>
                    <th scope="col" class="col-1"><?= $delete ?></th>
                </tr>
            </thead>
            <tbody id="table">
                <?php
                    foreach ($allUsers as $user) { ?>
                <tr id="tr-<?= $user['id'] ?>"
                    class="<?php if ($_COOKIE['mode'] == "light") { ?>table-primary <?php } ?>primary">
                    <td><?= $user['id']; ?></td>
                    <td><?= $user['name']; ?></td>
                    <td>
                        <button style="background: none; border: none;" onclick="goToEdit(<?= $user['id']; ?>)">
                            <svg class="bi">
                                <use xlink:href="#edit" />
                            </svg>
                        </button>
                    </td>
                    <td class="center">
                        <button data-bs-toggle="modal" data-bs-target="#delete-modal"
                            style="background: none; border: none;" onclick="modal(<?= $user['id']; ?>)">
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
</main>
<script src="/CashManager/public/admin/js/users.js"></script>
<?php } ?>