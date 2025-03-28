const BTN_CREATE = document.getElementById("add-objective");

function goToEdit(id) {
    window.location.href = "/CashManager/objectives/edit/" + id;
}

function goToCreate() {
    window.location.href = "/CashManager/objectives/add";
}

BTN_CREATE.addEventListener("click", goToCreate);

const TABLE = document.getElementById("table");
const httpRequest = new XMLHttpRequest;
const TD = document.querySelectorAll("td");

async function deleteError() {
    var error = await getWordXml("error");
    Swal.fire({
        title: error + "!",
        icon: "error"
    });
}
async function deleteConfirm() {
    var deleted = await getWordXml("deleted");
    var textAlert = await getWordXml("objective_deleted")
    Swal.fire({
        title: deleted,
        text: textAlert,
        icon: "success"
    });
}
function deleteObjective(id) {
    var url = "/CashManager/objectives/delete/" + id;

    httpRequest.onloadend = writeTable;

    function writeTable() {
        console.log(httpRequest.response)
        if (httpRequest.status === 200 && httpRequest.readyState === 4) {
            if (httpRequest.response == 1) {
                var tr = document.getElementById("tr-" + id);
                TABLE.removeChild(tr);
                deleteConfirm();
            }
            else
                deleteError();
        }
    }
    httpRequest.open("DELETE", url);
    httpRequest.send();
}

async function modal(id) {
    var alert1 = await getWordXml("are_delete_objective");
    var alert2 = await getWordXml("you_do_not_revert_this");
    var confirm = await getWordXml("yes_delete");
    var cancel = await getWordXml("cancel");
    console.log(alert1);
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
            deleteObjective(id);
        }
    });
}