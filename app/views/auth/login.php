<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="website icon" type="png" href="/CashManager/public/assets/logos/logo.png">
    <title>Cash Manager | Login</title>
    <link rel="stylesheet" href="/CashManager/public/assets/css/loader.css">
    <link rel="stylesheet" href="/CashManager/public/assets/css/login.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
</head>

<body>
    <div class="loader-div" id="loader-div">
        <div class="loader">
        </div>
    </div>

    <nav id="deact">
        <img src="assets/logos/logo-transparent.png" alt="logo" id="logo">
        <div class="inside2">
            <ul id="list2">
                <li id="language-selected"><?= $_COOKIE['lang'] == 'PT' ? "PORTUGUÊS" : "ENGLISH"; ?></li>
                <ul id="select-language" style="display: none;">
                    <li id="en" onclick="language('EN')">ENGLISH</li>
                    <li id="pt" onclick="language('PT')">PORTUGUÊS</li>
                </ul>
            </ul>
        </div>
    </nav>

    <!------------------------------------------ main part -------------------------------------->
    <div class="section-box">
        <section id="videoSide" class="hide0">
            <div class="left-bar spcl"></div>

            <div class="imageDiv vid">
                <div class="loginBox">

                    <div class="LOG">
                        <h2 id="loginText"><?= $login; ?></h2>
                        <form method="POST" class="form">
                            <input id="email" type="email" name="email" placeholder="E-MAIL" required>
                            <input id="password" type="password" name="password"
                                placeholder="<?= $password_translate; ?>" required>
                            <div id="livesearch"><?php if (isset($_GET['e'])) {
                                                        echo "<p id='red-notif'>E-MAIL OR PASSWORD INCORRECT.</p>";
                                                    } ?></div>
                            <a href="forgotPassword.php" style="color: black;">
                                <p id="forgot"><?= $have_forgot_pass; ?></p>
                            </a>
                            <input id="loginbtn" type="submit" value="<?= $login; ?>">
                        </form>
                    </div>

                    <div class="REGISTER">
                        <h2 id="register-text"><?= $register; ?></h2>
                        <input id="createbtn" type="submit" value="<?= $create_account_log; ?>">
                    </div>
                </div>
            </div>

            <div class="right-bar">
            </div>
        </section>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="/CashManager/public/assets/js/loader.js"></script>
    <script src="/CashManager/public/assets/js/login.js"></script>
    <script src="/CashManager/public/assets/js/all.js"></script>
</body>

</html>