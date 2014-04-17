<?php

session_start();

function user_connected() {
  return isset($_SESSION['user']);
}

function user_is_admin() {
  return user_connected() && $_SESSION['admin'];
}

function set_user_session($user) {
  global $admins;

  $_SESSION['user'] = $user;
  $_SESSION['admin'] = in_array($user, $admins);
}

function user_login($user, $password) {
  global $passwords;

  foreach ($passwords as $passwordsUser => $passwordsPassword) {
    if($user == $passwordsUser && password_verify($password, $passwordsPassword)) {

      set_user_session($user);

      set_long_session($user);

      break;
    }
  }

  return user_connected();
}

function user_logout() {
  $_SESSION = array();
  $session = get_long_session();
  if($session) {
    $user = $session['user'];
    $session = $session['session'];
    $config = get_global_config();

    if(isset($config['longsessions'][$user])) {
      if(($key = array_search($session, $config['longsessions'][$user])) !== false) {
        unset($config['longsessions'][$user][$key]);
        set_global_config($config);
      }
    }

    setcookie('LONGSESSION', null, -1, '/');
  }
}

function get_long_session() {
  if(isset($_COOKIE['LONGSESSION'])) {
    $session = explode(':', $_COOKIE['LONGSESSION']);
    return array(
      'user' => $session[0],
      'session' => $session[1]
    );
  }

  return false;
}

function set_long_session($user) {
  $session = MD5(microtime());

  $config = get_global_config();
  if(!isset($config['longsessions'][$user])) {
    $config['longsessions'][$user] = array();
  }

  $config['longsessions'][$user] []= $session;
  set_global_config($config);

  setcookie('LONGSESSION', $user . ':' . $session, time() + 3600 * 24 * 365, '/');
}

function auto_login() {
  global $passwords;

  if(!user_connected()) {
    $session = get_long_session();
    if($session) {
      $user = $session['user'];
      $session = $session['session'];

      foreach ($passwords as $passwordsUser => $passwordsPassword) {
        if($user == $passwordsUser) {

          $config = get_global_config();

          if(isset($config['longsessions'][$user])) {
            if(in_array($session, $config['longsessions'][$user])) {
              set_user_session($user);
            }
          }

          break;
        }
      }

    }
  }

  return user_connected();
}