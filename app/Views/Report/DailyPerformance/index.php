<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-dark">
          <h5 class="card-title"><?= lang('App.dailyperformance') ?> <span class="daily-performance-title"></span></h5>
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-success export-report" href="<?= base_url('report/export/dailyPerformance') ?>" data-action="export" data-toggle="tooltip" title="Export to excel">
              <i class="fad fa-download"></i>
            </a>
            <a class="btn btn-tool bg-gradient-warning" href="#" data-widget="control-sidebar" data-toggle="tooltip" title="Filter" data-slide="true">
              <i class="fad fa-filter"></i>
            </a>
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
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-dark">
          <h5 class="card-title"><?= lang('App.revenue') . ' ' . lang('App.and') . ' ' . lang('App.forecast') ?> <span class="revenue-forecast-title"></span></h5>
          <div class="card-tools">
            <a class="btn btn-tool bg-gradient-success export-report" href="<?= base_url('report/export/dailyPerformance') ?>" data-action="export" data-toggle="tooltip" title="Export to excel">
              <i class="fad fa-download"></i>
            </a>
            <a class="btn btn-tool bg-gradient-warning" href="#" data-widget="control-sidebar" data-toggle="tooltip" title="Filter" data-slide="true">
              <i class="fad fa-filter"></i>
            </a>
          </div>
        </div>
        <div class="card-body">
          <div id="revenue-forecast-chart" style="height:400px; width:100%"></div>
        </div>
        <div class="overlay dark" id="revenue-forecast-loader">
          <i class="fad fa-sync fa-spin fa-4x"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Control Sidebar -->
<aside class="control-sidebar">
  <!-- Control sidebar content goes here -->
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-gradient-indigo">
          <div class="card-title"><i class="fad fa-filter"></i> <?= lang('App.filter') ?></div>
        </div>
        <div class="card-body control-sidebar-content" style="max-height:400px">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-biller"><?= lang('App.biller') ?></label>
                <select id="filter-biller" class="select-biller" data-placeholder="<?= lang('App.biller') ?>" style="width:100%">
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="filter-period"><?= lang('App.period') ?></label>
                <input id="filter-period" class="form-control form-control-border form-control-sm" type="month">
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-warning filter-clear"><?= lang('App.clear') ?></button>
            <button class="btn btn-primary filter-apply"><?= lang('App.apply') ?></button>
          </div>
        </div>
      </div>
    </div>
</aside>
<!-- /.control-sidebar -->
<script type="module">
  import {
    TableFilter
  } from '<?= base_url('assets/app/js/ridintek.js?v=') . $resver ?>';

  TableFilter.bind('apply', '.filter-apply');
  TableFilter.bind('clear', '.filter-clear');

  TableFilter.on('apply', () => {
    erp.echart.reload();
  });

  TableFilter.on('clear', () => {
    preSelect2('biller', '#filter-biller', 2);
    $('#filter-period').val('<?= date('Y-m') ?>').trigger('change');

    erp.echart.reload();
  });
</script>
<script>
  $(function() {
    erp.chart.dailyPerformance = echarts.init(document.querySelector('#daily-performance-chart'));
    erp.chart.revenueForecast = echarts.init(document.querySelector('#revenue-forecast-chart'));

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
  });

  $(document).ready(async function() {
    $('#filter-period').val('<?= date('Y-m') ?>');
    await preSelect2('biller', '#filter-biller', 2);

    erp.echart.reload = function() {
      $('#daily-performance-loader').fadeIn();
      $('#revenue-forecast-loader').fadeIn();

      fetch(base_url + '/chart/dailyPerformance?period=' + $('#filter-period').val() + '&biller=' + $('#filter-biller').val(), {
        method: 'GET'
      }).then(response => response.json()).then((response) => {
        $('#daily-performance-loader').fadeOut();

        fetch(base_url + '/select2/biller?limit=1&term=' + $('#filter-biller').val(), {
          method: 'GET'
        }).then(response => response.json()).then((response) => {
          if (!response.results.length) {
            console.warn('Results is empty.');
            return false;
          }

          $('.daily-performance-title').html(`(Biller: ${response.results[0].text}, Period: ${$('#filter-period').val()})`);
        });

        erp.chart.dailyPerformance.setOption(response.data);
      });

      fetch(base_url + '/chart/revenueForecast?period=' + $('#filter-period').val(), {
        method: 'GET'
      }).then(response => response.json()).then((response) => {
        $('#revenue-forecast-loader').fadeOut();

        $('.revenue-forecast-title').html(`(Period: ${$('#filter-period').val()})`);

        erp.chart.revenueForecast.setOption(response.data);
      });
    };

    erp.echart.reload();
  });
</script>