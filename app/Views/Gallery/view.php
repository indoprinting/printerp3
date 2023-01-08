<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-user-plus"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <?php if (in_array($attachment->mime, ['image/jpg', 'image/jpeg', 'image/png'])) : ?>
    <img src="<?= base_url('attachment/' . $attachment->hashname) ?>">
  <?php elseif (in_array($attachment->mime, ['application/pdf'])) : ?>
    <embed src="<?= base_url('attachment/' . $attachment->hashname) ?>" height="540" width="1024" />
  <?php endif; ?>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('App.close') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
</script>