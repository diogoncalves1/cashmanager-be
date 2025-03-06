let expense = document.getElementById("expense");
let revenue = document.getElementById("revenue");

function goToExpense() {
  window.location.href = "/CashManager/transactions/add/expense";
}
function goToRevenue() {
  window.location.href = "/CashManager/transactions/add/revenue";
}

expense.addEventListener("click", goToExpense);
revenue.addEventListener("click", goToRevenue);

let week = document.getElementById("week");
let month = document.getElementById("month");
let year = document.getElementById("year");
let chartWeek = document.getElementById("myChart");
let chartMonth = document.getElementById("myChart2");
const CHART_YEAR = document.getElementById("myChart3");
let btn = document.getElementById("button");

function showWeek() {
  chartMonth.style.display = "none";
  chartWeek.style.display = "block";
  CHART_YEAR.style.display = "none";
  btn.textContent = week.textContent;
}

function showMonth() {
  chartWeek.style.display = "none";
  chartMonth.style.display = "block";
  CHART_YEAR.style.display = "none";
  btn.textContent = month.textContent;
}

function showYear() {
  chartWeek.style.display = "none";
  chartMonth.style.display = "none";
  CHART_YEAR.style.display = "block";
  btn.textContent = year.textContent;
}

week.addEventListener("click", showWeek);
month.addEventListener("click", showMonth);
year.addEventListener("click", showYear)


function goToManageTransactions(type) {
  window.location.href = "/CashManager/transactions?type=" + type;
}

function goToviewTransaction(id) {
  window.location.href = "/CashManager/transactions/view?i=" + id;
}