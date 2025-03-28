<script>
const ctx3 = document.getElementById('myChart3').getContext('2d');
let dates = <?= json_encode($days_name) ?>;
let datesValues = <?= json_encode($days) ?>;

console.log(datesValues);
datesValues.reverse();
dates.reverse();

const myChart3 = new Chart(ctx3, {
    type: 'line',
    data: {
        labels: dates,
        datasets: [{
            data: datesValues,
            lineTension: 0.3,
            backgroundColor: gradient,
            borderColor: "rgb(2, 182, 2)",
            borderWidth: 4,
            pointBackgroundColor: "white",
            fill: true
        }]
    },
    options: {
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                boxPadding: 3,
                callbacks: {
                    label: function(tooltipItem) {
                        // Adiciona o símbolo € no tooltip
                        return tooltipItem.raw.toFixed(2) +
                            '<?= $userCoin; ?>'; // .toFixed(2) para garantir duas casas decimais
                    }
                }
            }
        },

    },
})
</script>