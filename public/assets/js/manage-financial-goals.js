function goToEdit(id) {
    window.location.href = "/CashManager/financial-goals/edit?i=" + id;
}

const MODAL_BTN = document.getElementById("modal-btn");

function modal(id) {
    MODAL_BTN.setAttribute("onclick", "deleteFinancialGoal(" + id + ")");
}

const TABLE = document.getElementById("table");


function deleteFinancialGoal(id) {
    const XMLH = new XMLHttpRequest;
    var url = "/CashManager/backend/querys.php";
    var data = "function=delete_financial_goal&id=" + id;

    XMLH.onreadystatechange = updateTable;

    function updateTable() {
        if (XMLH.readyState == 4 && XMLH.status === 200) {
            TABLE.innerHTML = XMLH.responseText;
            const successToast = document.getElementById('liveToast')
            const toastBootstrap = bootstrap.Toast.getOrCreateInstance(successToast)
            toastBootstrap.show()
        }
    }

    XMLH.open("POST", url, 1);
    XMLH.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    XMLH.send(data);
}