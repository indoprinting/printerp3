<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Monthly Sales</h5>
        </div>
        <div class="card-body">
          <div id="monthly-sales-chart" style="height:400px; width:100%"></div>
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
          <div id="target-revenue-chart" style="height:400px; width:100%"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  $(function() {
    window.chartMonthlySales = echarts.init(document.querySelector('#monthly-sales-chart'), 'dark');
    window.chartTargetRevenue = echarts.init(document.querySelector('#target-revenue-chart'), 'dark');

    chartMonthlySales.setOption({
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'shadow'
        }
      },
      legend: {
        data: ['<?= lang('App.revenue') ?>', '<?= lang('Status.paid') ?>', '<?= lang('App.receivable') ?>']
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
      },
      xAxis: [{
        type: 'category',
        axisTick: {
          show: false
        },
        data: []
      }],
      yAxis: [{
        type: 'value'
      }],
      series: [{
          name: '<?= lang('App.revenue') ?>',
          type: 'bar',
          emphasis: {
            focus: 'series'
          },
          data: []
        },
        {
          name: '<?= lang('Status.paid') ?>',
          type: 'bar',
          stack: 'Total',
          emphasis: {
            focus: 'series'
          },
          data: []
        },
        {
          name: '<?= lang('App.receivable') ?>',
          type: 'bar',
          stack: 'Total',
          emphasis: {
            focus: 'series'
          },
          data: []
        }
      ]
    });

    chartTargetRevenue.setOption({
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'shadow'
        }
      },
      legend: {
        data: ['<?= lang('App.revenue') ?>', '<?= lang('Status.paid') ?>', '<?= lang('App.receivable') ?>']
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
      },
      xAxis: [{
        type: 'category',
        axisTick: {
          show: false
        },
        data: []
      }],
      yAxis: [{
        type: 'value'
      }],
      series: [{
          name: '<?= lang('App.target') ?>',
          type: 'bar',
          emphasis: {
            focus: 'series'
          },
          data: []
        },
        {
          name: '<?= lang('App.revenue') ?>',
          type: 'bar',
          emphasis: {
            focus: 'series'
          },
          data: []
        },
        {
          name: '<?= lang('Status.paid') ?>',
          type: 'bar',
          emphasis: {
            focus: 'series'
          },
          data: []
        }
      ]
    });
  });

  $(document).ready(function() {
    fetch(base_url + '/chart/monthlySales', {
      method: 'GET'
    }).then(response => response.json()).then((response) => {
      // chartMonthlySales.data.labels = response.data.labels;
      // chartMonthlySales.data.datasets = response.data.datasets;
      // chartMonthlySales.update();
      console.log(response.data);
      chartMonthlySales.setOption(response.data);
    });

    fetch(base_url + '/chart/targetRevenue', {
      method: 'GET'
    }).then(response => response.json()).then((response) => {
      // chartTargetRevenue.data.labels = response.data.labels;
      // chartTargetRevenue.data.datasets = response.data.datasets;
      // chartTargetRevenue.update();
      chartTargetRevenue.setOption(response.data);
    });

    let hChart = setInterval(async () => {
      if ($('#monthly-sales-chart').length == 0) {
        console.log('Auto update chart is disabled.');
        clearInterval(hChart);
        return false;
      }

      fetch(base_url + '/chart/monthlySales', {
        method: 'GET'
      }).then(response => response.json()).then((response) => {
        // chartMonthlySales.data.labels = response.data.labels;
        // chartMonthlySales.data.datasets = response.data.datasets;
        // chartMonthlySales.update();
      });

      fetch(base_url + '/chart/targetRevenue', {
        method: 'GET'
      }).then(response => response.json()).then((response) => {
        // chartTargetRevenue.data.labels = response.data.labels;
        // chartTargetRevenue.data.datasets = response.data.datasets;
        // chartTargetRevenue.update();
      });
    }, 1000 * 60);
  });
</script>