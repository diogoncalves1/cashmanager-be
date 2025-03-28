function goToEdit(id) {
    window.location.href = "/CashManager/transactions/edit?i=" + id;
}
const httpRequest = new XMLHttpRequest;
const table = document.getElementById("table");
const TD = document.querySelectorAll("td");

function goToDelete(id) {

    let td_lenght = TD.length;
    for (i = 0; i < td_lenght; i++) {
        TD[i].innerHTML = '<div class="spinner-grow text-success" role="status"><span class="visually-hidden">Loading...</span></div>';
    }

    httpRequest.onreadystatechange = writeTable;
    var url = "/CashManager/transactions/delete";
    var data = "id=" + id;

    function writeTable() {
        if (httpRequest.readyState == 4 && httpRequest.status === 200) {
            table.innerHTML = httpRequest.responseText;
        }
    }

    httpRequest.open("POST", url, true);
    httpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    httpRequest.send(data);
}

function goToView(id) {
    window.location.href = "/CashManager/transactions/view/" + id;
}

const MODAL_BTN = document.getElementById("modal-btn");

function modal(id) {
    MODAL_BTN.setAttribute("onclick", "goToDelete(" + id + ")");
}

const TR = document.querySelectorAll("tr");
const VALUE_BTN = document.getElementById("value-ord");
var trs = [];

function sortValue(type) {
    TR[0].classList.remove("dropup");
    for (i = 0; i < 6; i++) {
        if (TR[0].children[i].classList.contains("dropdown-toggle")) {
            TR[0].children[i].classList.remove("dropdown-toggle");
        }
    }

    var trLenght = TR.length;
    var arrValues = [];
    for (i = 0; i < trLenght; i++) {
        if (TR[i].classList.contains("transaction")) {
            trs.push(TR[i]);
            arrValues.push(parseFloat(TR[i].children[1].ariaValueNow));
        }
    }
    sortArr(arrValues, 0, 0, 0, trs, type);

    TR[0].children[1].classList.add("dropdown-toggle");
    if (type == 0)
        type = 1;
    else {
        TR[0].classList.add("dropup");
        type = 0;
    }
    VALUE_BTN.setAttribute("onclick", "sortValue(" + type + ")")
}

const DATE_BTN = document.getElementById("date-ord");

function sortDate(type) {
    TR[0].classList.remove("dropup");
    for (i = 0; i < 6; i++) {
        if (TR[0].children[i].classList.contains("dropdown-toggle")) {
            TR[0].children[i].classList.remove("dropdown-toggle");
        }
    }
    TR[0].children[0].classList.add("dropdown-toggle");

    var trLenght = TR.length;
    var arrValues = [];
    for (i = 0; i < trLenght; i++) {
        if (TR[i].classList.contains("transaction")) {
            trs.push(TR[i]);
            arrValues.push(new Date(TR[i].children[0].ariaValueNow));
        }
    }
    sortArr(arrValues, 0, 0, 0, trs, type);
    if (type == 0)
        type = 1;
    else {
        TR[0].classList.add("dropup");
        type = 0;
    }
    DATE_BTN.setAttribute("onclick", "sortDate(" + type + ")")
}

const toast = document.getElementById('checkToast');
const toastBstrap = bootstrap.Toast.getOrCreateInstance(toast);
toastBstrap.show();