const httpRequest = new XMLHttpRequest;
const table = document.getElementById("table");
const TD = document.querySelectorAll("td");

function goToDelete(id) {
    var url = "/CashManager/backend/querys.php";
    var data = "id=" + id + "&function=delete_scheduled_expenses";

    let tr = document.getElementById("tr-" + id);
    for (i = 0; i < tr.children.length; i++) {
        tr.style.height = "43px";
        tr.children[i].innerHTML = '';
        tr.style.height = "0px";
    }

    setTimeout(removeTr, 200);

    function removeTr() {
        table.removeChild(tr);
    }

    httpRequest.open("POST", url, true);
    httpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    httpRequest.send(data);
}

function confirm(id) {

    var url = "/CashManager/scheduled-expenses/confirm";
    var data = "id=" + id ;

    let tr = document.getElementById("tr-" + id);
    for (i = 0; i < tr.children.length; i++) {
        tr.style.height = "43px";
        tr.children[i].innerHTML = '';
        tr.style.height = "0px";
    }

    setTimeout(removeTr, 200);

    function removeTr() {
        table.removeChild(tr);
    }
    httpRequest.onreadystatechange = () => {
        console.log(httpRequest.responseText)
    }

    httpRequest.open("POST", url, true);
    httpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    httpRequest.send(data);
}

function goToView(id) {
    window.location.href = "/CashManager/public/scheduled-expenses/view?id=" + id;
}

function goToEdit(id) {
    window.location.href = "/CashManager/public/scheduled-expenses/edit?id=" + id;
}

const MODAL_BTN = document.getElementById("modal-btn");

function modal(id) {
    MODAL_BTN.setAttribute("onclick", "goToDelete(" + id + ")");
}
