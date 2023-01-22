<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Monthly Sales</h5>
        </div>
        <div class="card-body">
          <canvas id="monthly-sales-chart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Target Revenue</h5>
        </div>
        <div class="card-body">
          <canvas id="target-revenue-chart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  $(function() {
    window.chartMonthlySales = new Chart('monthly-sales-chart', {
      type: 'bar',
      options: {
        responsive: true,
        scales: {
          y: {
            ticks: {
              callback: function(value, index, ticks) {
                return '$' + value;
              }
            }
          }
        }
      }
    });

    window.chartTargetRevenue = new Chart('target-revenue-chart', {
      type: 'bar',
      options: {
        responsive: true
      }
    });
  });

  $(document).ready(function() {
    fetch(base_url + '/chart/monthlySales', {
      method: 'GET'
    }).then(response => response.json()).then((response) => {
      chartMonthlySales.data.labels = response.data.labels;
      chartMonthlySales.data.datasets = response.data.datasets;
      chartMonthlySales.update();
    });

    fetch(base_url + '/chart/targetRevenue', {
      method: 'GET'
    }).then(response => response.json()).then((response) => {
      chartTargetRevenue.data.labels = response.data.labels;
      chartTargetRevenue.data.datasets = response.data.datasets;
      chartTargetRevenue.update();
    });

    let hChart = setInterval(async () => {
      fetch(base_url + '/chart/monthlySales', {
        method: 'GET'
      }).then(response => response.json()).then((response) => {
        chartMonthlySales.data.labels = response.data.labels;
        chartMonthlySales.data.datasets = response.data.datasets;
        chartMonthlySales.update();
      });

      fetch(base_url + '/chart/targetRevenue', {
        method: 'GET'
      }).then(response => response.json()).then((response) => {
        chartTargetRevenue.data.labels = response.data.labels;
        chartTargetRevenue.data.datasets = response.data.datasets;
        chartTargetRevenue.update();
      });
    }, 1000 * 60);
  });
</script>