const BTN_CREATE = document.getElementById("add-account");

function goToEdit(id) {
    window.location.href = "/CashManager/accounts/edit/" + id;
}

function goToView(id) {
    window.location.href = "/CashManager/transactions?id=" + id;
}

function goToCreate() {
    window.location.href = "/CashManager/accounts/add";
}

BTN_CREATE.addEventListener("click", goToCreate);


const httpRequest = new XMLHttpRequest;
const table = document.getElementById("table");
const TD = document.querySelectorAll("td");

function goToDelete(id) {

    var tr = document.getElementById("tr-" + id);

    var url = "/CashManager/accounts/delete/" + id;

    httpRequest.onloadend = writeTable;

    function writeTable() {
        console.log(httpRequest.response);
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


    httpRequest.open("DELETE", url, true);
    httpRequest.send();
}

const MODAL_BTN = document.getElementById("modal-btn");

function modal(id) {
    MODAL_BTN.setAttribute("onclick", "goToDelete(" + id + ")");
}

const toast = document.getElementById('checkToast');
const toastBstrap = bootstrap.Toast.getOrCreateInstance(toast);
toastBstrap.show();