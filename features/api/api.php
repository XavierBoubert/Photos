<?php

ini_set('memory_limit', '1024M');
set_time_limit(90);

global $pathurl;

$result = array();

if(count($pathurl) == 1) {
  $result = array(
    'api' => 'photos',
    'version' => '1.0'
  );
  echo json_encode($result);
  exit;
}

$command = $pathurl[1];

switch($command) {
  case 'login':

    $result['success'] = false;

    if(user_connected()) {
      $result['error'] = 'Vous êtes déja connecté';
    }
    else if(!isset($_POST['login']) || $_POST['login'] === '') {
      $result['error'] = 'Il vous faut entrer un identifiant';
    }
    else if(!isset($_POST['password']) || $_POST['password'] === '') {
      $result['error'] = 'Il vous faut entrer un mot de passe';
    }
    else if(user_login($_POST['login'], $_POST['password'])) {
      $result['success'] = true;
    }
    else {
      $result['error'] = 'Ces identifiant et mot de passe ne correspondent pas aux utilisateurs enregistrés.';
    }

    break;

  case 'logout':

    user_logout();

    $result['success'] = true;

    break;

  case 'passwords':

    if(!isset($_POST['password']) || $_POST['password'] === '') {
      $result['error'] = 'Il vous faut entrer un mot de passe';
    }
    else {
      $result['success'] = true;
      $result['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    break;

  case 'photos-worker':

    if(!user_is_admin()) {

      $result['success'] = false;
      $result['error'] = 'Vous devez être administrateur du site';

    }
    else {

      $result['success'] = true;
      $result['status'] = 'idle';

      $firstTime = isset($_GET['firstTime']) && $_GET['firstTime'] == 'true';

      if($firstTime) {
        $globalConfig = get_global_config();
        $totalToMake = number_photos() + number_videos();

        $result['number_photos'] = number_photos();
        $result['number_videos'] = number_videos();

        if($globalConfig['number_photos'] + $globalConfig['number_videos'] < $totalToMake) {
          $result['status'] = 'working';

          $globalConfig = get_global_config();

          $result['work'] = array(
            'number_photos' => $globalConfig['number_photos'],
            'number_videos' => $globalConfig['number_videos'],
            'number_photos_visible' => $globalConfig['number_photos_visible'],
            'number_videos_visible' => $globalConfig['number_videos_visible'],
            'number_albums_visible' => $globalConfig['number_albums_visible'],
            'last_update' => strftime('%e %B %Y', $globalConfig['last_update']),
            'total_to_make' => $totalToMake
          );
        }

      }
      else if($file = make_next_thumb()) {
        $result['status'] = 'working';

        $path = $file['path'];
        $file = $file['file'];
        $photosUrl = PHOTOS_URL . ($path !== '' ? $path . '/' : '/');
        $cachePath = CACHE_PATH . ($path !== '' ? $path . '/' : '/');
        $cacheUrl = CACHE_URL . ($path !== '' ? $path . '/' : '/');
        $sizes = photos_sizes();

        if($path == $_GET['path']) {
          $config = get_config_file($cachePath . '/config');

          foreach($config['items'] as $item) {
            if($item['name'] == $file) {
              if($item['visible']) {

                $item['fileurl'] = $photosUrl . $item['name'];
                $item['sizes'] = array();
                $names = thumbs_names($item['name']);
                for($j = 0; $j < count($sizes); $j++) {
                  $item['sizes'][$sizes[$j]] = $cacheUrl . $names[$j];
                }

                $result['newFile'] = $item;
              }
              break;
            }
          }

        }
        else if(get_parent_url($path) == $_GET['path']) {
          $config = get_config_file($cachePath . '/config');
          if($config['visible']) {

            $pos = strrpos('/', $path);
            $name = substr($path, $pos + 1, strlen($path) - $pos + 1);

            $date = explode(' ', str_replace(array('photos ', '.'), array('', '/'), strtolower($name)));
            $date = $date[0];
            if(count(explode('/', $date)) == 3) {
              $date = strtotime($date);
            }
            else {
              $date = intval($date);
            }

            $folderUrl = array();
            $folderUrlArray = explode('/', str_replace(CACHE_PATH . '/', '', $cachePath));
            foreach($folderUrlArray as $url) {
              $folderUrl []= photos_folder_name_to_url($url);
            }
            $folderUrl = implode('/', $folderUrl);

            $poster = array();
            $names = thumbs_names('folder.jpg');
            for($j = 0; $j < count($sizes); $j++) {
              $poster[$sizes[$j]] = $cacheUrl . '/' . $names[$j];
            }

            $result['newAlbum'] = array(
              'name' => $name,
              'title' => photos_folder_name_to_title($name),
              'description' => $config['description'],
              'url' => '/' . $folderUrl,
              'date' => $date,
              'poster' => $poster,
              'number_photos' => $config['number_photos_visible'],
              'number_videos' => $config['number_videos_visible'],
              'tags' => array_keys($config['tags']),
              'identities' => array_keys($config['identities'])
            );
          }
        }

        $globalConfig = get_global_config();

        $result['work'] = array(
          'number_photos' => $globalConfig['number_photos'],
          'number_videos' => $globalConfig['number_videos'],
          'number_photos_visible' => $globalConfig['number_photos_visible'],
          'number_videos_visible' => $globalConfig['number_videos_visible'],
          'number_albums_visible' => $globalConfig['number_albums_visible'],
          'last_update' => strftime('%e %B %Y', $globalConfig['last_update']),
          'total_to_make' => number_photos() + number_videos()
        );
      }
    }

    break;

  case 'role-admin':

    if(!user_is_admin()) {

      $result['success'] = false;
      $result['error'] = 'Vous devez être administrateur du site';

    }
    else {

      make_path_url($_GET['path']);

      $result['success'] = true;
      $result['invisibles'] = photos_elements(true);

    }

    break;

  case 'item-remove-featured':

    if(!user_is_admin()) {

      $result['success'] = false;
      $result['error'] = 'Vous devez être administrateur du site';

    }
    else {

      $name = $_GET['name'];
      $type = $_GET['type'];
      $path = $type == 'album' ? $_GET['url'] : $_GET['path'];
      $path = substr($path, strlen($path) - 1, 1) == '/' ? substr($path, 0, strlen($path) - 1) : $path;

      $file = $path . ($type != 'album' ? '/' . $name : '');

      make_path_url($path);

      global $photospath;
      $configPath = str_replace(PHOTOS_PATH, CACHE_PATH, $photospath);

      $config = get_config_file($configPath . '/config');

      if($type == 'album') {
        $config['featured'] = false;
      }
      else {
        for($i = 0; $i < count($config['items']); $i++) {
          if($config['items'][$i]['name'] == $name) {
            $config['items'][$i]['featured'] = false;
            break;
          }
        }
      }

      set_config_file($configPath . '/config', $config);

      $globalConfig = get_global_config();

      if(isset($globalConfig['featured'][$file])) {

        unset($globalConfig['featured'][$file]);

        set_global_config($globalConfig);
      }

      $result['success'] = true;

    }

    break;

  case 'item-featured':

    if(!user_is_admin()) {

      $result['success'] = false;
      $result['error'] = 'Vous devez être administrateur du site';

    }
    else {

      $globalConfig = get_global_config();
      $name = $_GET['name'];
      $type = $_GET['type'];
      $path = $type == 'album' ? $_GET['url'] : $_GET['path'];
      $path = substr($path, strlen($path) - 1, 1) == '/' ? substr($path, 0, strlen($path) - 1) : $path;

      $file = $path . ($type != 'album' ? '/' . $name : '');

      if(!isset($globalConfig['featured'][$file])) {

        $url = $file;
        $picture = '';

        make_path_url($path);

        global $photospath;
        $configPath = str_replace(PHOTOS_PATH, CACHE_PATH, $photospath);
        $configUrl = str_replace(PHOTOS_PATH, CACHE_URL, $photospath);

        $config = get_config_file($configPath . '/config');

        if($type == 'album') {
          $picture = $configUrl . '/folder_1920x1080.jpg';

          $config['featured'] = true;
        }
        else {
          $url = $path . '#' . $_GET['urlindex'];
          $names = thumbs_names($name);

          $picture = $configUrl . '/' . $names[1];

          for($i = 0; $i < count($config['items']); $i++) {
            if($config['items'][$i]['name'] == $name) {
              $config['items'][$i]['featured'] = true;
              break;
            }
          }
        }

        set_config_file($configPath . '/config', $config);

        $globalConfig['featured'][$file] = array(
          'url' => $url,
          'picture' => $picture,
          'title' => $_GET['title'],
          'description' => $_GET['description']
        );

        set_global_config($globalConfig);

      }

      $result['success'] = true;

    }

    break;

  case 'item-show':
  case 'item-hide':

    if(!user_is_admin()) {

      $result['success'] = false;
      $result['error'] = 'Vous devez être administrateur du site';

    }
    else {

      $visible = $command == 'item-show';
      $name = $_GET['name'];
      $url = $_GET['url'];
      $type = $_GET['type'];
      $path = $type == 'album' ? $_GET['url'] : $_GET['path'];

      $counts = array(
        'albums' => 0,
        'photos' => 0,
        'videos' => 0
      );

      make_path_url($path);

      global $photospath;
      $path = str_replace(PHOTOS_PATH, CACHE_PATH, $photospath);

      $config = get_config_file($path . '/config');

      if($type == 'album') {
        $config['visible'] = $visible;

        $counts['albums']++;
        $counts['photos'] += $config['number_photos_visible'];
        $counts['videos'] += $config['number_videos_visible'];
      }
      else {
        for($i = 0; $i < count($config['items']); $i++) {
          if($config['items'][$i]['name'] == $name) {
            $config['items'][$i]['visible'] = $visible;

            $type = $config['items'][$i]['type'];

            $counts[$type . 's'] += 1;

            break;
          }
        }
      }

      set_config_file($path . '/config', $config);

      if(!$visible) {
        $counts['albums'] = -$counts['albums'];
        $counts['photos'] = -$counts['photos'];
        $counts['videos'] = -$counts['videos'];
      }

      $globalConfig = get_global_config();

      $globalConfig['number_albums_visible'] += $counts['albums'];
      $globalConfig['number_photos_visible'] += $counts['photos'];
      $globalConfig['number_videos_visible'] += $counts['videos'];

      set_global_config($globalConfig);

      $result['success'] = true;

    }

    break;

  case 'item-remake':

    if(!user_is_admin()) {

      $result['success'] = false;
      $result['error'] = 'Vous devez être administrateur du site';

    }
    else {

      delete_in_cache($_GET['path'], $_GET['name'], $_GET['type']);

      $result['success'] = true;

    }

    break;

  case 'item-make-logo':
  case 'item-make-banner':

    if(!user_is_admin()) {

      $result['success'] = false;
      $result['error'] = 'Vous devez être administrateur du site';

    }
    else {

      $parent = $_GET['parent'] == 'true';

      if($command == 'item-make-logo') {
        $result['src'] = make_logo($_GET['type'], $_GET['path'], $_GET['name'], $parent);
      }
      else if($command == 'item-make-banner') {
        $result['src'] = make_banner($_GET['type'], $_GET['path'], $_GET['name'], $parent);
      }

      $result['success'] = true;
    }

    break;

  case 'item-tags':
  case 'item-identities':

    if(!user_is_admin()) {

      $result['success'] = false;
      $result['error'] = 'Vous devez être administrateur du site';

    }
    else {

      if($command == 'item-tags') {
        $result['tags'] = set_tags($_GET['path'], $_GET['name'], $_GET['value']);
      }
      else if($command == 'item-identities') {
        $result['identities'] = set_identities($_GET['path'], $_GET['name'], $_GET['value']);
      }

      $result['success'] = true;
    }

    break;

  default:
    $result = array(
      'success' => false,
      'error' => 'Cette commande n\'existe pas'
    );

    break;
}

echo json_encode($result);