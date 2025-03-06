const SELECT_TYPE = document.getElementById("type");
const SELECT_FRIEND = document.getElementById("friend");
const SELECT_OBJ = document.getElementById("obj");
const OBJ_TITLE = document.getElementById("obj-title");
const XML = new XMLHttpRequest;
const url = "/CashManager/backend/querys.php";
const cookies = document.cookie.split("lang");


function test() {

    var type = SELECT_TYPE.value;
    var user_sent = SELECT_FRIEND.value;
    var data = "function=test_obj&type=" + type + "&user_sent=" + user_sent;

    XML.onload = updateSelect;

    switch (type) {
        case "1": {
            if (cookies[1].match("EN"))
                OBJ_TITLE.innerText = "Account";
            else
                OBJ_TITLE.innerText = "Conta";
            break;
        }
        case "2": {
            if (cookies[1].match("EN"))
                OBJ_TITLE.innerText = "Objective";
            else
                OBJ_TITLE.innerText = "Objetivo";
            break;
        }

        case "3": {
            if (cookies[1].match("EN"))
                OBJ_TITLE.innerText = "Financial Goal";
            else
                OBJ_TITLE.innerText = "Meta Financeira";
            break;
        }
    }

    function updateSelect() {
        SELECT_OBJ.innerHTML = XML.responseText;
        console.log(XML.responseText)
    }

    XML.open("POST", url, 1);
    XML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    XML.send(data);
}
const toast = document.getElementById('checkToast');
const toastBstrap = bootstrap.Toast.getOrCreateInstance(toast);
toastBstrap.show();