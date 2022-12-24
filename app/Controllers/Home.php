<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\{Attachment, Locale, User};

class Home extends BaseController
{
  public function index()
  {
    checkPermission();

    $this->data['page'] = [
      'bc' => [
        ['name' => lang('App.dashboard'), 'slug' => 'dashboard', 'url' => '#']
      ],
      'content' => 'dashboard',
      'title' => lang('App.dashboard')
    ];

    return $this->buildPage($this->data);
  }

  public function attachment($hashName = NULL)
  {
    $download = getGet('d');
    $attachment = Attachment::getRow(['hashname' => $hashName]);
    
    if ($attachment) {
      if ($download == 1) {
        header("Content-Disposition: attachment; filename=\"{$attachment->filename}\"");
      }

      $cacheLifeTime = 86400;

      header('Cache-Control: max-age=' . $cacheLifeTime);
      header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cacheLifeTime) . ' GMT');
      header('Content-Type: ' . $attachment->mime);
      header('Content-Size: ' . $attachment->size);

      die($attachment->data);
    } else {
      $this->response(404, ['message' => 'File not found.']);
    }
  }

  public function lang($localeCode = 'id')
  {
    checkPermission();

    foreach (Locale::get() as $locale) {
      if ($locale->code == $localeCode) {
        $login = session('login');

        User::update((int)$login->user_id, ['lang' => $locale->code]);
        $login->lang = $locale->code;

        session()->set('login', $login);

        $this->response(200, ['message' => 'Change language success', 'data' => $locale->code]);
      }
    }

    $this->response(404, ['message' => 'Language not found']);
  }

  public function mutasi()
  {
    $log = new \FileLogger(WRITEPATH . 'logs/mutasi-' . date('Y-m-d') . '.log');

    $log->write($this->request->getRawInput());

    echo "OK";
  }
}
