// ligações com outras paginas
var createbtn = document.getElementById("createbtn");
var logo = document.getElementById("logo");

function goToHomePage() {
    window.location.href = "/CashManager/public/";
}

function goToCreatePage() {
    window.location.href = "/CashManager/public/sign-up";
}

logo.addEventListener("click", goToHomePage);
createbtn.addEventListener("click", goToCreatePage);

/* Traduções */
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
