// ligacoes com outras paginas
var login = document.getElementById("login");
var logo = document.getElementById("logo");

function goToHomePage() {
    window.location.href = "/CashManager/public/";
}
function goToLoginPage() {
    window.location.href = "/CashManager/public/login";
}
logo.addEventListener("click", goToHomePage);
login.addEventListener("click", goToLoginPage);

// Traducoes
let sLang = document.getElementById("select-language");
let langSelected = document.getElementById("language-selected");

function showSelectLang() {
    if (sLang.style.display == "none") {
        sLang.style.display = "flex";
    }
    else {
        sLang.style.display = "none";
    }
}

langSelected.addEventListener("click", showSelectLang);