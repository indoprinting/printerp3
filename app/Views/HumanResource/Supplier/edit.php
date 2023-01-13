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
                  <label for="name"><?= lang('App.name') ?></label>
                  <input id="name" name="name" class="form-control form-control-border form-control-sm" placeholder="Ex. Riyan Widiyanto" value="<?= $supplier->name ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="company"><?= lang('App.company') ?></label>
                  <input id="company" name="company" class="form-control form-control-border form-control-sm" placeholder="Ex. PT. Ridintek Industri" value="<?= $supplier->company ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="phone"><?= lang('App.phone') ?></label>
                  <input id="phone" name="phone" class="form-control form-control-border form-control-sm" placeholder="Ex. 0823116620xx" value="<?= $supplier->phone ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="email"><?= lang('App.email') ?></label>
                  <input id="email" name="email" class="form-control form-control-border form-control-sm" placeholder="Ex. user@company.com" value="<?= $supplier->email ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="address"><?= lang('App.address') ?></label>
                  <input id="address" name="address" class="form-control form-control-border form-control-sm" placeholder="Ex. Jl. Bukit Kelapa xxx" value="<?= $supplier->address ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="city"><?= lang('App.city') ?></label>
                  <input id="city" name="city" class="form-control form-control-border form-control-sm" placeholder="Ex. Semarang" value="<?= $supplier->city ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="country"><?= lang('App.country') ?></label>
                  <input id="country" name="country" class="form-control form-control-border form-control-sm" placeholder="Ex. Indonesia" value="<?= $supplier->country ?>">
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
          <div class="card-header bg-gradient-success">
            <div class="card-title"><?= lang('App.bankaccount') ?></div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="accname"><?= lang('App.accountname') ?></label>
                  <input id="accname" name="accname" class="form-control form-control-border form-control-sm" placeholder="Ex. BCA, BNI, Mandiri" value="<?= ($supplierJS->acc_name ?? '') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="accno"><?= lang('App.accountnumber') ?></label>
                  <input id="accno" name="accno" class="form-control form-control-border form-control-sm" placeholder="Ex. 62502645xx" value="<?= ($supplierJS->acc_no ?? '') ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="accholder"><?= lang('App.accountholder') ?></label>
                  <input id="accholder" name="accholder" class="form-control form-control-border form-control-sm" placeholder="Ex. Riyan Widiyanto" value="<?= ($supplierJS->acc_holder ?? '') ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="accbic"><?= lang('App.accountbic') ?></label>
                  <input id="accbic" name="accbic" class="form-control form-control-border form-control-sm" placeholder="Ex. CENAIJDA" value="<?= ($supplierJS->acc_bic ?? '') ?>">
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
          <div class="card-header bg-gradient-purple">
            <div class="card-title"><?= lang('App.productpurchase') ?></div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <label for="delivery_time"><?= lang('App.deliverytime') ?></label>
                <input type="number" class="form-control form-control-border form-control-sm" id="delivery_time" name="delivery_time" value="<?= ($supplierJS->delivery_time ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label for="transfer_cycle"><?= lang('App.purchasecycle') ?></label>
                <input type="number" class="form-control form-control-border form-control-sm" id="purchase_cycle" name="purchase_cycle" value="<?= ($supplierJS->cycle_purchase ?? '') ?>">
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

    let visit_days = <?= json_encode(explode(',', $supplierJS->visit_days ?? '') ?? []) ?>;
    let visit_weeks = <?= json_encode(array_map(fn ($val) => trim($val), explode(',', $supplierJS->visit_weeks ?? '')) ?? []) ?>;

    $('#visit_days').val(visit_days).trigger('change');
    $('#visit_weeks').val(visit_weeks).trigger('change');

    initModalForm({
      form: '#form',
      submit: '#submit',
      url: base_url + '/humanresource/supplier/edit/<?= $supplier->id ?>'
    });
  });
</script>