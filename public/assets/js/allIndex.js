function modalDelete(url) {
    modalAlert("Tem a certeza que quer apagar?", tryDelete, url);
}

function tryDelete(url) {
    $.ajax({
        url: url,
        type: "DELETE",
        success: function (response) {
            successToast(response.message);
            $("#data-table").DataTable().ajax.reload();
        },
        error: function (error) {
            if (error.status == 403) {
                warningToast(error.responseJSON.message);
            } else {
                if (error.responseJSON.message)
                    return errorToast(error.responseJSON.message);
                errorToast("Erro na tentativa.");
            }
        },
    });
}
