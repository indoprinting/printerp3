<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title overflow-hidden"><i class="fad fa-fw fa-file"></i>
    <?= $attachment->filename . ' (' . $attachment->mime . ')' ?>
  </h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <?php if (in_array($attachment->mime, ['image/jpg', 'image/jpeg', 'image/png'])) : ?>
    <img src="<?= base_url('attachment/' . $attachment->hashname) ?>" style="width:100%">
  <?php elseif (in_array($attachment->mime, ['application/pdf'])) : ?>
    <embed src="<?= base_url('attachment/' . $attachment->hashname) ?>" height="540" width="1024" />
  <?php endif; ?>
</div>
<div class="modal-footer">
  <a href="<?= base_url('attachment/' . $attachment->hashname) ?>?d=1" class="btn bg-gradient-success"><?= lang('App.download') ?></a>
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><?= lang('App.close') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
</script>