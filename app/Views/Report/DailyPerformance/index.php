<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-dark">
          <h5 class="card-title"><?= lang('App.revenue') . ' ' . lang('App.and') . ' ' . lang('App.forecast') ?></h5>
          <div class="card-tools" style="max-width:150px;width:100%">
            <input id="period" class="form-control form-control-border form-control-sm" type="month">
          </div>
        </div>
        <div class="card-body">
          <div id="revenue-forecast-chart" style="height:400px; width:100%"></div>
        </div>
        <div class="overlay dark" id="revenue-forecase-loader">
          <i class="fad fa-sync fa-spin fa-4x"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-dark">
          <h5 class="card-title"><?= lang('App.dailyperformance') ?></h5>
          <div class="card-tools" style="max-width:150px;width:100%">
            <select id="biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%">
            </select>
          </div>
        </div>
        <div class="card-body">
          <div id="daily-performance-chart" style="height:400px; width:100%"></div>
        </div>
        <div class="overlay dark" id="daily-performance-loader">
          <i class="fad fa-sync fa-spin fa-4x"></i>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  $(function() {
    erp.chart.revenueForecast = echarts.init(document.querySelector('#revenue-forecast-chart'));
    erp.chart.dailyPerformance = echarts.init(document.querySelector('#daily-performance-chart'));

    erp.chart.revenueForecast.setOption({
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'shadow'
        }
      },
      legend: {
        data: [],
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
          name: '<?= lang('App.targetrevenue') ?>',
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
          name: '<?= lang('App.averagerevenue') ?>',
          type: 'bar',
          emphasis: {
            focus: 'series'
          },
          data: []
        },
        {
          name: '<?= lang('App.forecast') ?>',
          type: 'bar',
          emphasis: {
            focus: 'series'
          },
          data: []
        }
      ]
    });

    erp.chart.dailyPerformance.setOption({
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'shadow'
        }
      },
      legend: {
        data: [],
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
          type: 'line',
          emphasis: {
            focus: 'series'
          },
          data: []
        }, {
          name: '<?= lang('App.stockvalue') ?>',
          type: 'line',
          emphasis: {
            focus: 'series'
          },
          data: []
        },
        {
          name: '<?= lang('App.receivable') ?>',
          type: 'line',
          emphasis: {
            focus: 'series'
          },
          data: []
        }
      ]
    });
  });

  $(document).ready(function() {
    $('#period').val('<?= date('Y-m') ?>');

    $('#period').change(function() {
      $('#revenue-forecase-loader').fadeIn();

      fetch(base_url + '/chart/revenueForecast?period=' + this.value, {
        method: 'GET'
      }).then(response => response.json()).then((response) => {
        $('#revenue-forecase-loader').fadeOut();

        erp.chart.revenueForecast.setOption(response.data);
      });
    });

    fetch(base_url + '/chart/revenueForecast', {
      method: 'GET'
    }).then(response => response.json()).then((response) => {
      $('#revenue-forecase-loader').fadeOut();

      erp.chart.revenueForecast.setOption(response.data);
    });

    fetch(base_url + '/chart/dailyPerformance', {
      method: 'GET'
    }).then(response => response.json()).then((response) => {
      $('#daily-performance-loader').fadeOut();

      erp.chart.dailyPerformance.setOption(response.data);
    });

    let hChart = setInterval(async () => {
      if ($('#revenue-forecast-chart').length == 0) {
        console.log('Auto update chart is disabled.');
        clearInterval(hChart);
        return false;
      }

      $('#revenue-forecase-loader').fadeIn();
      $('#daily-performance-loader').fadeIn();

      fetch(base_url + '/chart/revenueForecast', {
        method: 'GET'
      }).then(response => response.json()).then((response) => {
        $('#revenue-forecase-loader').fadeOut();

        erp.chart.revenueForecast.setOption(response.data);
      });

      fetch(base_url + '/chart/dailyPerformance', {
        method: 'GET'
      }).then(response => response.json()).then((response) => {
        $('#daily-performance-loader').fadeOut();

        erp.chart.dailyPerformance.setOption(response.data);
      });
    }, 1000 * 60);
  });
</script>