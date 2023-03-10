<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Attachment;

class Filemanager extends BaseController
{
  public function index()
  {
  }

  public function view($hashname = NULL)
  {
    if (!$hashname) {
      $this->response(400, ['message' => 'Hashname required.']);
    }

    $attachment = Attachment::select('*')->where('hashname', $hashname)->getRow();

    if (!$attachment) {
      $this->response(404, ['message' => 'Attachment is not found.']);
    }

    $this->data['attachment'] = $attachment;
    $this->response(200, ['content' => view('FileManager/view', $this->data)]);
  }
}
