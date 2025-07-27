function renderGraficoValoresPorCategoria(etiquetas, valores) {
  const ctx = document.getElementById('graficoValoresCategoria')?.getContext('2d');
  if (!ctx || !etiquetas.length || !valores.length) return;

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: etiquetas,
      datasets: [{
        label: 'Valor total (€)',
        data: valores,
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: context => `${context.parsed.y.toLocaleString()} €`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: value => value.toLocaleString() + ' €'
          },
          title: { display: true, text: 'Valor (€)' }
        },
        x: {
          title: { display: true, text: 'Categoría' }
        }
      }
    }
  });
}
