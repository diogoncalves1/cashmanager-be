const PASSWORD = document.getElementById("password");
const PASSWORD_VERIFY = document.getElementById("password-verify");
const BTN = document.getElementById("btn");
const BD_THEME = document.getElementById("bd-theme");
const BD_LANG = document.getElementById("bd-lang");

function verifyPassword() {
    if (PASSWORD.value != PASSWORD_VERIFY.value) {
        BTN.classList.add("disabled");
    }
    else
        BTN.classList.remove("disabled");
}
PASSWORD.addEventListener("keydown", function () {
    setTimeout(verifyPassword, 1);
})
PASSWORD_VERIFY.addEventListener("keydown", function () {
    setTimeout(verifyPassword, 1);
})

function lightTest(value) {
    if (value) {
        BD_THEME.classList.add("box-shadow-success-btn");
        BD_LANG.classList.add("box-shadow-success-btn");
    }
    else {
        BD_THEME.classList.remove("box-shadow-success-btn");
        BD_LANG.classList.remove("box-shadow-success-btn");
    }
}