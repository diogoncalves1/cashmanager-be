function goToEdit(id) {
    window.location.href = "/CashManager/public/debts/edit?i=" + id;
}

const httpRequest = new XMLHttpRequest;
const table = document.getElementById("table");
const TD = document.querySelectorAll("td");

function goToDelete(id) {

    var tr = document.getElementById("tr-" + id);

    var url = "/CashManager/backend/querys.php";
    var data = "id=" + id + "&function=delete_debt";

    httpRequest.onloadend = writeTable;

    function writeTable() {
        if (httpRequest.status === 200 && httpRequest.readyState === 4) {
            if (httpRequest.response == 1) {
                table.removeChild(tr);
                const successToast = document.getElementById('liveToast')
                const toastBootstrap = bootstrap.Toast.getOrCreateInstance(successToast)
                toastBootstrap.show()
            }
            else {
                const alertToast = document.getElementById('liveToastWarning')
                const toastBootstrap = bootstrap.Toast.getOrCreateInstance(alertToast)
                toastBootstrap.show()
            }
        }
    }


    httpRequest.open("POST", url, true);
    httpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    httpRequest.send(data);
}

const MODAL_BTN = document.getElementById("modal-btn");

function modal(id) {
    MODAL_BTN.setAttribute("onclick", "goToDelete(" + id + ")");
}

const toast = document.getElementById('checkToast');
const toastBstrap = bootstrap.Toast.getOrCreateInstance(toast);
toastBstrap.show();