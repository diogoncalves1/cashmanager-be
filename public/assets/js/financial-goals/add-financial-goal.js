const HTTP = new XMLHttpRequest;
const VALUE_INPUT = document.getElementById("input-value");
const NAME_INPUT = document.getElementById("name-ipt");
const INITIAL_DATE = document.getElementById("initial-date");
const FINAL_DATE = document.getElementById("final-date");
const CATEGORY = document.getElementById("category-select");
const STATUS = document.getElementById("status-select");
const PRIORITY = document.getElementById("priority-select");
const BTN_SUBMIT = document.getElementById("submit-btn");

function verifyDate() {
    FINAL_DATE.min = INITIAL_DATE.value;
    INITIAL_DATE.max = FINAL_DATE.value
}



BTN_SUBMIT.addEventListener("click", addFinancialGoal);

async function inputEmpty() {
    const alert = await getWordXml("fill_all_details");
    Swal.fire({
        position: "top-center",
        icon: "warning",
        title: alert,
        showConfirmButton: false,
        timer: 1500
    });

}
async function addSuccess() {
    var added = await getWordXml("addedF");
    var textAlert = await getWordXml("financial_goal_added")
    Swal.fire({
        title: added,
        text: textAlert,
        icon: "success"
    });
}
async function addError() {
    var error = await getWordXml("error");
    Swal.fire({
        title: error + "!",
        icon: "error"
    });
}

function addFinancialGoal() {
    if (VALUE_INPUT.value == "" || NAME_INPUT.value == "" || INITIAL_DATE.value == "" || FINAL_DATE.value == "" || CATEGORY.value == "" || STATUS.value == "" || PRIORITY.value == "") {
        inputEmpty();
        return;
    }
    var data = "value=" + VALUE_INPUT.value + "&name=" + NAME_INPUT.value + "&start_date=" + INITIAL_DATE.value + "&final_date=" + FINAL_DATE.value + "&category=" + CATEGORY.value + "&status=" + STATUS.value + "&priority=" + PRIORITY.value;
    var url = window.location.href;

    HTTP.onload = () => {
        console.log(HTTP.response);
        if (HTTP.response == 1) {
            addSuccess();
        }
        else
            addError();
    }

    HTTP.open("POST", url);
    HTTP.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    HTTP.send(data);
}