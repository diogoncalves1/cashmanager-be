const XML = new XMLHttpRequest
const DIV_CARDS = document.getElementsByClassName("cards");
const url = "/CashManager/backend/querys.php";
const REQUEST_COUNTER = document.getElementById("requests-counter");
const DIV_COUNTER = document.getElementById("requests");

function confirm(type, obj_id, request_id, role_id) {
    var data = "function=accept_request&type=" + type + "&obj_id=" + obj_id + "&id=" + request_id + "&role_id" + role_id;
    const REQUEST = document.getElementsByClassName("request" + request_id);

    REQUEST[0].style.padding = 0;
    REQUEST[0].style.marginBottom = "0px";
    REQUEST[0].style.marginTop = "0px";
    REQUEST[0].innerHTML = "";
    REQUEST[0].style.height = 0;

    function delete_request() {
        DIV_CARDS[0].removeChild(REQUEST[0]);
        var count = parseInt(REQUEST_COUNTER.innerHTML)
        if (count == 1)
            DIV_COUNTER.removeChild(REQUEST_COUNTER);
        else
            REQUEST_COUNTER.innerHTML = count - 1;
    }

    setTimeout(delete_request, 400);


    XML.open("POST", url, 1);
    XML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    XML.send(data);
}


function reject(request_id) {
    var data = "function=delete_share_request&id=" + request_id;

    const REQUEST = document.getElementsByClassName("request" + request_id);

    REQUEST[0].style.padding = 0;
    REQUEST[0].style.marginBottom = "0px";
    REQUEST[0].style.marginTop = "0px";
    REQUEST[0].innerHTML = "";
    REQUEST[0].style.height = 0;

    function delete_request() {
        DIV_CARDS[0].removeChild(REQUEST[0]);
        var count = parseInt(REQUEST_COUNTER.innerHTML)
        if (count == 1)
            DIV_COUNTER.removeChild(REQUEST_COUNTER);
        else
            REQUEST_COUNTER.innerHTML = count - 1;
    }

    setTimeout(delete_request, 400);


    XML.open("POST", url, 1);
    XML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    XML.send(data);
}