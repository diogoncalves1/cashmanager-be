<script>
    const ctx3 = document.getElementById('myChart3').getContext('2d');

    days = array(<?= json_encode($days) ?>);

    const myChart3 = new Chart(ctx3, {
        type: 'line',
        data: {
            labels: [
                <?php for ($i = 365; $i >= 0; $i -= 13) {
                    echo "'" . $days_name[$i] . "',";
                } ?>
            ],
            datasets: [{
                data: [
                    <?php for ($i = 365; $i >= 0; $i -= 13) {
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
                                '<?= $coin; ?>'; // .toFixed(2) para garantir duas casas decimais
                        }
                    }
                }
            },

        },
    })
</script>