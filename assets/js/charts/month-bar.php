<script>
    const ctx2 = document.getElementById('myChart2').getContext('2d');
    // eslint-disable-next-line no-unused-vars
    const myChart2 = new Chart(ctx2, {
        type: 'line',
        data: {
            labels: [
                <?php for ($i = 30; $i >= 0; $i -= 2) {
                    echo "'" . $days_name[$i] . "',";
                } ?>
            ],
            datasets: [{
                data: [
                    <?php for ($i = 30; $i >= 0; $i -= 2) {
                        echo  $days[$i] . ",";
                    } ?>
                ],
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