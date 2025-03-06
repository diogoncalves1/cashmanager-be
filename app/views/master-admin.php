<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Manager</title>
    <link rel="icon" href="/CashManager/public/assets/logos/logo.png">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/dashboard/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link rel="stylesheet" href="/CashManager/public/assets/css/loader.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.2/dist/chart.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="/CashManager/public/assets/css/dashboard.css">
    <link rel="stylesheet" href="/CashManager/public/assets/css/slide-bar.css">
</head>

<body <?php if (isset($_COOKIE['mode'])) { ?> data-bs-theme="<?= $_COOKIE['mode'] == "dark" ? "dark" : ""; ?>"
    <?php } ?>>
    <?php
    include("../backend/config.php");
    include("../assets/icons/icons.html");
    require_once "../backend/querys.php";
    require "../backend/language.php";
    require "../backend/translate.php";
    include("admin/layouts/header.php");
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php include("admin/layouts/sidebar.php"); ?>
            <?= $this->section("content"); ?>

            <script src="/CashManager/public/assets/js/all.js"></script>
            <script src="/CashManager/public/assets/admin/js/home.js"></script>
            <script src="/CashManager/public/assets/js/loader.js"></script>
        </div>
    </div>
</body>

</html>