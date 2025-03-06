const FROM_CURRENCY = document.getElementById("from-currency")
const TO_CURRENCY = document.getElementById("to-currency")
const RESULT_DIV = document.getElementById("result");
const AMOUNT = document.getElementById("amount")
const INPUT_INITIAL_AMOUNT = document.getElementById("initial-amount");
const INPUT_RATE = document.getElementById("rate");
const INPUT_YEARS = document.getElementById("years");
const RESULT_INTEREST = document.getElementById("result-interest");
const INPUT_REINFORCEMENT = document.getElementById("reinforcement");
const DIV_CHART = document.getElementById("chart");

function convert() {
    RESULT_DIV.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
    var fromCurrency = FROM_CURRENCY.value;
    var toCurrency = TO_CURRENCY.value;
    var amountToConvert = AMOUNT.value;

    url = "https://v6.exchangerate-api.com/v6/fbd30e414a2fcb5b26108b54/latest/" + fromCurrency;
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error("Erro na requisição: " + response.status);
            }
            return response.json();
        })
        .then(data => {
            const conversion_rates = data.conversion_rates;
            const value = Object.entries(conversion_rates).find(t => t[0] == toCurrency)
            const Rate = value[1];
            var valueConvert = (amountToConvert * Rate).toFixed(2);
            RESULT_DIV.innerText = valueConvert;
        })

}


function getInterest() {
    var initialAmount = parseFloat(INPUT_INITIAL_AMOUNT.value);
    var rate = (INPUT_RATE.value / 100) / 12;
    var numberOfMonths = INPUT_YEARS.value * 12;
    var r = INPUT_REINFORCEMENT.value;
    let cookies = document.cookie.split("lang");
    var total = [];
    var dates = [];
    var interest = []
    var invested = [];
    for (i = 1; i <= numberOfMonths; i += 3) {
        var amount = (initialAmount * Math.pow((1 + rate), i) + r * ((Math.pow((1 + rate), i) - 1) / rate)).toFixed(2);

        total.push(amount);
        var d = new Date();
        interest.push(amount - initialAmount - i * r).toFixed(2);
        invested.push(initialAmount + i * r);
        d.setDate(d.getDate() + 31 * i);
        dates.push((d.getMonth() + 1 + "-" + d.getFullYear()));
    }

    var interest_translate = cookies[1].match("EN") ? "Interests" : "Juros";
    var invested_translate = cookies[1].match("EN") ? "Invested" : "Investido";
    RESULT_INTEREST.innerHTML = "<p>Total: " + amount + "</p><p>" + interest_translate + ": " + interest[interest.length - 1].toFixed(2) + "</p><p>" + invested_translate + ": " + invested[invested.length - 1] + "</p>";


    DIV_CHART.innerHTML = '<canvas id="result-chart"></canvas>';

    let ctx = document.getElementById('result-chart').getContext('2d');

    var gradient = ctx.createLinearGradient(0, 0, 0, 600);
    gradient.addColorStop(0, 'rgba(2, 182, 2, 0.2)');
    gradient.addColorStop(1, 'rgba(0, 128, 0, 0)');

    let myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: "Total",
                data: total,
                lineTension: .3,
                backgroundColor: "rgba(12, 73, 204, 0.2)",
                borderColor: "rgb(12, 73, 204)",
                borderWidth: 4,
                pointBackgroundColor: "rgb(2, 182, 2, 0.2)",
                fill: true
            },
            {
                label: interest_translate, // Legenda para a linha
                data: interest, // Valores para a linha
                type: 'line', // Tipo do gráfico para a linha
                borderColor: 'rgb(2, 182, 2)',
                backgroundColor: 'rgba(2, 182, 2, 0.18)', // Cor da linha
                borderWidth: 2, // Espessura da linha
                fill: true, // Não preencher área abaixo da linha
                tension: 0.5 // Curvatura da linha
            },
            {
                label: invested_translate, // Legenda para a linha
                data: invested, // Valores para a linha
                type: 'line', // Tipo do gráfico para a linha
                borderColor: 'rgb(196, 2, 2)',
                backgroundColor: 'rgba(204, 0, 0, 0.18)', // Cor da linha
                borderWidth: 2, // Espessura da linha
                fill: true, // Não preencher área abaixo da linha
                tension: 0.5 // Curvatura da linha
            },]
        },
        options: {
            plugins: {
                legend: {
                    display: true
                },
                tooltip: {
                    boxPadding: 3,
                }
            },

        },
    })


}
