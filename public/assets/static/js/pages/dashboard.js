(function () {
  const chartKeys = [
    '__chartProfileVisit',
    '__chartVisitorsProfile',
    '__chartEurope',
    '__chartAmerica',
    '__chartIndonesia'
  ]

  const destroyCharts = () => {
    chartKeys.forEach((key) => {
      if (window[key]) {
        try {
          window[key].destroy()
        } catch (error) {
          console.error('Gagal menghancurkan chart:', key, error)
        }
        window[key] = null
      }
    })
  }

  const createChart = (key, element, options) => {
    if (!element) {
      return
    }

    if (window[key]) {
      window[key].destroy()
    }

    window[key] = new ApexCharts(element, options)
    window[key].render()
  }

  const renderDashboardCharts = () => {
    const profileVisitEl = document.querySelector('#chart-profile-visit')
    if (!profileVisitEl) {
      destroyCharts()
      return
    }

    const optionsProfileVisit = {
      annotations: { position: 'back' },
      dataLabels: { enabled: false },
      chart: { type: 'bar', height: 300 },
      fill: { opacity: 1 },
      plotOptions: {},
      series: [{ name: 'sales', data: [9, 20, 30, 20, 10, 20, 30, 20, 10, 20, 30, 20] }],
      colors: '#435ebe',
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      },
    }

    const optionsVisitorsProfile = {
      series: [70, 30],
      labels: ['Male', 'Female'],
      colors: ['#435ebe', '#55c6e8'],
      chart: { type: 'donut', width: '100%', height: '350px' },
      legend: { position: 'bottom' },
      plotOptions: { pie: { donut: { size: '30%' } } },
    }

    const baseAreaOptions = {
      series: [{ name: 'series1', data: [310, 800, 600, 430, 540, 340, 605, 805, 430, 540, 340, 605] }],
      chart: { height: 80, type: 'area', toolbar: { show: false } },
      colors: ['#5350e9'],
      stroke: { width: 2 },
      grid: { show: false },
      dataLabels: { enabled: false },
      xaxis: {
        type: 'datetime',
        categories: [
          '2018-09-19T00:00:00.000Z',
          '2018-09-19T01:30:00.000Z',
          '2018-09-19T02:30:00.000Z',
          '2018-09-19T03:30:00.000Z',
          '2018-09-19T04:30:00.000Z',
          '2018-09-19T05:30:00.000Z',
          '2018-09-19T06:30:00.000Z',
          '2018-09-19T07:30:00.000Z',
          '2018-09-19T08:30:00.000Z',
          '2018-09-19T09:30:00.000Z',
          '2018-09-19T10:30:00.000Z',
          '2018-09-19T11:30:00.000Z',
        ],
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { show: false },
      },
      yaxis: { labels: { show: false } },
      tooltip: { x: { format: 'dd/MM/yy HH:mm' } },
    }

    destroyCharts()

    createChart('__chartProfileVisit', profileVisitEl, optionsProfileVisit)
    createChart('__chartVisitorsProfile', document.getElementById('chart-visitors-profile'), optionsVisitorsProfile)
    createChart(
      '__chartEurope',
      document.querySelector('#chart-europe'),
      { ...baseAreaOptions, colors: ['#5350e9'] }
    )
    createChart(
      '__chartAmerica',
      document.querySelector('#chart-america'),
      { ...baseAreaOptions, colors: ['#008b75'] }
    )
    createChart(
      '__chartIndonesia',
      document.querySelector('#chart-indonesia'),
      { ...baseAreaOptions, colors: ['#dc3545'] }
    )
  }

  document.addEventListener('DOMContentLoaded', renderDashboardCharts)
  document.addEventListener('livewire:navigated', renderDashboardCharts)
})()
