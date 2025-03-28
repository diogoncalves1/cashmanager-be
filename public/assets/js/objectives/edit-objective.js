const HTTP = new XMLHttpRequest;
const INPUT_NAME = document.getElementById("name-ipt");
const INPUT_VALUE = document.getElementById("value-ipt");
const SUMBIT_BTN = document.getElementById("submit-btn");

SUMBIT_BTN.addEventListener("click", editObjective);

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
async function editSuccess() {
    var edited = await getWordXml("edited");
    var textAlert = await getWordXml("objective_edited")
    Swal.fire({
        title: edited,
        text: textAlert,
        icon: "success"
    });
}
async function editError() {
    var error = await getWordXml("error");
    Swal.fire({
        title: error + "!",
        icon: "error"
    });
}

function editObjective() {
    if (INPUT_NAME.value == "" || INPUT_VALUE == "") {
        inputEmpty();
        return 0;
    }
    var data = "name=" + INPUT_NAME.value + "&value=" + INPUT_VALUE.value;
    var url = window.location.href;

    HTTP.onload = () => {
        console.log(HTTP.response)
        if (HTTP.response == 1) {
            editSuccess();
        }
        else {
            editError();
        }
    }

    HTTP.open("PUT", url);
    HTTP.setRequestHeader('Content-Type', 'application/json');
    HTTP.send(data);
}