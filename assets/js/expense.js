const INPUT_VALUE = document.getElementById("input-value");
const SPAN_MAX = document.getElementById("max");

function changeMax(value) {
    let option = document.getElementById(value);
    INPUT_VALUE.max = option.role
    SPAN_MAX.innerText = option.role
}