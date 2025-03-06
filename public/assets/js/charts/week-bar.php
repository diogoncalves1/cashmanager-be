<script>
// Graphs
const ctx = document.getElementById('myChart').getContext('2d');

var gradient = ctx.createLinearGradient(0, 0, 0, 600); // cria um gradiente de cima para baixo
gradient.addColorStop(0, 'rgba(2, 182, 2, 0.2)'); // cor verde no topo (opacidade 100%)
gradient.addColorStop(1, 'rgba(0, 128, 0, 0)');

// eslint-disable-next-line no-unused-vars
const myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            <?php for ($i = 6; $i >= 0; $i--) {
                    echo "'" . $days_name[$i] . "',";
                } ?>
        ],
        datasets: [{
            data: [
                <?php for ($i = 6; $i >= 0; $i--) {
                        echo  $days[$i] . ",";
                    } ?>
            ],
            lineTension: .3,
            backgroundColor: gradient,
            borderColor: "rgb(2, 182, 2)",
            borderWidth: 4,
            pointBackgroundColor: "rgb(2, 182, 2, 0.2)",
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