const MODAL_ACCOUNT_BTN = document.getElementById("modal-btn-account")
const MODAL_OBJECTIVE_BTN = document.getElementById("modal-btn-objective")
const MODAL_FINANCIAL_GOAL_BTN = document.getElementById("modal-btn-financial-goals")
const DIVS = document.getElementsByClassName("cards")
const XML = new XMLHttpRequest;
const URL = "/CashManager/backend/querys.php";
const cookies = document.cookie.split("lang");
console.log(DIVS);

function goToEdit(shareID) {
    window.location.href = "/CashManager/public/shares/edit";
}

function modalAccount(accID, idRow, user_id) {
    MODAL_ACCOUNT_BTN.setAttribute("onclick", "deleteShareAcc(" + accID + ", " + idRow + ", " + user_id + ")");
}
function modalObjective(objID, idRow, user_id) {
    MODAL_OBJECTIVE_BTN.setAttribute("onclick", "deleteShareObj(" + objID + ", " + idRow + ", " + user_id + ")");
}
function modalFinancialGoal(fGoalID, idRow, user_id) {
    MODAL_FINANCIAL_GOAL_BTN.setAttribute("onclick", "deleteShareFGoal(" + fGoalID + ", " + idRow + ", " + user_id + ")");
}

function deleteShareAcc(accID, idRow, user_id) {
    var data = "acc_id=" + accID + "&user_id=" + user_id;
    var url = "/CashManager/accounts/delete-share";
    var card = document.getElementsByClassName("share-acc-" + idRow);
    card[0].innerHTML = "";
    card[0].style.padding = 0;
    card[0].style.margin = 0;
    function updateCard() {
        DIVS[0].removeChild(card[0]);
        if (DIVS[0].children.length == 0) {
            if (cookies[1].match("EN"))
                DIVS[0].innerHTML = "<p class='text-center'>No Shared Accounts</p>";
            else
                DIVS[0].innerHTML = "<p class='text-center'>Sem Contas Partilhadas</p>";
        }
    }

    XML.onreadystatechange = () => { console.log(XML.response); }

    setTimeout(updateCard, 400)

    XML.open("POST", url, 1);
    XML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    XML.send(data);
}

function deleteShareObj(objID, idRow, user_id) {
    var data = "function=delete_share_obj&obj_id=" + objID + "&user_id=" + user_id;
    var card = document.getElementsByClassName("share-obj-" + idRow);
    card[0].innerHTML = "";
    card[0].style.padding = 0;
    card[0].style.margin = 0;
    function updateCard() {
        DIVS[1].removeChild(card[0]);
        if (DIVS[1].children.length == 0) {
            if (cookies[1].match("EN"))
                DIVS[1].innerHTML = "<p class='text-center'>No Shared Objectives</p>";
            else
                DIVS[1].innerHTML = "<p class='text-center'>Sem Objetivos Partilhadas</p>";
        }
    }

    XML.onreadystatechange = () => { console.log(XML.response); }

    setTimeout(updateCard, 400)

    XML.open("POST", URL, 1);
    XML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    XML.send(data);
}

function deleteShareFGoal(fGoalID, idRow, user_id) {
    var data = "function=delete_share_f_goal&f_goal_id=" + fGoalID + "&user_id=" + user_id;
    var card = document.getElementsByClassName("share-f-goal-" + idRow);
    card[0].innerHTML = "";
    card[0].style.padding = 0;
    card[0].style.margin = 0;
    function updateCard() {
        DIVS[2].removeChild(card[0]);
        if (DIVS[2].children.length == 0) {
            if (cookies[1].match("EN"))
                DIVS[2].innerHTML = "<p class='text-center'>No Shared Objectives</p>";
            else
                DIVS[2].innerHTML = "<p class='text-center'>Sem Objetivos Partilhadas</p>";
        }
    }

    XML.onreadystatechange = () => { console.log(XML.response); }

    setTimeout(updateCard, 400)

    XML.open("POST", URL, 1);
    XML.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    XML.send(data);
}