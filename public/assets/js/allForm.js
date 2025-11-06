const groups = document.querySelectorAll(".form-group");
const btnSubmit = document.getElementById("btnSubmit");

Array.from(groups).forEach((group) => {
    let input = group.querySelector(".form-control");
    let erroFeedback = group.querySelector(".invalid-feedback");
    input.addEventListener("input", () => {
        isInputValid(input, erroFeedback);
    });
});

$(function () {
    btnSubmit.addEventListener("click", () => {
        feedbacks(groups);
    });

    $("#form").on("submit", async function (e) {
        try {
            feedbacks(groups);

            e.preventDefault();

            const url = e.target.action;
            var method;

            method = window.location.pathname.includes("create")
                ? "POST"
                : "PUT";

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function (response) {
                    successToast(response.message);
                },
                error: function (error) {
                    if (error.responseJSON.message)
                        return errorToast(error.responseJSON.message);
                    return errorToast("Erro na tentativa.");
                },
            });
        } catch (e) {
            console.log(e);
            infoToast("Erro");
        }
    });

    function feedbacks(groups) {
        Array.from(groups).map(async (group) => {
            let input = group.querySelector(".form-control");

            console.log(input.required);

            if (input.required) {
                let erroFeedback = group.querySelector(".invalid-feedback");
                if (!(await isInputValid(input, erroFeedback))) return null;
            }
        });
    }
});
