<div class="modal-header bg-gradient-dark">
  <h5 class="modal-title"><i class="fad fa-fw fa-magnifying-glass"></i> <?= $title ?></h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-body">
          <form id="form">
            <?= csrf_field() ?>
            <table class="table table-hover table-sm table-striped">
              <tbody>
                <tr>
                  <td><?= lang('App.id') ?></td>
                  <td><?= $category->id ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.code') ?></td>
                  <td><?= $category->code ?></td>
                </tr>
                <tr>
                  <td><?= lang('App.name') ?></td>
                  <td><?= $category->name ?></td>
                </tr>
                <?php if ($parent) : ?>
                  <tr>
                    <td><?= lang('App.parentcategory') ?></td>
                    <td>(<?= $parent->code ?>) <?= $parent->name ?></td>
                  </tr>
                <?php endif; ?>
                <tr>
                  <td><?= lang('App.description') ?></td>
                  <td><?= $category->description ?></td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-footer">
  <button type="button" class="btn bg-gradient-danger" data-dismiss="modal"><i class="fad fa-fw fa-times"></i> <?= lang('App.cancel') ?></button>
</div>
<script>
  (function() {
    initControls();
  })();
</script>