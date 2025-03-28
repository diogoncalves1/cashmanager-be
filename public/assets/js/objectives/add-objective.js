const HTTP = new XMLHttpRequest;
const SUMBIT_BTN = document.getElementById("submit-btn");
const INPUT_NAME = document.getElementById("name-ipt");
const INPUT_VALUE = document.getElementById("value-ipt");

SUMBIT_BTN.addEventListener("click", addObjective);

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
    var edited = await getWordXml("created");
    var textAlert = await getWordXml("objective_created")
    Swal.fire({
        title: edited,
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

function addObjective() {

    if (INPUT_NAME.value != "" || INPUT_VALUE.value != "") {

        var data = "name=" + INPUT_NAME.value + "&value=" + INPUT_VALUE.value;
        var url = window.location.href;

        HTTP.onload = () => {
            console.log(HTTP.response);
            if (HTTP.response == 1)
                addSuccess();
            else
                addError();
        }

        HTTP.open("POST", url, 1);
        HTTP.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        HTTP.send(data);
    }
    else {
        inputEmpty();
    }
}