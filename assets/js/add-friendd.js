const INPUT = document.getElementById("search-input"); // INPUT ONDE É ESCRITO O ID
const DIV_RES = document.getElementById("search-result"); // DIV ONDE É IMPRIMIDO O RESULTADO DA PROCURA
const REQUEST = new XMLHttpRequest; // AJAX
const RECEIVED_REQUESTS = document.getElementById("received-requests"); // DIV ONDE SÃO IMPRIMIDOS OS PEDIDOS RECEBIDOS
const BTN = document.getElementById("btn-search"); // BOTAO DE PROCURAR
const SENDED_REQUEST = document.getElementById("request-sended"); // DIV ONDE SÃO IMPRIMIDOS OS PEDIDOS EVIADOS

function searchUser() { // FUNÇÃO QUE PROCURA O UTILIZADOR PROCURADO PELO ID
    var id = INPUT.value; // ID É RETIRADO DO VALOR DO INPUT

    var url = "../../backend/querys.php"; // URL 
    var data = "function=search_user&id=" + id; // DADOS ENVIADOS

    REQUEST.onload = updateSearch; // QUANDO RETORNAR A RESPOSTA FAZER A FUNCAO

    function updateSearch() { // FUNÇÃO QUE IMPRIME NA DIV O RESULTADO
        DIV_RES.innerHTML = REQUEST.responseText;
    }

    REQUEST.open("POST", url, 1);
    REQUEST.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    REQUEST.send(data);
}

function sendRequest(id, type) {
    let cookies = document.cookie.split("lang");

    var icon = document.getElementById("icon-result"); // ICON
    var icon_a = document.getElementById("icon-a"); // UTILIZADO PARA MUDAR OS ATRIBUTOS DO ONCLICK
    var url = "../../backend/querys.php"; // URL
    var data = "function=send_fried_request&id=" + id + "&type=" + type; // DADOS ENVIADOS
    var cards = document.getElementsByClassName("user" + id);
    let t = 0;
    const cardsLenght = cards.length;
    REQUEST.onload = request;

    function request() {

        if (t == 0) {
            console.log(1);
            if (type == 0) { // SE NÃO TIVER ENVIADO PEDIDO
                icon.setAttribute("xlink:href", "#people-check"); // ICON PASSA PARA CHECK
                icon_a.setAttribute("onclick", "sendRequest(" + id + "," + 1 + ")");
                if (SENDED_REQUEST.innerText == "Nenhum Pedido Enviado" || SENDED_REQUEST.innerText == "No Friend Sended") /* SE NÃO HOUVER
                PEDIDOS ENVIADOS */
                    SENDED_REQUEST.innerHTML = DIV_RES.innerHTML;
                else
                    SENDED_REQUEST.innerHTML += DIV_RES.innerHTML;
            }
            else {
                var row = cards[cardsLenght - 1];
                row.style.padding = 0;
                row.style.marginBottom = "0px";
                row.style.marginTop = "0px";
                row.innerHTML = "";
                row.style.height = 0;

                function remove() {
                    SENDED_REQUEST.removeChild(cards[cardsLenght - 1]);
                    if (SENDED_REQUEST.children.length == 0) {
                        if (cookies[1].match("EN") == null)
                            SENDED_REQUEST.innerText = "Nenhum Pedido Enviado";
                        else
                            SENDED_REQUEST.innerText = "No Friend Sended";
                    }
                }

                setTimeout(remove, 400);

                icon.setAttribute("xlink:href", "#people-add");
                icon_a.setAttribute("onclick", "sendRequest(" + id + "," + 0 + ")");
            }
            t++;
        }
    }

    REQUEST.open("POST", url, 1);
    REQUEST.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    REQUEST.send(data);
}

function confirm(id, accept) {
    var url = "../../backend/querys.php";
    var data = "function=set_friend_request&id=" + id + "&confirm=" + accept;
    let cookies = document.cookie.split("lang");
    var row = document.getElementById("received" + id);
    var counter = document.getElementById("friend-request-counter");
    row.style.padding = 0;
    row.innerHTML = "";
    row.style.height = 0;
    function remove() {
        RECEIVED_REQUESTS.removeChild(row);

        if (RECEIVED_REQUESTS.children.length == 0) {
            var a = document.getElementById("add-friend");
            a.removeChild(counter);
            if (cookies[1].match("EN") == null)
                RECEIVED_REQUESTS.innerText = "Nenhum Pedido Recebido";
            else
                RECEIVED_REQUESTS.innerText = "No Friend Requests";
        }
        else {
            var test = parseInt(counter.innerHTML) - 1;
            counter.innerHTML = test;
        }

    }

    setTimeout(remove, 400);
    REQUEST.open("POST", url, 1);
    REQUEST.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    REQUEST.send(data);
}

INPUT.addEventListener("focusout", searchUser);
BTN.addEventListener("click", searchUser);