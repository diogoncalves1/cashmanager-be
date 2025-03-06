function getCookie(name) {
    let cookies = document.cookie;
    var cookiesArr = cookies.split(";");
    for (i = 0; i < cookiesArr.length; i++) {
        let cookie = cookiesArr[i].trim();
        if (cookie.indexOf(name) === 0) {
            cookie = cookie.split("=");
            return decodeURIComponent(cookie[1]);
        }
    }
}
const coin = getCookie("coin_symbol");
const DIV_CHART = document.getElementById("chart");
const BTNS = document.getElementsByClassName("exp-link");
const TABLE = document.getElementById("table");
function makeGraph(date, id) {
    for (i = 0; i < BTNS.length; i++) {
        if (BTNS[i].classList.contains("active"))
            BTNS[i].classList.remove("active");
    }
    BTNS[id].classList.add("active");
    console.log(date);
    var data = "function=get_data_expenses_summary&date=" + date;
    var url = "/CashManager/backend/querys.php";
    const xml = new XMLHttpRequest;
    xml.open("POST", url, 1);
    xml.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
    xml.send(data);

    xml.onloadend = function () {

        DIV_CHART.innerHTML = "";
        TABLE.innerHTML = "";
        var response = xml.responseText;
        var dataJson = JSON.parse(response, 1);
        if (dataJson.status == 1) {
            DIV_CHART.innerHTML = '<div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"><span class="visually-hidden">Loading...</span></div>';
            var value = dataJson.values;
            var backgroundColor = dataJson.backgroundColor;
            var borderColor = dataJson.borderColor;
            var catNames = dataJson.catNames;
            var backgroundColors = [];
            var borderColors = []
            var values = [];
            var total = 0;
            var backgroundLenght = backgroundColor.length;
            for (i = 0; i < backgroundColor.length; i++) {
                backgroundColors.push(backgroundColor[i][0]);
                borderColors.push(borderColor[i][0]);
                values.push((parseFloat(value[i][0])).toFixed(2));
                total += values[i];
            }
            sortArr(values, backgroundColors, borderColors, catNames, 0, 0);


            DIV_CHART.innerHTML = '<canvas id="result-chart"></canvas>';

            var dataSet = [{
                label: '€',
                data: values,
                backgroundColor: backgroundColors, // Cor do gráfico
                borderColor: borderColors,
                borderWidth: 1,
                shadowOffsetX: 4,
                shadowOffsetY: 4,
                shadowBlur: 15,
                shadowColor: 'rgba(0, 0, 0, 0.6)',
            }]


            let ctx = document.getElementById('result-chart').getContext('2d');
            let myChart = new Chart(ctx, {
                type: 'doughnut', // Tipo do gráfico: barra
                data: {
                    labels: catNames, // Rótulos no eixo X
                    datasets: dataSet,
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
                                    return tooltipItem.raw.toFixed(2) + coin; // .toFixed(2) para garantir duas casas decimais
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
            });
            for (i = 0; i < backgroundLenght; i++) {
                TABLE.innerHTML += "<td>" + catNames[i] + "</td>" + "<td>" + values[i] + coin + "</td>" + "<td>" + percentage(total, values[i]) + "%</td>";
            }
        }
    };

}

function percentage(total, value) {
    var percen = ((value / total) * 100).toFixed(2);
    return percen;
}