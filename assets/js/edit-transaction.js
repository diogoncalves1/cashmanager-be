function getCookie(name) {
    let cookies = document.cookie;
    var cookiesArr = cookies.split(";");
    for (i = 0; i < cookiesArr.length; i++) {
        let cookie = cookiesArr[i].trim();
        if (cookie.indexOf(name) === 0) {
            cookie = cookie.split("=");
            return cookie[1];
        }
    }
}

const lang = getCookie("lang");
const xml = new XMLHttpRequest;
function deletePhoto(id) {
    const DIV = document.getElementById("photo-div");
    const IMAGE = document.getElementById("expense-photo");
    const BTN = document.getElementById("close-btn");
    DIV.removeChild(BTN);


    var data = "function=delete_photo_expense&id=" + id;
    var url = "/CashManager/backend/querys.php";
    xml.open("POST", url);
    xml.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xml.send(data);

    IMAGE.style.height = "0%";

    function remove() {
        if (lang == "PT") {
            var proof = "Comprovante";
            var optional = "Opcional";
        }
        else {
            var proof = "Proof of Payment";
            var optional = "Optional";
        }
        DIV.innerHTML = '<label for="">' + proof + '<span> (' + optional + ')</span></label><input type="file" class="form - control" name="proof" accept="image/*, application/pdf">';
    }
    setTimeout(remove, 400);
}