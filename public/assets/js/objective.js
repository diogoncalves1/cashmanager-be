const INPUT_VALUE = document.getElementById("value-input");
const MAX_LABEL = document.getElementById("max");
const ACCOUNT_SELECT = document.getElementById("account-select");
const OBJECTIVE_SELECT = document.getElementById("objetive-select");
const GOAL_REMAINING = document.getElementById("total-remaining");
function updateMax(){
    let accountMax = document.getElementById(ACCOUNT_SELECT.value);
    let objetiveMax = document.getElementById(OBJECTIVE_SELECT.value)
    accountMax = parseFloat(accountMax.role)
    objetiveMax = parseFloat(objetiveMax.role)
    INPUT_VALUE.max = accountMax > objetiveMax ? objetiveMax : accountMax;
    MAX_LABEL.innerText =  accountMax > objetiveMax ? objetiveMax : accountMax;
    GOAL_REMAINING.innerText = objetiveMax
}