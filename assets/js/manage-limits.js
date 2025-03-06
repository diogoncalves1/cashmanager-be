function goToEdit(id) {
    window.location.href = "/CashManager/public/budgets/edit?i=" + id;
}

const MODAL_BTN = document.getElementById("modal-btn")
function modal(id, type) {
    MODAL_BTN.setAttribute("onclick", "goToDelete(" + id + ", " + type + ")");
}

const TABLE_DAILY = document.getElementById("table-daily");
const TABLE_WEEKLY = document.getElementById("table-weekly");
const TABLE_MONTHLY = document.getElementById("table-monthly");
const TABLE_ANNUAL = document.getElementById("table-annual");
const TD_DAILY = document.getElementsByClassName("td-daily");
const TD_WEEKLY = document.getElementsByClassName("td-weekly");
const TD_MONTHLY = document.getElementsByClassName("td-monthly");
const TD_ANNUAL = document.getElementsByClassName("td-annual");
const xml = new XMLHttpRequest;
const DIV_DAILY = document.getElementById("daily-budget");
const DIV_WEEKLY = document.getElementById("weekly-budget");
const DIV_MONTHLY = document.getElementById("monthly-budget");
const DIV_ANNUAL = document.getElementById("annual-budget");

function goToDelete(id, type) {
    var url = "/CashManager/backend/querys.php";
    var data = "id=" + id + "&function=delete_limit";
    var cookies = document.cookie.split("lang");

    console.log(type);
    switch (type) {

        case 0: {
            let td_lenght = TD_DAILY.length;
            for (i = 0; i < td_lenght; i++) {
                TD_DAILY[i].innerHTML = '<div class="spinner-grow text-success" role="status"><span class="visually-hidden">Loading...</span></div>';
            }
            function changeTable() {
                if (xml.readyState == 4 && xml.status == 200) {
                    if (xml.responseText != "")
                        TABLE_DAILY.innerHTML = xml.responseText;
                    else {
                        if (cookies[1].match("EN"))
                            DIV_DAILY.innerHTML = "<p class='text-center'>No Daily Budgets</p>";
                        else
                            DIV_DAILY.innerHTML = "<p class='text-center'>Sem Orçamentos Diários</p>";
                    }
                }
            }
            break;
        }
        case 1: {
            let td_lenght = TD_WEEKLY.length;
            for (i = 0; i < td_lenght; i++) {
                TD_WEEKLY[i].innerHTML = '<div class="spinner-grow text-success" role="status"><span class="visually-hidden">Loading...</span></div>';
            }
            function changeTable() {
                if (xml.readyState == 4 && xml.status == 200) {
                    if (xml.responseText != "")
                        TABLE_WEEKLY.innerHTML = xml.responseText;
                    else {
                        if (cookies[1].match("EN"))
                            DIV_WEEKLY.innerHTML = "<p class='text-center'>No Weekly Budgets</p>";
                        else
                            DIV_WEEKLY.innerHTML = "<p class='text-center'>Sem Orçamentos Semanais</p>";
                    }
                }
            }
            break;
        }
        case 2: {
            let td_lenght = TD_MONTHLY.length;
            for (i = 0; i < td_lenght; i++) {
                TD_MONTHLY[i].innerHTML = '<div class="spinner-grow text-success" role="status"><span class="visually-hidden">Loading...</span></div>';
            }
            function changeTable() {
                if (xml.readyState == 4 && xml.status == 200) {
                    if (xml.responseText != "")
                        TABLE_MONTHLY.innerHTML = xml.responseText;
                    else {
                        if (cookies[1].match("EN"))
                            DIV_MONTHLY.innerHTML = "<p class='text-center'>No Monthly Budgets</p>";
                        else
                            DIV_MONTHLY.innerHTML = "<p class='text-center'>Sem Orçamentos Mensais</p>";
                    }
                }
            }
            break;
        }
        case 3: {
            let td_lenght = TD_ANNUAL.length;
            for (i = 0; i < td_lenght; i++) {
                TD_ANNUAL[i].innerHTML = '<div class="spinner-grow text-success" role="status"><span class="visually-hidden">Loading...</span></div>';
            }
            function changeTable() {
                if (xml.readyState == 4 && xml.status == 200) {
                    if (xml.responseText != "")
                        TABLE_ANNUAL.innerHTML = xml.responseText;
                    else {
                        if (cookies[1].match("EN"))
                            DIV_ANNUAL.innerHTML = "<p class='text-center'>No Annual Budgets</p>";
                        else
                            DIV_ANNUAL.innerHTML = "<p class='text-center'>Sem Orçamentos Anuais</p>";
                    }
                }
            }
            break;
        }

    }

    xml.onreadystatechange = changeTable;
    xml.open("POST", url);
    xml.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xml.send(data)
}

const toast = document.getElementById('checkToast');
const toastBstrap = bootstrap.Toast.getOrCreateInstance(toast);
toastBstrap.show();