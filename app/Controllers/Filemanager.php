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

<<<<<<< HEAD
    $this->data['attachment'] = Attachment::getRow(['hashname' => $hashname]);
=======
    $attachment = Attachment::select('*')->where('hashname', $hashname)->orWhere('id', $hashname)->getRow();

    if (!$attachment) {
      $this->response(404, ['message' => 'Attachment is not found.']);
    }

    $this->data['attachment'] = $attachment;
>>>>>>> 1ae6785e697272c1e35ec80607179c1cf3a00170
    $this->response(200, ['content' => view('FileManager/view', $this->data)]);
  }
}
