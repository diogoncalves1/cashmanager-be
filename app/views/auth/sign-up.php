<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="website icon" type="png" href="/CashManager/public/assets/logos/logo.png">
    <title>Cash Manager | Sign-up</title>
    <link rel="stylesheet" href="/CashManager/public/assets/css/loader.css">
    <link rel="stylesheet" href="/CashManager/public/assets/css/sing-up.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>
    <?php
    if (isset($_GET["e"]))
        $error = $_GET['e'];
    ?>
    <div class="loader-div" id="loader-div">
        <div class="loader"></div>
    </div>

    <nav id="deact">
        <img src="/CashManager/public/assets/logos/logo-transparent.png" alt="logo" id="logo">
        <div class="inside2">
            <ul id="list2">
                <li id="login"><?= $login; ?></li>
                <li id="language-selected"><?= $_COOKIE['lang'] == 'PT' ? "PORTUGUÊS" : "ENGLISH"; ?></li>
                <ul id="select-language" style="display: none;">
                    <li id="en" onclick="language('EN')">ENGLISH</li>
                    <li id="pt" onclick="language('PT')">PORTUGUÊS</li>
                </ul>
            </ul>
        </div>
    </nav>
    </div>
    <!------------------------------------------ main part -------------------------------------->
    <div class="section-box">
        <section id="videoSide" class="hide0">
            <div class="left-bar spcl"></div>

            <div class="imageDiv vid">
                <div class="loginBox">

                    <div class="LOG">
                        <h2 id="register-text"><?= $register; ?></h2>
                        <form action="" method="POST">
                            <input id="name" type="text" placeholder="<?= $name_sing; ?>" name="name"
                                onkeyup="showResult(this.value)" required>
                            <input id="email" type="email" placeholder="E-MAIL" name="email"
                                onkeyup="showResult(this.value)" required>
                            <input id="password" type="password" placeholder="<?= $password_translate; ?>"
                                name="password" required>
                            <div id="livesearch"><?php if (isset($error)) {
                                                        echo "<p id='red-notif'>ERROR</p>";
                                                    }
                                                    ?></div>
                            <input id="loginbtn" type="submit" value="<?= $register; ?>">
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script src="/CashManager/public/assets/js/sing-up.js"></script>
    <script src="/CashManager/public/assets/js/loader.js"></script>
    <script src="/CashManager/public/assets/js/all.js"></script>
</body>

</html>