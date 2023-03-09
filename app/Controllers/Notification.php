<?php

declare(strict_types=1);

namespace App\Controllers;

use \App\Libraries\{DataTables, FileUpload};
use App\Models\{Biller, DB, Notification as NotifyModel, User, UserGroup, Warehouse};

class Notification extends BaseController
{
  public function getNotifications()
  {
    checkPermission('Notification.View');

    $dt = new DataTables('notification');
    $dt
      ->select("notification.id AS id, notification.created_at, notification.title, notification.note,
      notification.scopes, notification.type, notification.status, creator.fullname AS creator_name")
      ->join('users creator', 'creator.id = notification.created_by', 'left')
      ->editColumn('id', function ($data) {
        return '
          <div class="btn-group btn-action">
            <a class="btn bg-gradient-primary btn-sm dropdown-toggle" href="#" data-toggle="dropdown">
              <i class="fad fa-gear"></i>
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="' . base_url('notification/edit/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-edit"></i> ' . lang('App.edit') . '
              </a>
              <a class="dropdown-item" href="' . base_url('notification/view/' . $data['id']) . '"
                data-toggle="modal" data-target="#ModalStatic"
                data-modal-class="modal-dialog-centered modal-dialog-scrollable">
                <i class="fad fa-fw fa-magnifying-glass"></i> ' . lang('App.view') . '
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="' . base_url('notification/delete/' . $data['id']) . '"
                data-action="confirm">
                <i class="fad fa-fw fa-trash"></i> ' . lang('App.delete') . '
              </a>
            </div>
          </div>';
      })
      ->editColumn('note', function ($data) {
        return "<div data-widget=\"tooltip\" title=\"{$data['note']}\">" . getExcerpt(html2Note($data['note'])) . '</div>';
      })
      ->editColumn('scopes', function ($data) {
        $scopes = getJSON($data['scopes']);
        $status = lang('App.all');
        $type   = 'navy';
        $res    = '';

        if (empty($scopes->billers) && empty($scopes->usergroups) && empty($scopes->users) && empty($scopes->warehouses)) {
          return "<div class=\"badge bg-gradient-{$type} m-1 p-2\">{$status}</div>";
        }

        if (!empty($scopes->billers)) {
          foreach ($scopes->billers as $biller) {
            $biller = Biller::getRow(['code' => $biller]);
            $status = lang('App.biller') . ': ' . $biller->name;
            $res .= "<div class=\"badge bg-gradient-orange m-1 p-2\">{$status}</div>";
          }
        }

        if (!empty($scopes->usergroups)) {
          foreach ($scopes->usergroups as $usergroup) {
            $status = lang('App.usergroup') . ': ' . $usergroup;
            $res .= "<div class=\"badge bg-gradient-indigo m-1 p-2\">{$status}</div>";
          }
        }

        if (!empty($scopes->users)) {
          foreach ($scopes->users as $user) {
            $user = User::getRow(['phone' => $user]);
            $status = lang('App.user') . ': ' . $user->fullname;
            $res .= "<div class=\"badge bg-gradient-info m-1 p-2\">{$status}</div>";
          }
        }

        if (!empty($scopes->warehouses)) {
          foreach ($scopes->warehouses as $warehouse) {
            $warehouse = Warehouse::getRow(['code' => $warehouse]);
            $status = lang('App.warehouse') . ': ' . $warehouse->name;
            $res .= "<div class=\"badge bg-gradient-green m-1 p-2\">{$status}</div>";
          }
        }

        return $res;
      })
      ->editColumn('type', function ($data) {
        $type = $data['type'];
        $status = lang('Status.' . $type);

        return "<div class=\"badge bg-gradient-{$type} p-2\">{$status}</div>";
      })
      ->editColumn('status', function ($data) {
        if ($data['status'] == 'pending') {
          $type = 'warning';
        } else if ($data['status'] == 'active') {
          $type = 'success';
        }

        $status = lang('Status.' . $data['status']);

        return "<div class=\"badge bg-gradient-{$type} p-2\">{$status}</div>";
      })
      ->generate();
  }

  public function add()
  {

    checkPermission('Notification.Add');

    if (requestMethod() == 'POST') {
      $data = [
        'title'   => getPost('title'),
        'note'    => stripTags(getPost('note')),
        'scopes'  => getPost('scope'),
        'type'    => getPost('type'),
        'status'  => getPost('status')
      ];

      if (empty($data['title'])) {
        $this->response(400, ['message' => 'Title is required.']);
      }

      if (empty($data['note'])) {
        $this->response(400, ['message' => 'Note is required.']);
      }

      DB::transStart();

      $insertID = NotifyModel::add($data);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Notification has been added.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['title'] = lang('App.addnotification');

    $this->response(200, ['content' => view('Notification/add', $this->data)]);
  }

  public function delete($id = null)
  {
    checkPermission('Notification.Delete');

    $notif = NotifyModel::getRow(['id' => $id]);

    if (!$notif) {
      $this->response(404, ['message' => 'Notification is not found.']);
    }

    DB::transStart();

    $res = NotifyModel::delete(['id' => $id]);

    if (!$res) {
      $this->response(400, ['message' => getLastError()]);
    }

    DB::transComplete();

    if (DB::transStatus()) {
      $this->response(200, ['message' => 'Notification has been deleted.']);
    }

    $this->response(400, ['message' => 'Failed to delete notification.']);
  }

  public function edit($id = null)
  {
    checkPermission('Notification.Edit');

    $notify = NotifyModel::getRow(['id' => $id]);

    if (!$notify) {
      $this->response(404, ['message' => 'Notification is not found.']);
    }

    if (requestMethod() == 'POST') {
      $data = [
        'title'   => getPost('title'),
        'note'    => stripTags(getPost('note')),
        'scopes'  => getPost('scope'),
        'type'    => getPost('type'),
        'status'  => getPost('status')
      ];

      if (empty($data['title'])) {
        $this->response(400, ['message' => 'Title is required.']);
      }

      if (empty($data['note'])) {
        $this->response(400, ['message' => 'Note is required.']);
      }

      DB::transStart();

      $insertID = NotifyModel::update((int)$id, $data);

      if (!$insertID) {
        $this->response(400, ['message' => getLastError()]);
      }

      DB::transComplete();

      if (DB::transStatus()) {
        $this->response(201, ['message' => 'Notification has been updated.']);
      }

      $this->response(400, ['message' => (isEnv('development') ? getLastError() : 'Failed')]);
    }

    $this->data['notification'] = $notify;
    $this->data['title'] = lang('App.editnotification');

    $this->response(200, ['content' => view('Notification/edit', $this->data)]);
  }

  public function index()
  {
    checkPermission('Notification.View');

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.notification'), 'slug' => 'notification', 'url' => '#']
      ],
      'content' => 'Notification/index',
      'title' => lang('App.notification')
    ];

    return $this->buildPage($this->data);
  }
}
