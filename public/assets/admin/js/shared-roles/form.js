const inputCode = document.querySelector("[name='code']");
const errorFeedbackCode = document.getElementById("errorFeedbackCode");

inputCode.addEventListener("input", function () {
    checkRoleCode(this.value);
});

async function checkRoleCode(code) {
    var url = "/api/v1/shared-roles/check-code";
    var id = document.querySelector("#sharedRoleId")?.value;

    var response = await $.ajax({
        url: url,
        type: "GET",
        data: {
            code: code,
            id: id ?? null,
        },
    });

    console.log(response);

    if (response.additionals.exists) {
        inputCode.classList.remove("is-valid");
        inputCode.classList.add("is-invalid");
        errorFeedbackCode.innerText = "Esse código já existe";
    } else inputCode.classList.remove("is-invalid");

    return response.additionals.exists;
}
