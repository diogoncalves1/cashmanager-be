<script>
    const ctx = document.getElementById('myChart').getContext('2d');

    var gradient = ctx.createLinearGradient(0, 0, 0, 1000); // cria um gradiente de cima para baixo
    gradient.addColorStop(0, 'rgba(2, 182, 2, 0.2)'); // cor verde no topo (opacidade 100%)
    gradient.addColorStop(1, 'rgba(0, 128, 0, 0)');

    var gradient1 = ctx.createLinearGradient(0, 0, 0, 1000); // cria um gradiente de cima para baixo
    gradient1.addColorStop(0, 'rgba(255, 7, 58, 0.2)'); // cor verde no topo (opacidade 100%)
    gradient1.addColorStop(1, 'rgba(0, 128, 0, 0)');

    // eslint-disable-next-line no-unused-vars
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [<?php for ($i = $maxMonths; $i >= 0; $i--) {
                            echo "'" . $month_name[$i] . "', ";
                        } ?>],
            datasets: [{
                    label: 'Mensal Total',
                    data: [<?php for ($i = $maxMonths; $i >= 0; $i--) {
                                echo $month[$i] . ",";
                            } ?>],
                    lineTension: 0,


                    backgroundColor: function(context) {
                        // Pega o valor atual do ponto
                        const value = context.dataset.data[context.dataIndex];
                        // Retorna a cor baseada no valor
                        return value > 0 ? 'rgba(2, 182, 2, 0.2)' : 'rgba(255, 7, 58, 0.2)';
                    },


                    borderColor: function(context) {
                        // Pega o valor atual do ponto
                        const value = context.dataset.data[context.dataIndex];
                        // Retorna a cor baseada no valor
                        return value > 0 ? 'rgba(2, 182, 2)' : 'rgba(255, 7, 58)';
                    },
                    borderWidth: 2,
                    pointBackgroundColor: "white",

                },
                {
                    label: '<?= $expense; ?>', // Legenda para a linha
                    data: [<?php for ($i = $maxMonths; $i >= 0; $i--) {
                                if (isset($expenses[$i]))
                                    echo $expenses[$i] . ",";
                                else
                                    echo 0 . ",";
                            } ?>], // Valores para a linha
                    type: 'line', // Tipo do gráfico para a linha
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: gradient1, // Cor da linha
                    borderWidth: 2, // Espessura da linha
                    fill: true, // Não preencher área abaixo da linha
                    tension: 0.5 // Curvatura da linha
                },
                {
                    label: '<?= $revenue; ?>', // Legenda para a linha
                    data: [<?php for ($i = $maxMonths; $i >= 0; $i--) {
                                if (isset($revenues[$i]))
                                    echo $revenues[$i] . ",";
                                else
                                    echo 0 . ",";
                            } ?>], // Valores para a linha
                    type: 'line', // Tipo do gráfico para a linha
                    borderColor: 'rgba(2, 182, 2)',
                    backgroundColor: gradient, // Cor da linha
                    borderWidth: 2, // Espessura da linha
                    fill: true, // Não preencher área abaixo da linha
                    tension: .5 // Curvatura da linha
                }
            ]
        },

        options: {

            plugins: {
                tooltip: {
                    boxPadding: 3,
                    callbacks: {
                        label: function(tooltipItem) {
                            // Adiciona o símbolo € no tooltip
                            return tooltipItem.raw.toFixed(2) + '<?= $coin; ?>'; // .toFixed(2) para garantir duas casas decimais
                        }
                    }
                }
            },

        },
        plugins: {
            annotation: {
                annotations: {
                    line1: {
                        type: 'line',
                        xMin: 0,
                        xMax: 11, // Aqui você pode configurar a posição da linha no eixo X
                        yMin: 0,
                        yMax: 0,
                        borderColor: 'red', // Cor da linha
                        borderWidth: 2, // Largura da linha
                        label: {
                            enabled: false, // Não habilitar o rótulo
                        }
                    }
                }
            }
        }

    })
</script>