const BTN_CREATE = document.getElementById("add-debt");

function goToEdit(id) {
    window.location.href = "debts/edit/" + id;
}

function goToCreate() {
    window.location.href = "debts/add";
}

BTN_CREATE.addEventListener("click", goToCreate);

const httpRequest = new XMLHttpRequest;
const table = document.getElementById("table");
const TD = document.querySelectorAll("td");

const MODAL_BTN = document.getElementById("modal-btn");

async function modal(id) {
    var alert1 = await getWordXml("are_delete_debt");
    var alert2 = await getWordXml("you_do_not_revert_this");
    var confirm = await getWordXml("yes_delete");
    var cancel = await getWordXml("cancel");
    Swal.fire({
        title: alert1,
        text: alert2,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: cancel,
        confirmButtonText: confirm
    }).then((result) => {
        if (result.isConfirmed) {
            deleteDebt(id);
        }
    });
}

async function deleteError() {
    var error = await getWordXml("error");
    Swal.fire({
        title: error + "!",
        icon: "error"
    });
}
async function deleteSuccess() {
    var deleted = await getWordXml("deletedF");
    var textAlert = await getWordXml("financial_goal_deleted")
    Swal.fire({
        title: deleted,
        text: textAlert,
        icon: "success"
    });
}

function deleteDebt(id) {
    var tr = document.getElementById("tr-" + id);
    var url = "delete/" + id;

    httpRequest.onloadend = () => {
        if (httpRequest.response == 1) {
            table.removeChild(tr);
            deleteSuccess();
        }
        else {
            deleteError();
        }

    }


    httpRequest.open("DELETE", url, true);
    httpRequest.send(data);
}