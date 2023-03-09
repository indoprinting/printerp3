<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-dark">
          <h5 class="card-title"><?= lang('App.monthlysales') ?></h5>
        </div>
        <div class="card-body">
          <div id="monthly-sales-chart" style="height:400px; width:100%"></div>
        </div>
        <div class="overlay dark" id="monthly-sales-loader">
          <i class="fad fa-sync fa-spin fa-4x"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-dark">
          <h5 class="card-title"><?= lang('App.targetrevenue') ?></h5>
        </div>
        <div class="card-body">
          <div id="target-revenue-chart" style="height:400px; width:100%"></div>
        </div>
        <div class="overlay dark" id="target-revenue-loader">
          <i class="fad fa-sync fa-spin fa-4x"></i>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  $(function() {
    erp.chart.monthlySales = echarts.init(document.querySelector('#monthly-sales-chart'));
    erp.chart.targetRevenue = echarts.init(document.querySelector('#target-revenue-chart'));

    erp.chart.monthlySales.setOption({
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'shadow'
        }
      },
      legend: {
        data: ['<?= lang('App.revenue') ?>', '<?= lang('Status.paid') ?>', '<?= lang('App.receivable') ?>'],
        textStyle: {
          color: '#888'
        }
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

    erp.chart.targetRevenue.setOption({
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'shadow'
        }
      },
      legend: {
        data: ['<?= lang('App.revenue') ?>', '<?= lang('Status.paid') ?>', '<?= lang('App.receivable') ?>'],
        textStyle: {
          color: '#888'
        }
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
      $('#monthly-sales-loader').fadeOut();

      erp.chart.monthlySales.setOption(response.data);
    });

    fetch(base_url + '/chart/targetRevenue', {
      method: 'GET'
    }).then(response => response.json()).then((response) => {
      $('#target-revenue-loader').fadeOut();

      erp.chart.targetRevenue.setOption(response.data);
    });
  });
</script>