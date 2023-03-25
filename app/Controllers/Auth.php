<?php

declare(strict_types=1);

namespace App\Controllers;

use \App\Models\Auth as Authentication;

class Auth extends BaseController
{
  public function index()
  {
    checkPermission();
  }

  public function login()
  {
    if (requestMethod() == 'POST' && isAJAX()) {
      $id       = getPost('id');
      $pass     = getPost('pass');
      $remember = (getPost('remember') == 1 ? TRUE : FALSE);

      if (session()->has('logfail')) {
        if (session('logfail')['count'] > 3) {
          if (strtotime('+5 minute', session('logfail')['time']) > time()) {
            $this->response(403, ['message' => 'Please wait for 5 minutes to login again.']);
          } else {
            session()->remove('logfail');
          }
        }
      }

      if (Authentication::login($id, $pass, $remember)) {
        $this->response(200, ['message' => 'Login success.']);
      }

      if (session()->has('logfail')) {
        session()->set('logfail', [
          'count' => session('logfail')['count'] + 1,
          'time'  => time()
        ]);
      } else {
        session()->set('logfail', ['count' => 1, 'time' => time()]);
      }

      $this->response(401, ['message' => (isEnv('development') ? getLastError() : 'Login failed.')]);
    } else {
      if (isLoggedIn()) return redirect()->to(base_url());

      // Cookie name ___ is remember login cookie.
      if (!isLoggedIn() && getCookie('___')) {
        Authentication::loginRememberMe(getCookie('___'));
      }

      echo view('Auth/login', $this->data);
    }
  }

  public function logout()
  {
    if (Authentication::logout()) {
      if (isAJAX()) {
        $this->response(200, ['message' => 'Logout success']);
      }

      return redirect()->to('auth/login');
    } else {
      if (isAJAX()) {
        $this->response(200, ['message' => 'Logout success']);
      }

      return redirect()->to('auth/login');
    }
  }

  public function status()
  {
    if (isLoggedIn()) {
      $this->response(200, ['message' => 'Logged in']);
    } else {
      $this->response(403, ['message' => 'Not logged in']);
    }
  }
}
