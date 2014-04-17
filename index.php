<?php

  define('ROOT_PATH', dirname(__FILE__));
  define('ROOT_URL', '/');
  define('CACHE_PATH', ROOT_PATH . '/cache');
  define('CACHE_URL', '/cache');

  date_default_timezone_set('Europe/Paris');
  setlocale(LC_TIME, 'fr_FR.utf8', 'fra');

  $pages = array('api', 'passwords');

  require_once 'features/common/common.php';

  if(file_exists('features/customize/customize.php')) {
    require_once 'features/customize/customize.php';
  }

  if(file_exists('features/customize/passwords.php')) {
    require_once 'features/customize/passwords.php';
  }

  require_once 'features/users/users.php';
  require_once 'features/photos/photos.php';

  make_path_url();

  global $pathurl;
  global $isRoot;
  global $photospath;
  global $cacheVars;
  global $siteConfig;

  $isRoot = false;
  $cacheVars = array();
  $siteConfig['page-title'] = $siteConfig['site-title'];
  $siteConfig['title'] = $siteConfig['site-title'];
  $siteConfig['body_css'] = 'page-default';

  $pathurlLen = count($pathurl);
  if($pathurlLen === 0) {
    $siteConfig['body_css'] = 'page-index';
  }

  auto_login();

  if($pathurlLen > 0) {
    $imageTypes = array('png', 'jpg', 'jpeg', 'gif', 'bmp');
    $filesNoLogin = array('cache/logo.jpg', 'cache/banner.jpg');
    $noPictureFile = 'features/photos/assets/access-denied.jpg';

    $ext = pathinfo($pathurl[$pathurlLen - 1], PATHINFO_EXTENSION);
    if($ext && in_array($ext, $imageTypes)) {

      $download = false;
      if($pathurl[0] == 'download') {
        $download = true;
        unset($pathurl[0]);
      }
      $path = urldecode(implode('/', $pathurl));

      if(file_exists($path)) {

        if(!user_connected() && !in_array($path, $filesNoLogin)) {
          $ext = 'jpg';
          $path = $noPictureFile;
        }

        $ctype = 'image/jpg';
        if($ext == 'gif' || $ext == 'png') {
          $ctype = 'image/' . $ext;
        }

        if($download) {
          header('Content-Description: File Transfer');
          header('Content-Type: application/octet-stream');
          header('Content-Disposition: attachment; filename="'.basename($path).'"');
          header('Content-Transfer-Encoding: binary');
          header('Connection: Keep-Alive');
          header('Expires: 0');
          header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
          header('Pragma: public');
          header('Content-Length: ' . filesize($path));
        }
        else {
          $time = 3600 * 24 * 14; // 14 days
          header('Content-type: ' . $ctype);
          header('Cache-Control: max-age=' . $time . ', must-revalidate');
          header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $time) . ' GMT');
        }

        readfile(ROOT_PATH . '/' . $path);

        exit;
      }
      else {
        $time = 3600 * 24 * 14; // 14 days
        header('Content-type: image/gif');
        header('Cache-Control: max-age=' . $time . ', must-revalidate');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $time) . ' GMT');

        readfile(ROOT_PATH . '/features/explorer/assets/images/s.gif');

        exit;
      }
    }
  }

  if($pathurlLen > 0 && in_array($pathurl[0], $pages)) {
    $feature = $pathurl[0];
    require_once 'features/'.$feature.'/'.$feature.'.php';
  }
  else if(!user_connected()) {
    require_once 'features/users/users-login-page.php';
  }
  else {

    if($pathurlLen > 0) {
      if(!$photospath) {
        header('Location: /');
        exit;
      }

      $photoscache = str_replace(PHOTOS_PATH, CACHE_PATH, $photospath);
      $config = get_config_file($photoscache . '/config');

      if(!$config || !$config['visible']) {
        header('Location: /');
        exit;
      }

      $title = explode('/', $photospath);
      $title = $title[count($title) - 1];
      $siteConfig['title'] = $title;
      $siteConfig['page-title'] = $title . ' - ' . $siteConfig['page-title'];
    }
    else {
      $isRoot = true;
    }

    if(!$photospath) {
      $photospath = PHOTOS_PATH;
    }

    require_once 'features/explorer/explorer-page.php';

  }