function language(lan) {
    window.location.href = "/CashManager/backend/select-lang.php?lang=" + lan;
}

const LIGHT_BTN = document.getElementById("light-btn");
const DARK_BTN = document.getElementById("dark-btn");
const REQUEST_MODE = new XMLHttpRequest;

const httpAlert = new XMLHttpRequest;

function readAlert(id) {
    var url = "/CashManager/backend/querys.php";
    var data = "id=" + id + "&function=set_readed_alert";

    httpAlert.open("POST", url, 1);
    httpAlert.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    httpAlert.send(data);
}

function readScheduledAlert(id) {
    var url = "/CashManager/backend/querys.php";
    var data = "id=" + id + "&function=set_readed_alert_scheduled";

    httpAlert.open("POST", url, 1);
    httpAlert.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    httpAlert.send(data);
}

function deleteAlertUser(id) {
    var url = "/CashManager/backend/querys.php";
    var data = "id=" + id + "&function=delete_alert_user";

    httpAlert.open("POST", url, 1);
    httpAlert.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    httpAlert.send(data);
}

const ICON_MODE_BTN = document.getElementById("icon-mode");

function changeMode(value) {

    let url = "/CashManager/backend/querys.php";
    let data = "mode=" + value + "&function=set_mode";
    const tp = document.getElementsByClassName("primary");
    const ts = document.getElementsByClassName("success");
    const td = document.getElementsByClassName("danger");
    console.log(ICON_MODE_BTN)
    console.log(value);
    if (value == "light") {
        ICON_MODE_BTN.setAttribute("href", "#sun-fill");
    }
    else
        ICON_MODE_BTN.setAttribute("href", "#moon-stars-fill");
    REQUEST_MODE.onreadystatechange = () => {
        console.log(REQUEST_MODE.response);
    }

    REQUEST_MODE.open("POST", url);
    REQUEST_MODE.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    REQUEST_MODE.send(data);


    if (value == "dark") {
        document.body.setAttribute("data-bs-theme", "dark");
        LIGHT_BTN.classList.remove("active");
        DARK_BTN.classList.add("active");

        var numSucTable = ts.length;
        var numDanTable = td.length;
        for (i = 0; i < numSucTable; i++) {
            ts[i].classList.remove("table-success");
        }
        for (i = 0; i < numDanTable; i++) {
            td[i].classList.remove("table-danger");
        }

        var numPriTable = tp.length;
        for (i = 0; i < numPriTable; i++) {
            tp[i].classList.remove("table-primary")
        }

    }
    else {
        document.body.setAttribute("data-bs-theme", "light");
        DARK_BTN.classList.remove("active");
        LIGHT_BTN.classList.add("active");

        var numSucTable = ts.length;
        var numDanTable = td.length;
        for (i = 0; i < numSucTable; i++) {
            ts[i].classList.add("table-success");
        }
        for (i = 0; i < numDanTable; i++) {
            td[i].classList.add("table-danger");
        }

        var numPriTable = tp.length;
        for (i = 0; i < numPriTable; i++) {
            tp[i].classList.add("table-primary")
        }
    }




}

function sortArr(arr, arr2 = 0, arr3 = 0, arr4 = 0, arr5 = 0, order) {
    var arrL = arr.length;

    if (order == 0) {
        for (i = 0; i < arrL; i++) {
            for (j = i; j < arrL; j++) {
                if (arr[i] < arr[j]) {
                    var t = arr[i];
                    arr[i] = arr[j];
                    arr[j] = t;
                    if (arr2 != 0) {
                        var t2 = arr2[i];
                        arr2[i] = arr2[j];
                        arr2[j] = t2;

                    }
                    if (arr3 != 0) {
                        var t3 = arr3[i];
                        arr3[i] = arr3[j];
                        arr3[j] = t3;
                    }
                    if (arr4 != 0) {
                        var t4 = arr4[i];
                        arr4[i] = arr4[j];
                        arr4[j] = t4;
                    }
                    if (arr5 != 0) {
                        var t5 = arr5[i].innerHTML;
                        arr5[i].innerHTML = arr5[j].innerHTML;
                        arr5[j].innerHTML = t5;
                    }
                }
            }
        }
    }
    else {
        for (i = 0; i < arrL; i++) {
            for (j = i; j < arrL; j++) {
                if (arr[i] > arr[j]) {
                    var t = arr[j];
                    arr[j] = arr[i];
                    arr[i] = t;
                    if (arr2 != 0) {
                        var t2 = arr2[j];
                        arr2[j] = arr2[i];
                        arr2[i] = t2;
                    }
                    if (arr3 != 0) {
                        var t3 = arr3[j];
                        arr3[j] = arr3[i];
                        arr3[i] = t3;
                    }
                    if (arr4 != 0) {
                        var t4 = arr4[j];
                        arr4[j] = arr4[i];
                        arr4[i] = t4;
                    }
                    if (arr5 != 0) {
                        var t5 = arr5[j].innerHTML;
                        arr5[j].innerHTML = arr5[i].innerHTML;
                        arr5[i].innerHTML = t5;
                    }
                }

            }
        }
    }
}