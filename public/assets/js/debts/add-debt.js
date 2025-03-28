const HTTP = new XMLHttpRequest;
const N_INPUT = document.getElementById("input-n");
const V_INPUT = document.getElementById("input-v");
const CREDITOR = document.getElementById("creditor-ipt");
const VALUE = document.getElementById("input-value");
const DATE = document.getElementById("date-ipt");
const RATE = document.getElementById("rate-ipt");
const COMPOUND_INTEREST = document.getElementById("compound-interest-ipt");
const INSTALLMENT = document.getElementById("installment");
const DESCRIPTION = document.getElementById("description");

function addInputs(check) {
    const N_DIV = document.getElementById("n-inst");
    const V_DIV = document.getElementById("value-inst");

    console.log(check);
    if (check) {
        N_DIV.classList.remove("d-none");
        V_DIV.classList.remove("d-none");
        N_DIV.classList.add("d-flex");
        V_DIV.classList.add("d-flex");
        N_INPUT.required = true;
        V_INPUT.required = true;
    }
    else {
        N_DIV.classList.remove("d-flex");
        V_DIV.classList.remove("d-flex");
        N_DIV.classList.add("d-none");
        V_DIV.classList.add("d-none");
        N_INPUT.required = false;
        V_INPUT.required = false;
    }
}

function addDebt() {
    var data = "credito= " + CREDITOR.value + "&value=" + VALUE.value + "&date=" + DATE.value;
    var url = window.location.href;

    HTTP.onload = () => {
        console.log(HTTP.response);
    }

    HTTP.open("POST", url);
    HTTP.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    HTTP.send(data);
}