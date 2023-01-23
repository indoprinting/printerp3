<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-user-plus"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form method="post" enctype="multipart/form-data" id="form">
    <?= csrf_field() ?>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-primary">
            <div class="card-title"><?= lang('App.notification') ?></div>
          </div>
          <div class="card-body table-responsive">
            <table class="table table-condensed table-hover table-striped" id="TableModal">
              <thead>
                <tr>
                  <th><?= lang('App.created_at') ?></th>
                  <th><?= lang('App.content') ?></th>
                </tr>
              </thead>
              <tfoot>
                <tr>
                  <th><?= lang('App.created_at') ?></th>
                  <th><?= lang('App.content') ?></th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    "use strict";

    window.Table = $('#Table').DataTable({
      ajax: {
        data: {
          <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        method: 'POST',
        url: base_url + '/profile/getNotifications'
      },
      columnDefs: [{
        targets: [0, 1],
        orderable: false
      }],
      fixedHeader: false,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, lang.App.all]
      ],
      order: [
        [1, 'asc']
      ],
      processing: true,
      responsive: true,
      scrollX: false,
      searchDelay: 1000,
      serverSide: true,
      stateSave: false
    });

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/humanresource/usergroup/add'
    });
  });
</script>