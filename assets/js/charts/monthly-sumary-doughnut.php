<script>
    let ctx = []
    let myChart = [];
    <?php for ($i = 0; $i <= $maxMonths; $i++) { ?>

        ctx[<?= $i ?>] = document.getElementById('myChart' + <?= $i ?>);
        // eslint-disable-next-line no-unused-vars

        myChart[<?= $i ?>] = new Chart(ctx[<?= $i ?>], {
            type: 'doughnut',
            data: {
                labels: ['<?= $expense ?>', '<?= $revenue ?>'],
                datasets: [{
                    label: '€',
                    data: [<?php if (isset($expenses[$i]))
                        echo $expenses[$i];
                    else
                        echo 0; ?>, <?php if (isset($revenues[$i]))
                              echo $revenues[$i];
                          else
                              echo 0; ?>],
                    backgroundColor: [
                        'rgba(255, 7, 58, 0.2)',
                        'rgba(2, 182, 2, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 7, 58)',
                        'rgba(2, 182, 2)'
                    ],
                    borderWidth: 1,
                    shadowOffsetX: 4,
                    shadowOffsetY: 4,
                    shadowBlur: 15,
                    shadowColor: 'rgba(0, 0, 0, 0.6)',

                }]
            },
            options: { // Permite que o gráfico se ajuste ao tamanho do contêiner
                layout: {
                    padding: {
                        bottom: 10 // Ajusta o espaçamento inferior (aumente ou diminua conforme necessário)
                    }
                },
                plugins: {
                    tooltip: {
                        boxPadding: 3,
                        callbacks: {
                            label: function (tooltipItem) {
                                // Adiciona o símbolo € no tooltip
                                return tooltipItem.raw.toFixed(2) + '<?= $coin; ?>'; // .toFixed(2) para garantir duas casas decimais
                            }
                        }
                    }
                },
                elements: {
                    arc: {
                        hoverOffset: 20
                    }
                }
            }
        })


    <?php } ?>
</script>