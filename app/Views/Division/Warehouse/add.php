<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-user-plus"></i> <?= $title ?></h5>
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
                  <input id="code" name="code" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.code') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.name') ?></label>
                  <input id="name" name="name" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.name') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="address"><?= lang('App.address') ?></label>
                  <input id="address" name="address" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.address') ?>">
                </div>
                <div class="form-group">
                  <label><?= lang('App.coordinate') ?></label>
                  <div class="row">
                    <div class="col-md-6">
                      <input id="latitude" name="latitude" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.latitude') ?>" readonly>
                    </div>
                    <div class="col-md-6">
                      <input id="longitude" name="longitude" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.longitude') ?>" readonly>
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
                  <input id="phone" name="phone" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.phone') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="name"><?= lang('App.email') ?></label>
                  <input id="email" name="email" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.email') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <input type="checkbox" id="active" name="active" value="1" checked>
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
                <label for="delivery_time"><?= lang('App.deliverytime') ?></label>
                <input type="number" class="form-control form-control-border form-control-sm" id="delivery_time" name="delivery_time">
              </div>
              <div class="col-md-6">
                <label for="transfer_cycle"><?= lang('App.transfercycle') ?></label>
                <input type="number" class="form-control form-control-border form-control-sm" id="transfer_cycle" name="transfer_cycle">
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
          <div class="card-header bg-gradient-success">
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
                            <?php foreach (\App\Models\User::select('*')->whereIn('groups', ['TEAMSUPPORT'])->orderBy('fullname', 'asc')->get() as $ts) : ?>
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
  <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <button type="button" id="submit" class="btn bg-gradient-primary"><?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
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
      url: base_url + '/division/warehouse/add'
    });
  });
</script>