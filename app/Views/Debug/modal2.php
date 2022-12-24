<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-sync"></i> Modal 2</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <form id="form2">
    <div class="row">
      <div class="col-3">
        <a href="<?= base_url('debug/modal2') ?>" class="btn btn-default" data-toggle="modal" data-target="#ModalDefault2">
          Show default modal
        </a>
      </div>
      <div class="col-3">
        <a href="<?= base_url('debug/modal2') ?>" class="btn btn-default" data-toggle="modal" data-target="#ModalDefault2" data-modal-class="modal-sm">
          Show SM modal
        </a>
      </div>
      <div class="col-3">
        <a href="<?= base_url('debug/modal2') ?>" class="btn btn-default" data-toggle="modal" data-target="#ModalDefault2" data-modal-class="modal-lg">
          Show LG modal
        </a>
      </div>
      <div class="col-3">
        <a href="<?= base_url('debug/modal2') ?>" class="btn btn-default" data-toggle="modal" data-target="#ModalDefault2" data-modal-class="modal-xl">
          Show XL modal
        </a>
      </div>
      <div class="col-3">
        <a href="<?= base_url('debug/modal2') ?>" class="btn btn-default" data-toggle="modal" data-target="#ModalStatic">
          Show static modal
        </a>
      </div>
      <div class="col-3"></div>
    </div>
  </form>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('App.cancel') ?></button>
  <button type="button" id="submit2" class="btn bg-gradient-indigo"><?= lang('App.save') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();

  $(document).ready(function() {
    initModalForm({
      form: '#form2',
      submit: '#submit2',
      url: base_url + '/academics/educationlevel/add'
    });
  });
</script>