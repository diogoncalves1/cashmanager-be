function addInputs(check) {
    const N_INPUT = document.getElementById("input-n");
    const V_INPUT = document.getElementById("input-v");
    const N_DIV = document.getElementById("n-inst");
    const V_DIV = document.getElementById("value-inst");

    console.log(check);
    if (check) {
        N_DIV.classList.remove("d-none");
        V_DIV.classList.remove("d-none");
        N_DIV.classList.add("d-flex");
        V_DIV.classList.add("d-flex");
        N_INPUT.required = true;
        V_INPUT.required = true;
    }
    else {
        N_DIV.classList.remove("d-flex");
        V_DIV.classList.remove("d-flex");
        N_DIV.classList.add("d-none");
        V_DIV.classList.add("d-none");
        N_INPUT.required = false;
        V_INPUT.required = false;
    }
}