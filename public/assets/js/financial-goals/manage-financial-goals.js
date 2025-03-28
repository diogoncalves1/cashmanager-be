const XML = new XMLHttpRequest;
const TABLE = document.getElementById("table");
const BTN_ADD = document.getElementById("add-financial-goal");

function goToAddFinancialGoal() {
    window.location.href = "financial-goals/add";
}
function goToEdit(id) {
    window.location.href = "financial-goals/edit/" + id;
}

BTN_ADD.addEventListener("click", goToAddFinancialGoal);

function goToEdit(id) {
    window.location.href = "/CashManager/financial-goals/edit/" + id;
}

async function modal(id) {
    var alert1 = await getWordXml("are_delete_fiancial_goal");
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
            deleteFinancialGoal(id);
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
async function deleteConfirm() {
    var deleted = await getWordXml("deletedF");
    var textAlert = await getWordXml("financial_goal_deleted")
    Swal.fire({
        title: deleted,
        text: textAlert,
        icon: "success"
    });
}

function deleteFinancialGoal(id) {

    var url = "/CashManager/financial-goals/delete/" + id;

    XML.onload = () => {
        console.log(XML.response);
        if (XML.response == 1) {
            var tr = document.getElementById("financial-goal-" + id);
            TABLE.removeChild(tr);

            deleteConfirm();
        }
        else {
            deleteError();
        }
    }

    XML.open("DELETE", url, 1);
    XML.send();
}