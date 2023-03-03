<div class="container-fluid">
  <div class="row">
    <div class="col-md-3">
      <div class="card card-primary card-outline">
        <div class="card-body box-profile">
          <div class="text-center">
            <img class="profile-user-img img-fluid img-circle" src="<?= base_url('attachment') . "/{$user->avatar}" ?>" alt="User profile picture">
          </div>

          <h3 class="profile-username text-center"><?= $user->fullname ?></h3>

          <p class="text-muted text-center">
            <?= $user->username ?>
          </p>

          <ul class="list-group list-group-unbordered mb-3">
            <li class="list-group-item">
              <b><i class="fad fa-fw fa-users"></i> <?= lang('App.usergroup') ?></b>
              <span class="float-right">
                <?php foreach ($userGroups as $userGroup) : ?>
                  - <?= $userGroup->name ?><br>
                <?php endforeach; ?>
              </span>
            </li>
            <li class="list-group-item">
              <b><i class="fad fa-fw fa-phone"></i> <?= lang('App.phone') ?></b>
              <span class="float-right"><?= $user->phone ?></span>
            </li>
            <li class="list-group-item">
              <b><i class="fad fa-fw fa-warehouse"></i> <?= lang('App.biller') ?></b>
              <span class="float-right"><?= ($user->biller ? \App\Models\Biller::getRow(['code' => $user->biller])->name : '-') ?></span>
            </li>
            <li class="list-group-item">
              <b><i class="fad fa-fw fa-warehouse-alt"></i> <?= lang('App.warehouse') ?></b>
              <span class="float-right"><?= ($user->warehouse ? \App\Models\Warehouse::getRow(['code' => $user->warehouse])->name : '-') ?></span>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-md-9">
      <div class="card">
        <div class="card-header p-2">
          <ul class="nav nav-pills">
            <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab"><?= lang('App.activity') ?></a></li>
            <li class="nav-item"><a class="nav-link" href="#profile" data-toggle="tab"><?= lang('App.profile') ?></a></li>
            <li class="nav-item"><a class="nav-link" href="#security" data-toggle="tab"><?= lang('App.security') ?></a></li>
          </ul>
          <div class="card-body">
            <div class="tab-content">
              <div class="tab-pane active" id="activity">
                <div class="timeline timeline-inverse">
                  <div class="time-label">
                    <span class="bg-danger">
                      10 Feb. 2014
                    </span>
                  </div>
                  <div>
                    <i class="fas fa-envelope bg-primary"></i>

                    <div class="timeline-item">
                      <span class="time"><i class="far fa-clock"></i> 12:05</span>

                      <h3 class="timeline-header"><a href="#">Support Team</a> sent you an email</h3>

                      <div class="timeline-body">
                        Etsy doostang zoodles disqus groupon greplin oooj voxy zoodles,
                        weebly ning heekya handango imeem plugg dopplr jibjab, movity
                        jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo kaboodle
                        quora plaxo ideeli hulu weebly balihoo...
                      </div>
                      <div class="timeline-footer">
                        <a href="#" class="btn btn-primary btn-sm">Read more</a>
                        <a href="#" class="btn btn-danger btn-sm">Delete</a>
                      </div>
                    </div>
                  </div>
                  <div>
                    <i class="fas fa-user bg-info"></i>

                    <div class="timeline-item">
                      <span class="time"><i class="far fa-clock"></i> 5 mins ago</span>

                      <h3 class="timeline-header border-0"><a href="#">Sarah Young</a> accepted your friend request
                      </h3>
                    </div>
                  </div>
                  <div>
                    <i class="fas fa-comments bg-warning"></i>

                    <div class="timeline-item">
                      <span class="time"><i class="far fa-clock"></i> 27 mins ago</span>

                      <h3 class="timeline-header"><a href="#">Jay White</a> commented on your post</h3>

                      <div class="timeline-body">
                        Take me to your leader!
                        Switzerland is small and neutral!
                        We are more like Germany, ambitious and misunderstood!
                      </div>
                      <div class="timeline-footer">
                        <a href="#" class="btn btn-warning btn-flat btn-sm">View comment</a>
                      </div>
                    </div>
                  </div>
                  <div class="time-label">
                    <span class="bg-success">
                      3 Jan. 2014
                    </span>
                  </div>
                  <div>
                    <i class="fas fa-camera bg-purple"></i>

                    <div class="timeline-item">
                      <span class="time"><i class="far fa-clock"></i> 2 days ago</span>

                      <h3 class="timeline-header"><a href="#">Mina Lee</a> uploaded new photos</h3>

                      <div class="timeline-body">
                        <img src="https://placehold.co/150x100.png" alt="...">
                        <img src="https://placehold.co/150x100.png" alt="...">
                        <img src="https://placehold.co/150x100.png" alt="...">
                        <img src="https://placehold.co/150x100.png" alt="...">
                      </div>
                    </div>
                  </div>
                  <div>
                    <i class="far fa-clock bg-gray"></i>
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="profile">
                <form method="post" enctype="multipart/form-data" id="form-profile">
                  <?= csrf_field() ?>
                  <div class="form-group row">
                    <label for="username" class="col-sm-2 col-form-label"><?= lang('App.username') ?></label>
                    <div class="col-sm-10">
                      <input type="text" id="username" name="username" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.username') ?>">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="fullname" class="col-sm-2 col-form-label"><?= lang('App.fullname') ?></label>
                    <div class="col-sm-10">
                      <input type="text" id="fullname" name="fullname" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.fullname') ?>">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="phone" class="col-sm-2 col-form-label"><?= lang('App.phone') ?></label>
                    <div class="col-sm-10">
                      <input type="text" id="phone" name="phone" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.phone') ?>">
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="gender" class="col-sm-2 col-form-label"><?= lang('App.gender') ?></label>
                    <div class="col-sm-10">
                      <select id="gender" name="gender" class="select" style="width:100%">
                        <option value="male"><?= lang('App.male') ?></option>
                        <option value="female"><?= lang('App.female') ?></option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="division" class="col-sm-2 col-form-label"><?= lang('App.division') ?></label>
                    <div class="col-sm-10">
                      <input type="division" id="division" name="division" class="form-control form-control-border form-control-sm" placeholder="<?= lang('App.division') ?>">
                    </div>
                  </div>
                  <div class="form-group row">
                    <div class="offset-sm-2 col-sm-10">
                      <button type="button" id="submit-profile" class="btn bg-gradient-primary"><i class="fad fa-fw fa-floppy-disk"></i> <?= lang('App.save') ?></button>
                    </div>
                  </div>
                </form>
              </div>
              <div class="tab-pane" id="security">
                <form method="post" enctype="multipart/form-data" id="form-security">
                  <?= csrf_field() ?>
                  <div class="card">
                    <div class="card-header bg-gradient-dark">
                      <i class="fad fa-fw fa-key"></i> <?= lang('App.changepassword') ?>
                    </div>
                    <div class="card-body">
                      <div class="form-group row">
                        <label for="currentpass" class="col-sm-2 col-form-label"><?= lang('App.currentpassword') ?></label>
                        <div class="col-sm-10">
                          <div class="input-group input-group-sm">
                            <input type="password" name="currentpass" id="currentpass" class="form-control form-control-border pass" placeholder="<?= lang('App.currentpassword') ?>" required>
                            <div class="input-group-append">
                              <span class="input-group-text bg-gradient-warning">
                                <i class="fad fa-fw fa-eye-slash show-pass"></i>
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="password" class="col-sm-2 col-form-label"><?= lang('App.newpassword') ?></label>
                        <div class="col-sm-10">
                          <div class="input-group input-group-sm">
                            <input type="password" name="password" id="password" class="form-control form-control-border pass" placeholder="<?= lang('App.newpassword') ?>" autocomplete="new-password" required>
                            <div class="input-group-append">
                              <span class="input-group-text bg-gradient-warning">
                                <i class="fad fa-fw fa-eye-slash show-pass"></i>
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="form-group row">
                        <div class="offset-sm-2 col-sm-10">
                          <button type="button" id="submit-security" class="btn bg-gradient-primary"><i class="fad fa-fw fa-floppy-disk"></i> <?= lang('App.save') ?></button>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  (function() {
    initControls();
  })();
</script>
<script>
  $(document).ready(function() {
    $('#username').val('<?= $user->username ?>');
    $('#fullname').val('<?= $user->fullname ?>');
    $('#phone').val('<?= $user->phone ?>');
    $('#gender').val('<?= $user->gender ?>').trigger('change');
    $('#division').val('<?= $user->company ?>');

    initModalForm({
      form: '#form-profile',
      submit: '#submit-profile',
      url: base_url + '/profile'
    });

    initModalForm({
      form: '#form-security',
      submit: '#submit-security',
      url: base_url + '/profile/security'
    });
  });
</script>