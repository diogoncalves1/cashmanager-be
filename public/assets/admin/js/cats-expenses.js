const BTN_CONFIRM_DELETE = document.getElementById("btn-delete");
const TBODY = document.getElementById("table");

function goToEdit(id) {
    window.location.href = "/CashManager/admin/categories-expenses/edit?i=" + id;
}

function goToAdd() {
    window.location.href = "/CashManager/admin/categories-expenses/add";
}

function updateModal(id) {
    BTN_CONFIRM_DELETE.setAttribute("onclick", "deleteCategory(" + id + ")");
}

function showErrorNotif() {
    var toast = document.getElementById('error-notif');
    var toastBstrap = bootstrap.Toast.getOrCreateInstance(toast);
    toastBstrap.show();
}

function showSuccessNotif() {
    var toast = document.getElementById('success-notif');
    var toastBstrap = bootstrap.Toast.getOrCreateInstance(toast);
    toastBstrap.show();
}

function deleteCategory(id) {
    $.ajax({
        url: "/CashManager/admin/categories-expenses/delete",
        method: "POST",
        data: {
            id: id
        },
        success: (data) => {

            console.log(data);
            if (data) {
                showSuccessNotif();
                var tr = document.getElementById("category-" + id);
                TBODY.removeChild(tr);
            }
            else
                showErrorNotif();
        }
    })
}
