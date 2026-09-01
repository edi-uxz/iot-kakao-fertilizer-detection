// === Grafik Suhu ===
const suhuCtx = document.getElementById('suhuChart').getContext('2d');
new Chart(suhuCtx, {
  type: 'line',
  data: {
    labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    datasets: [{
      label: 'Suhu (°C)',
      data: [25, 26, 28, 29, 27, 30, 31],
      borderColor: '#f59e0b',
      backgroundColor: 'rgba(245, 158, 11, 0.3)',
      fill: true,
      tension: 0.4
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});

// === Grafik pH ===
const phCtx = document.getElementById('phChart').getContext('2d');
new Chart(phCtx, {
  type: 'bar',
  data: {
    labels: ['pH 4', 'pH 5', 'pH 6', 'pH 7'],
    datasets: [{
      label: 'Tingkat pH Tanah',
      data: [20, 45, 60, 35],
      backgroundColor: ['#22c55e', '#3b82f6', '#f97316', '#eab308']
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});

// === Grafik Rekomendasi Pemupukan ===
const fertCtx = document.getElementById('fertilizerChart').getContext('2d');
new Chart(fertCtx, {
  type: 'line',
  data: {
    labels: ['1', '2', '3', '4', '5', '6'],
    datasets: [{
      label: 'Dosis Pupuk (kg/ha)',
      data: [0, 4, 8, 6, 10, 7],
      borderColor: '#10b981',
      backgroundColor: 'rgba(16,185,129,0.25)',
      fill: true,
      tension: 0.4
    }]
  },
  options: { responsive: true, plugins: { legend: { display: false } } }
});

// === Grafik Status Indikator ===
const indCtx = document.getElementById('indicatorChart').getContext('2d');
new Chart(indCtx, {
  type: 'doughnut',
  data: {
    labels: ['Nutrisi', 'Kelembapan', 'Kondisi AI'],
    datasets: [{
      data: [60, 25, 15],
      backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'],
      hoverOffset: 10
    }]
  },
//   options: { 
//   responsive: true,
//   maintainAspectRatio: false
// }

});
