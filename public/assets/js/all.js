$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

async function isInputValid(input, errorFeedback) {
    if (input.value == "" || input.value == null) {
        input.classList.remove("is-valid");
        input.classList.add("is-invalid");
        errorFeedback.innerText = "Preencha este campo";
        return 0;
    }

    if (input.type == "email") {
        if (!checkEmail(input, errorFeedback)) return 0;
    }

    var inputMinLength = input.getAttribute("minlength");
    if (inputMinLength) {
        if (input.value.length < inputMinLength) {
            errorFeedback.innerText =
                "O mínimo de caracteres é de " + inputMinLength + ".";
            input.classList.remove("is-valid");
            input.classList.add("is-invalid");
            return 0;
        }
    }
    var inputMaxLength = input.getAttribute("maxlength");
    if (inputMaxLength) {
        if (input.value.length > inputMaxLength) {
            errorFeedback.innerText =
                "O máximo de caracteres é de " + inputMaxLength + ".";
            input.classList.remove("is-valid");
            input.classList.add("is-invalid");
            return 0;
        }
    }
    var inputMax = input.max;
    if (inputMax) {
        if (parseFloat(input.value) > parseFloat(inputMax)) {
            console.log("aa");
            errorFeedback.innerText =
                "O valor é maior que o máximo atribuido: " + inputMax + ".";
            input.classList.remove("is-valid");
            input.classList.add("is-invalid");
            return 0;
        }
    }

    var inputExtra = input.getAttribute("data-extra");
    if (inputExtra && typeof window[inputExtra] === "function") {
        if (await window[inputExtra](input.value)) return 0;
    }

    input.classList.remove("is-invalid");
    input.classList.add("is-valid");
    return 1;
}

async function modalAlert(alertTitle, action, id) {
    Swal.fire({
        title: alertTitle,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sim Apagar",
    }).then((result) => {
        if (result.isConfirmed) {
            action(id);
        }
    });
}
function infoToast(title) {
    $(function () {
        var Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
        });
        Toast.fire({
            icon: "info",
            title: title,
        });
    });
}

function warningToast(title) {
    $(function () {
        var Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
        });
        Toast.fire({
            icon: "warning",
            title: title,
        });
    });
}
function successToast(title) {
    $(function () {
        var Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
        });
        Toast.fire({
            icon: "success",
            title: title,
        });
    });
}
function errorToast(title) {
    $(function () {
        var Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
        });
        Toast.fire({
            icon: "error",
            title: title,
        });
    });
}
function errorAlert(titleAlert, confirmButton = 1) {
    Swal.fire({
        title: titleAlert,
        icon: "error",
        showConfirmButton: confirmButton,
    });
}

function warningAlert(titleAlert, timerAlert) {
    const theme = getCookie("mode");

    if (theme == "dark") {
        var alertBackground = "#1e1e1e";
        var alertColor = "#ffffff";
    } else {
        var alertBackground = "#fff";
        var alertColor = "#545454";
    }

    Swal.fire({
        position: "top-center",
        icon: "warning",
        title: titleAlert,
        background: alertBackground,
        color: alertColor,
        showConfirmButton: false,
        timer: timerAlert,
    });
}

function successAlert(titleAlert, textAlert, confirmButton = 1) {
    Swal.fire({
        icon: "success",
        title: titleAlert,
        text: textAlert,
        showConfirmButton: confirmButton,
    });
}
