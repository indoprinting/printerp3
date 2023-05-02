<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Jobs;

class Service extends BaseController
{
  public function index()
  {
    $argv = func_get_args();

    return json_encode(['message' => 'Success', 'params' => $argv]);
  }

  public function main()
  {
    while (ob_get_level()) ob_end_clean();

    echo "\033[1;93mPrintERP Job\033[0m service is \033[1;92mrunning\033[0m.\r\n";

    while (true) {
      $jobs = Jobs::get(['status' => 'pending']);

      if (!$jobs) {
        sleep(5);
        continue;
      }

      foreach ($jobs as $job) {
        try {
          echo "\033[1;93mPROCESSING\033[0m: \033[1;95m{$job->class}\033[0m({$job->param})\r\n";
          // Class method must be static to be call.
          $res = call_user_func_array($job->class, [$job->param]);

          if ($res) {
            if ($job->callback) { // If has callback.
              call_user_func_array($job->callback, [$job, $res]);
            }

            echo "\033[1;92mSUCCESS\033[0m: \033[1;95m{$job->class}\033[0m({$job->param})\r\n";
            Jobs::update((int)$job->id, ['response' => $res, 'status' => 'success']);
          } else {
            echo "\033[1;91mFAILED\033[0m: \033[1;95m{$job->class}\033[0m({$job->param})\r\n";
            Jobs::update((int)$job->id, ['response' => null, 'status' => 'failed']);
          }
        } catch (\Exception $e) {
          echo "[\033[1;94mEXCEPTION\033[0m]: Message: {$e->getMessage()}. File: {$e->getFile()}. Line: {$e->getLine()}\r\n";
          Jobs::update((int)$job->id, ['response' => null, 'status' => 'failed']);
        }
      }

      sleep(5);
    }
  }
}
