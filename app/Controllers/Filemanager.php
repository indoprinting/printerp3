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

    $this->data['attachment'] = Attachment::getRow(['hashname' => $hashname]);
    $this->response(200, ['content' => view('FileManager/view', $this->data)]);
  }
}
