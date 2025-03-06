const INITIAL_DATE = document.getElementById("initial-date");
const FINAL_DATE = document.getElementById("final-date");

function verifyDate() {
    FINAL_DATE.min = INITIAL_DATE.value;
    INITIAL_DATE.max = FINAL_DATE.value
}