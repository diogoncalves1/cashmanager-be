const updateRates = document.getElementById("updateRates");

updateRates.addEventListener("click", tryUpdateRates);

async function tryUpdateRates() {
    var url = "/api/v1/currencies/update-rates";

    $.ajax({
        url: url,
        type: "GET",
        success: function (response) {
            successToast(response.message);
            $("#data-table").DataTable().ajax.reload();
        },
        error: function (error) {
            console.log(error);

            errorToast(error.responseJSON.message);
        },
    });
}
