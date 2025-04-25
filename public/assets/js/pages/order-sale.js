// totalSales chart
options = {
    series: [44, 55, 41, 17, 15],
    chart: {
      fontFamily: 'Montserrat, system-ui',
      height: 320,
      type: 'donut',
      dropShadow: {
        enabled: false,
        color: '#111',
        top: -1,
        left: 3,
        blur: 3,
        opacity: 0.2
      }
    },
    stroke: {
      width: 0,
    },

    legend: {
      position: 'bottom',
      fontSize: '14px',
      // fontFamily: '"Poppins", sans-serif',
      fontWeight: 500,
      labels: {
        colors: 'rgba(var(--secondary),1)',
        useSeriesColors: false
      },
      markers: {
        width: 15,
        height: 15,
        radius: 5,
        offsetX: -4,
      },
    },
    plotOptions: {
      pie: {
        donut: {
          labels: {
            show: false,
            total: {
              showAlways: false,
              show: false
            }
          }
        }
      }
    },
    labels: ["Point A", "Point B", "Point C", "Point D", "Point E"],

    dataLabels: {
    enabled: false,
      dropShadow: {
        blur: 3,
        opacity: 0.8
      }
    },
    colors: ['rgba(var(--primary-dark),1)','rgba(var(--primary),1)','rgba(var(--danger-dark),1)','rgba(var(--danger),.3)','rgba(var(--warning),1)'],
    fill: {
      // type: 'pattern',
      type: ['pattern', 'solid', 'pattern', 'solid', 'solid'],
      opacity: 1,
      pattern: {
        enabled: true,
        style: ['verticalLines', 'horizontalLines', 'horizontalLines', 'circles','horizontalLines'],
      },
    },
    states: {
      hover: {
        filter: 'none'
      }
    },
    theme: {
      palette: 'palette2'
    },
    tooltip: {
      x: {
        show: false,
      },
      style: {
        fontSize: '16px',

      },
    },
  };

  chart = new ApexCharts(document.querySelector("#order-sale-chart"), options);
  chart.render();
