<script>
    const chart = document.getElementById('myChart');
    const ctx = chart.getContext('2d');
    var dateComparison = <?= $dateComparisonJson ?>;
    var accountsMonthsAmountObject = <?= $accountsMonthsAmountJson; ?>;
    var accountsName = <?= $accountsNameJson ?>;
    var keys = [];
    var accountsMonthsAmounts = [];
    var datasets = [];
    const NUM_ACCOUNTS = accountsName.length;
    const NUM_DATE = dateComparison.length;
    console.log(dateComparison);

    Object.keys(accountsMonthsAmountObject).forEach(function(key) {
        keys.push(key);
    })

    for (i = 0; i < NUM_ACCOUNTS; i++) {
        accountsMonthsAmounts = [];

        for (j = 0; j < NUM_DATE; j++) {
            accountsMonthsAmounts.push(accountsMonthsAmountObject[keys[j]]
                .hasOwnProperty(i) ?
                accountsMonthsAmountObject[keys[j]][i] : 0);
        }
        var color = getRandomColor();
        colorPosition = color.indexOf(")");
        var colorOpacity = color.slice(0, colorPosition - 3) + "0" + color.slice(colorPosition);
        console.log(accountsMonthsAmountObject);
        console.log(i)
        var gradient = ctx.createLinearGradient(0, 0, 0, chart.height * 1.5);
        gradient.addColorStop(0, color);
        gradient.addColorStop(1, colorOpacity);

        datasets.push({
            label: accountsName[i],
            data: accountsMonthsAmounts,
            lineTension: 0.3,
            fill: true,
            backgroundColor: gradient,
            borderColor: color,
            borderWidth: 2,
            pointBackgroundColor: colorOpacity,
        });
    }


    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dateComparison,
            datasets: datasets
        },
        options: {
            plugins: {
                tooltip: {
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


    function getRandomColor() {

        var color = "rgba(" + Math.floor(Math.random() * 255) + "," + Math.floor(Math.random() * 255) + "," + Math.floor(
            Math
            .random() *
            255) + ", 0.6)";
        return color;
    }
</script>