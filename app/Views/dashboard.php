<div class="container-fluid">
  <div class="row">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Weekly Sales</h5>
        </div>
        <div class="card-body">
            <canvas id="weekly-sales-chart" height="200" width="400"></canvas>
        </div>
      </div>

      <div class="card card-primary card-outline">
        <div class="card-body">
          <h5 class="card-title">Card title</h5>

          <p class="card-text">
            Some quick example text to build on the card title and make up the bulk of the card's
            content.
          </p>
          <a href="#" class="card-link">Card link</a>
          <a href="#" class="card-link">Another link</a>
        </div>
      </div><!-- /.card -->
    </div>
    <!-- /.col-md-6 -->
  </div>
  <!-- /.row -->
</div><!-- /.container-fluid -->
<script>
  $(function() {
    window.weeklySales = new Chart('weekly-sales-chart', {
      type: 'bar',
      data: {
        labels: ['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU', 'AHAD'],
        datasets: [{
            label: 'Grand Total',
            backgroundColor: '#007bff',
            borderColor: '#007bff',
            data: [1000, 2000, 3000, 2500, 2700, 2500, 3000]
          },
          {
            label: 'Balance',
            backgroundColor: '#ced4da',
            borderColor: '#ced4da',
            data: [700, 1700, 2700, 2000, 1800, 1500, 2000]
          }
        ]
      }
    });
  });
</script>