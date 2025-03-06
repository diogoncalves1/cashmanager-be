const xml = new XMLHttpRequest;
const CONFIRM_BTN = document.getElementById("modal-btn");
const CARDS_CONTAINER = document.getElementById("cards-container");

function modal(id) {
    CONFIRM_BTN.setAttribute("onclick", "removeFriend(" + id + ")");
}

function removeFriend(id) {
    var url = "/CashManager/backend/querys.php";
    var data = "id=" + id + "&function=remove_friend";
    cookies = document.cookie.split("lang");
    var card = document.getElementsByClassName("user" + id);
    console.log(card[0]);
    card[0].innerHTML = "";
    card[0].style.padding = 0
    card[0].style.margin = 0
    function remove() {
        CARDS_CONTAINER.removeChild(card[0]);
        if (CARDS_CONTAINER.children.length == 0) {
            if (cookies[1].match("EN"))
                CARDS_CONTAINER.innerHTML = "<p class='text-center'>No Friends Added</p>";
            else
                CARDS_CONTAINER.innerHTML = "<p class='text-center'>Sem Amigos Adicionados</p>";
        }
    }

    setTimeout(remove, 500);

    xml.open("POST", url, 1);
    xml.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    xml.send(data);
}

