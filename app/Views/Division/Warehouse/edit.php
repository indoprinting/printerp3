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
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="code"><?= lang('App.code') ?></label>
                  <input id="code" name="code" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.code') ?>" value="<?= $warehouse->code ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.name') ?></label>
                  <input id="name" name="name" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.name') ?>" value="<?= $warehouse->name ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="address"><?= lang('App.address') ?></label>
                  <input id="address" name="address" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.address') ?>" value="<?= $warehouse->address ?>">
                </div>
                <div class="form-group">
                  <label><?= lang('App.coordinate') ?></label>
                  <div class="row">
                    <div class="col-md-6">
                      <input id="latitude" name="latitude" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.latitude') ?>" value="<?= ($warehouseJS->lat ?? '') ?>" readonly>
                    </div>
                    <div class="col-md-6">
                      <input id="longitude" name="longitude" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.longitude') ?>" value="<?= ($warehouseJS->lon ?? '') ?>" readonly>
                    </div>
                  </div>
                  <div id="map" style="width:100%; height: 400px;"></div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="code"><?= lang('App.phone') ?></label>
                  <input id="phone" name="phone" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.phone') ?>" value="<?= ($warehouse->phone) ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.email') ?></label>
                  <input id="email" name="email" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.email') ?>" value="<?= ($warehouse->email) ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <input type="checkbox" id="active" name="active" value="1">
                  <label for="active"><?= lang('App.active') ?></label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-danger">
            <div class="card-title"><?= lang('App.producttransfer') ?></div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <label><?= lang('App.deliverytime') ?></label>
                <input type="number" class="form-control form-control-border form-control-sm" name="delivery_time" value="<?= $warehouseJS->delivery_time ?? 0 ?>">
              </div>
              <div class="col-md-6">
                <label><?= lang('App.transfercycle') ?></label>
                <input type="number" class="form-control form-control-border form-control-sm" name="transfer_cycle" value="<?= $warehouseJS->cycle_transfer ?? 0 ?>">
              </div>
              <div class="col-md-6">
                <label for="visit_days"><?= lang('App.visitday') ?></label>
                <select id="visit_days" name="visit_days[]" class="select" style="width:100%" multiple>
                  <option value="Minggu"><?= lang('App.sunday') ?></option>
                  <option value="Senin"><?= lang('App.monday') ?></option>
                  <option value="Selasa"><?= lang('App.tuesday') ?></option>
                  <option value="Rabu"><?= lang('App.wednesday') ?></option>
                  <option value="Kamis"><?= lang('App.thursday') ?></option>
                  <option value="Jumat"><?= lang('App.friday') ?></option>
                  <option value="Sabtu"><?= lang('App.saturday') ?></option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="visit_weeks"><?= lang('App.visitweek') ?></label>
                <select id="visit_weeks" name="visit_weeks[]" class="select" style="width:100%" multiple>
                  <option value="1"><?= lang('App.first') ?></option>
                  <option value="2"><?= lang('App.second') ?></option>
                  <option value="3"><?= lang('App.third') ?></option>
                  <option value="4"><?= lang('App.fourth') ?></option>
                  <option value="5"><?= lang('App.fifth') ?></option>
                  <option value="6"><?= lang('App.sixth') ?></option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header bg-gradient-warning">
            <div class="card-title"><?= lang('App.maintenanceschedule') ?></div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <table class="table table-condensed table-hover" id="TableModal">
                  <thead>
                    <tr>
                      <th><?= lang('App.category') ?></th>
                      <th><?= lang('App.pic') ?></th>
                      <th><?= lang('App.autoassign') ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $a = 1; ?>
                    <?php foreach (\App\Models\ProductCategory::get(['parent_code' => 'AST']) as $pcat) : ?>
                      <tr>
                        <td><?= $pcat->name ?><input type="hidden" name="maintenance[<?= $a ?>][category]" value="<?= $pcat->code ?>"></td>
                        <td>
                          <select id="pic_<?= strtolower($pcat->code) ?>" class="select-allow-clear" name="maintenance[<?= $a ?>][pic]" style="width:100%" data-placeholder="<?= lang('App.name') ?>">
                            <option value=""></option>
                            <?php foreach (\App\Models\User::select('*')->whereIn('groups', ['TEAMSUPPORT'])->where('active', 1)->orderBy('fullname', 'asc')->get() as $ts) : ?>
                              <option value="<?= $ts->id ?>"><?= $ts->fullname ?></option>
                            <?php endforeach; ?>
                          </select>
                        </td>
                        <td><input type="checkbox" id="assign_<?= strtolower($pcat->code) ?>" name="maintenance[<?= $a ?>][auto_assign]" value="1"></td>
                      </tr>
                      <?php $a++; ?>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    let active = <?= $warehouse->active ?>;
    let maintenances = <?= json_encode($warehouseJS->maintenances ?? []) ?>;
    let visit_days = <?= json_encode(explode(',', $warehouseJS->visit_days ?? '') ?? []) ?>;
    let visit_weeks = <?= json_encode(array_map(fn ($val) => trim($val), explode(',', $warehouseJS->visit_weeks ?? '')) ?? []) ?>;

    if (active) {
      $('#active').iCheck('check');
    }

    for (let m of maintenances) {
      $('#pic_' + m.category.toLowerCase()).val(m.pic).trigger('change');

      if (m.auto_assign == 1) {
        $('#assign_' + m.category.toLowerCase()).iCheck('check');
      }
    }

    $('#visit_days').val(visit_days).trigger('change');
    $('#visit_weeks').val(visit_weeks).trigger('change');

    createGoogleMaps({
      element: {
        map: '#map',
        searchBox: '#address',
        latitude: '#latitude',
        longitude: '#longitude'
      }
    });

    $('#TableModal').DataTable({
      columnDefs: [{
        targets: [1, 2],
        orderable: false
      }],
      order: [
        [0, 'asc']
      ],
      paging: false,
      processing: true
    });

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/division/warehouse/edit/<?= $warehouse->id ?>'
    });
  });
</script>