<?php

global $siteConfig;

$photoPath = ROOT_PATH . '/photos';

if(isset($siteConfig['photos-path']) && strlen($siteConfig['photos-path']) > 0) {
  if(substr($siteConfig['photos-path'], 0, 1) == '/') {
    $photoPath = $siteConfig['photos-path'];
  }
  else {
    $photoPath = ROOT_PATH . '/' . $siteConfig['photos-path'];
  }
}

define('PHOTOS_PATH', $photoPath);
define('PHOTOS_URL', '/photos');
define('PHOTOS_RESERVED', 'config.conf,banner.jpg,folder_300x300.jpg,logo.jpg');
define('PHOTOS_SIZES', '300x300,1920x1080');
define('PHOTOS_CROP', '300x300,2048x1000');
define('PHOTOS_LOGO_SIZE', '300x300');
define('PHOTOS_BANNER_SIZE', '2048x1000');

DEFINE('IPTC_KEYWORDS', '025');
DEFINE('IPTC_CAPTION', '120');

function photos_reserved() {
  return explode(',', PHOTOS_RESERVED);
}

function photos_sizes() {
  return explode(',', PHOTOS_SIZES);
}

function photos_crop() {
  return explode(',', PHOTOS_CROP);
}

// "Photos 2000.02.01 Toto en voyage" -> "01.02.2000-toto-en-voyage"
function photos_folder_name_to_url($folderName) {
  $folderName = strtolower($folderName);
  $folderName = trim(str_replace('photos ', '', $folderName));
  $folderDate = explode(' ', $folderName);
  $folderDate = $folderDate[0];
  $date = explode('.', $folderDate);
  if(count($date) == 3) {
    $date = $date[2] . '.' . $date[1] . '.' . $date[0];
    $folderName = str_replace($folderDate, $date, $folderName);
  }
  $folderName = str_replace(' ', '-', $folderName);
  return $folderName;
}


// "Photos 2000.02.01 Toto en voyage" -> array('title' => 'Toto en voyage', folder' => Photos 2000.02.01')
function photos_folder_name_to_title($folderName) {
  $title = array(
    'title' => '',
    'folder' => ''
  );

  $titleStart = -1;
  $folderNameArray = explode(' ', $folderName);
  for ($i = 0; $i < count($folderNameArray); $i++) {
    if(substr_count($folderNameArray[$i], '.') == 2) {
      $titleStart = $i;
      break;
    }
  }
  if($titleStart === -1 || $titleStart == count($folderNameArray) - 1) {
    $title['folder'] = $folderName;
  }
  else {
    $title['folder'] = implode(' ', array_slice($folderNameArray, 0, $titleStart + 1));
    $title['title'] = implode(' ', array_slice($folderNameArray, $titleStart + 1));
  }

  $title['folder'] = str_replace('photos ', '', strtolower($title['folder']));
  $date = explode('.', $title['folder']);
  if(count($date) == 3) {
    $title['folder'] = strftime('%e %B %Y', strtotime(str_replace('.', '/', $title['folder'])));
  }

  return $title;
}

function photos_path($pathUrl) {
  $path = PHOTOS_PATH;

  foreach($pathUrl as $folderUrl) {
    $found = false;

    if($handle = opendir($path)) {

      while(($folderDir = readdir($handle)) !== false) {
        if($folderDir != '.' && $folderDir != '..') {
          $folder = photos_folder_name_to_url($folderDir);
          if($folder == $folderUrl) {
            $found = true;
            $path .= '/' . $folderDir;
            break;
          }
        }
      }

      closedir($handle);
    }

    if(!$found) {
      $path = false;
      break;
    }
  }

  return $path;
}

function make_cache_folder($path) {
  $path = CACHE_PATH . $path;

  if(!is_dir($path)) {
    mkdir($path, 0775, true);

    $globalConfig = get_global_config();
    $globalConfig['number_albums']++;
    $globalConfig['number_albums_visible']++;
    set_global_config($globalConfig);
  }

  if(!is_file($path . '/config')) {
    $config = get_config_file($path . '/config');
    set_config_file($path . '/config', $config);
  }
}

function find_file_in_cache($cachePath, $fileName, $makeCacheFolder = false) {
  if($makeCacheFolder) {
    make_cache_folder($cachePath);
  }

  $cacheUrl = CACHE_URL . $cachePath;
  $cachePath = CACHE_PATH . $cachePath;

  if(!is_array($fileName)) {
    $fileName = array($fileName);
  }

  $found = true;
  $path = '';
  foreach($fileName as $file) {
    if(is_file($cachePath . '/' . $file)) {
      if($path === '') {
        $path = $cacheUrl . '/' . $file;
      }
    }
    else {
      $found = false;
    }
  }

  if($found) {
    return $path;
  }

  return false;
}

function photos_banner() {
  global $cacheVars;

  if(isset($cacheVars['photos-banner'])) {
    return $cacheVars['photos-banner'];
  }

  global $photospath;

  $cachePath = str_replace(PHOTOS_PATH, '', $photospath);
  $url = find_file_in_cache($cachePath, 'banner.jpg', true);

  if(!$url) {
    $url = CACHE_URL . '/banner.jpg';
  }

   $cacheVars['photos-banner'] = $url;

  return $url;
}

function photos_logo() {
  global $cacheVars;

  if(isset($cacheVars['photos-logo'])) {
    return $cacheVars['photos-logo'];
  }

  global $photospath;

  $cachePath = str_replace(PHOTOS_PATH, '', $photospath);
  $url = find_file_in_cache($cachePath, 'logo.jpg', true);

  if(!$url) {
    $url = CACHE_URL . '/logo.jpg';
  }

   $cacheVars['photos-logo'] = $url;

  return $url;
}

function number_albums() {
  global $cacheVars;

  if(isset($cacheVars['number-albums'])) {
    return $cacheVars['number-albums'];
  }

  $number = 0;
  $directory = new DirectoryIterator(PHOTOS_PATH);
  $iterator = new IteratorIterator($directory);

  foreach($iterator as $fileinfo) {
    if($fileinfo->isDir() && $fileinfo->getFilename() != '..') {
      $number++;
    }
  }

  $cacheVars['number-albums'] = $number;

  return $number;
}

function count_items($extensions, $path = false) {
  if(!$path) {
    $path = PHOTOS_PATH;
  }

  $number = 0;
  $directory = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
  $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);

  foreach($iterator as $fileinfo) {
    if(in_array(strtolower($fileinfo->getExtension()), $extensions)) {
      $number++;
    }
  }

  return $number;
}

function number_photos($path = false) {
  global $cacheVars;

  if(!$path && isset($cacheVars['number-photos'])) {
    return $cacheVars['number-photos'];
  }

  $number = count_items(array('png', 'jpg', 'jpeg', 'gif'), $path);

  if(!$path) {
    $cacheVars['number-photos'] = $number;
  }

  return $number;
}

function number_videos($path = false) {
  global $cacheVars;

  if(!$path && isset($cacheVars['number-videos'])) {
    return $cacheVars['number-videos'];
  }

  $number = count_items(array('mp4'), $path);

  if(!$path) {
    $cacheVars['number-videos'] = $number;
  }

  return $number;
}

function thumbs_names($photoName) {
  $photoName = strtolower($photoName);
  $sizes = photos_sizes();
  $names = array();
  foreach($sizes as $size) {
    $names []= substr($photoName, 0, strrpos($photoName, '.')) . '_' . $size . '.jpg';
  }
  return $names;
}

function remove_path($path) {
  $config = get_config_file($path . '/config');
  $result = array(
    'number_albums' => 1,
    'number_photos' => $config['number_photos'],
    'number_videos' => $config['number_videos'],
    'number_albums_visible' => $config['visible'] ? 1 : 0,
    'number_photos_visible' => $config['number_photos_visible'],
    'number_videos_visible' => $config['number_videos_visible']
  );

  $files = array_diff(scandir($path), array('.', '..'));
  foreach($files as $file) {
    if(is_dir($path . '/' . $file)) {
      $mergeResult = remove_path($path . '/' . $file);
      $result['number_albums'] += $mergeResult['number_albums'];
      $result['number_photos'] += $mergeResult['number_photos'];
      $result['number_videos'] += $mergeResult['number_videos'];
      $result['number_albums_visible'] += $mergeResult['number_albums_visible'];
      $result['number_photos_visible'] += $mergeResult['number_photos_visible'];
      $result['number_videos_visible'] += $mergeResult['number_videos_visible'];
    }
    else {
      @unlink($path . '/' . $file);
    }
  }
  rmdir($path);

  return $result;
}

function newer_first($a, $b) {
  return $b->getPathname() > $a->getPathname();
}

function make_next_thumb() {
  $extensions = array('png', 'jpg', 'jpeg', 'gif', 'mp4');
  $videosExtensions = array('mp4');

  $sizes = photos_sizes();

  $haveWorked = false;

  $directory = new RecursiveDirectoryIterator(PHOTOS_PATH, RecursiveDirectoryIterator::SKIP_DOTS);
  $it = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);
  $iterator = new SortingIterator($it, 'newer_first');

  foreach($iterator as $fileinfo) {
    $ext = strtolower($fileinfo->getExtension());

    if(in_array($ext, $extensions)) {

      $path = $fileinfo->getPath();

      $cachePath = str_replace(PHOTOS_PATH, '', $fileinfo->getPath());
      $filename = $fileinfo->getFilename();
      $names = thumbs_names($filename);

      if(!find_file_in_cache($cachePath, $names, true)) {
        $type = in_array($ext, $videosExtensions) ? 'video' : 'photo';

        for($i = 0; $i < count($sizes); $i++) {
          $fileSized = CACHE_PATH . $cachePath . '/' . $names[$i];

          if($type == 'video') {
            create_video_thumb($fileinfo->getPathname(), $fileSized, $sizes[$i]);
          }
          else {
            create_photo_thumb($fileinfo->getPathname(), $fileSized, $sizes[$i]);
          }

          $folderFile = CACHE_PATH . $cachePath . '/folder_' . $sizes[$i] . '.jpg';
          if(!file_exists($folderFile)) {
            copy(CACHE_PATH . $cachePath . '/' . $names[$i], $folderFile);
            chmod($folderFile, 0775);
          }
        }

        if(!file_exists(CACHE_PATH . $cachePath . '/banner.jpg')) {
          if($type == 'video') {
            create_video_thumb($fileinfo->getPathname(), CACHE_PATH . $cachePath . '/banner.jpg', PHOTOS_BANNER_SIZE);
          }
          else {
            create_photo_thumb($fileinfo->getPathname(), CACHE_PATH . $cachePath . '/banner.jpg', PHOTOS_BANNER_SIZE);
          }
        }

        if(!file_exists(CACHE_PATH . $cachePath . '/logo.jpg')) {
          if($type == 'video') {
            create_video_thumb($fileinfo->getPathname(), CACHE_PATH . $cachePath . '/logo.jpg', PHOTOS_LOGO_SIZE);
          }
          else {
            create_photo_thumb($fileinfo->getPathname(), CACHE_PATH . $cachePath . '/logo.jpg', PHOTOS_LOGO_SIZE);
          }
        }

        $config = get_config_file(CACHE_PATH . $cachePath . '/config');

        $size = '';
        if($type == 'photo') {
          $size = getimagesize($fileinfo->getPathname());
          $size = $size[0] . 'x' . $size[1];
        }

        $configFileExists = false;
        for($i = 0, $len = count($config['items']); $i < $len; $i++) {
          if($config['items'][$i]['name'] == $filename) {
            $configFileExists = true;

            $config['items'][$i]['size'] = $size;

            break;
          }
        }

        if(!$configFileExists) {
          $config['items'] []= array(
            'name' => $filename,
            'type' => $type,
            'visible' => true,
            'featured' => false,
            'size' => $size,
            'identities' => array(),
            'tags' => array()
          );
        }

        if($type == 'photo') {
          $config['number_photos']++;
          $config['number_photos_visible']++;
        }
        else if($type == 'video') {
          $config['number_videos']++;
          $config['number_videos_visible']++;
        }
        set_config_file(CACHE_PATH . $cachePath . '/config', $config);

        $globalConfig = get_global_config();

        $globalConfig['last_update'] = date('U');
        if($type == 'photo') {
          $globalConfig['number_photos']++;
          $globalConfig['number_photos_visible']++;
        }
        else if($type == 'video') {
          $globalConfig['number_videos']++;
          $globalConfig['number_videos_visible']++;
        }

        set_global_config($globalConfig);

        $haveWorked = array(
          'path' => $cachePath,
          'file' => $filename
        );

        break;
      }

    }
  }

  // TODO: Need optims
  if(false && !$haveWorked) {
    $directory = new RecursiveDirectoryIterator(CACHE_PATH);
    $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::LEAVES_ONLY);

    $globalConfig = get_global_config();
    $haveUpdatedGlobalConfig = false;

    foreach($iterator as $fileinfo) {
      $filePath = $fileinfo->getPath();
      $path = str_replace(CACHE_PATH, '', $filePath);

      if($fileinfo->getFilename() == '.') {

        if(!is_dir(PHOTOS_PATH . $path)) {
          $haveUpdatedGlobalConfig = true;

          $result = remove_path($filePath);
          $globalConfig['number_photos'] -= $result['number_photos'];
          $globalConfig['number_videos'] -= $result['number_videos'];
          $globalConfig['number_albums'] -= $result['number_albums'];
          $globalConfig['number_photos_visible'] -= $result['number_photos_visible'];
          $globalConfig['number_videos_visible'] -= $result['number_videos_visible'];
          $globalConfig['number_albums_visible'] -= $result['number_albums_visible'];
        }
      }
      else if(is_dir($filePath)) {
        if(strpos($fileinfo->getFilename(), '_' . $sizes[0]) !== false) {
          $file = str_replace('_' . $sizes[0] . '.jpg', '',  $fileinfo->getFilename());
          $fileLen = strlen($file);

          $exists = false;

          if($handle = opendir(PHOTOS_PATH . $path)) {
            while(($fileSearch = readdir($handle)) !== false) {
              if(strlen($fileSearch) > $fileLen && substr($fileSearch, 0, $fileLen) == $file) {
                $exists = true;
                break;
              }
            }
            closedir($handle);
          }

          if(!$exists) {
            $config = get_config_file($filePath . '/config');
            $i = 0;
            foreach($config['items'] as $item) {
              if(strlen($item['name']) > $fileLen && substr($item['name'], 0, $fileLen) == $file) {

                $haveUpdatedGlobalConfig = true;

                if($item['type'] == 'photo') {
                  $config['number_photos'] --;
                  $globalConfig['number_photos'] --;
                  if($item['visible']) {
                    $config['number_photos_visible'] --;
                    $globalConfig['number_photos_visible'] --;
                  }
                }
                else if($item['type'] == 'video') {
                  $config['number_videos'] --;
                  $globalConfig['number_videos'] --;
                  if($item['visible']) {
                    $config['number_videos_visible'] --;
                    $globalConfig['number_videos_visible'] --;
                  }
                }

                array_splice($config['items'], $i, 1);
                set_config_file($filePath . '/config', $config);

                $files = array_diff(scandir($filePath), array('.', '..'));
                foreach($files as $fileSearch) {
                  if(is_file($filePath . '/' . $fileSearch) && strlen($fileSearch) > $fileLen && substr($fileSearch, 0, $fileLen) == $file) {
                    @unlink($filePath . '/' . $fileSearch);
                  }
                }

                break;
              }
              $i++;
            }
          }
        }
      }
    }
    if($haveUpdatedGlobalConfig) {
      set_global_config($globalConfig);
    }
  }

  return $haveWorked;
}

function create_photo_thumb($fileSource, $fileDestination, $size) {
  require_once ROOT_PATH . '/vendor/phpThumb/phpthumb.class.php';

  $crop = in_array($size, photos_crop());

  $size = explode('x', $size);

  $phpThumb = new phpThumb();
  $phpThumb->setSourceFilename($fileSource);
  if($size[0] != 'auto') {
    $phpThumb->setParameter('w', $size[0]);
  }
  if($size[1] != 'auto') {
    $phpThumb->setParameter('h', $size[1]);
  }

  if($crop) {
    $phpThumb->setParameter('zc', 1);
  }
  $phpThumb->setParameter('config_output_format', 'jpeg');

  if($phpThumb->GenerateThumbnail()) {
    $phpThumb->RenderToFile($fileDestination);
    $phpThumb->purgeTempFiles();
  }
  else {
    copy(__DIR__ . '/assets/error.jpg', $fileDestination);
    // var_dump($phpThumb->fatalerror);
    // var_dump($phpThumb->debugmessages);
  }

  chmod($fileDestination, 0775);
}

function create_video_thumb($fileSource, $fileDestination, $size) {
  $video = new ffmpeg_movie($fileSource);
  $frame = $video->getFrame(min($video->getFrameCount() - 1, 100));
  if($frame) {
    $gd_image = $frame->toGDImage();
    if ($gd_image) {
      $tmpFile = $fileDestination . '.tmp';

      imagejpeg($gd_image, $tmpFile);
      imagedestroy($gd_image);
      chmod($tmpFile, 0775);

      create_photo_thumb($tmpFile, $fileDestination, $size);
      @unlink($fileDestination . '.tmp');

      return;
    }
  }

  copy(__DIR__ . '/assets/error.jpg', $fileDestination);
}

function default_config() {
  return array(
    'visible' => true,
    'featured' => false,
    'description' => '',
    'identities' => array(),
    'tags' => array(),
    'number_photos' => 0,
    'number_videos' => 0,
    'number_photos_visible' => 0,
    'number_videos_visible' => 0,
    'items' => array()
  );
}

function get_config_file($fileDestination, $applyDefault = true) {
  $configFile = $fileDestination . '.conf';
  $data = false;
  if(is_file($configFile)) {
    $data = json_decode(file_get_contents($configFile), true);
  }

  if($applyDefault) {
    if(!$data) {
      $data = array();
    }
    $data = array_merge(default_config(), $data);
  }

  return $data;
}

function set_config_file($fileDestination, $params) {
  $configFile = $fileDestination . '.conf';
  file_put_contents($configFile, json_encode($params));
  chmod($configFile, 0775);
}

function default_global_config() {
  return array(
    'last_update' => 0,
    'identities' => array(),
    'tags' => array(),
    'longsessions' => array(),
    'number_albums' => 0,
    'number_photos' => 0,
    'number_videos' => 0,
    'number_albums_visible' => 0,
    'number_photos_visible' => 0,
    'number_videos_visible' => 0,
    'featured' => array()
  );
}

function get_global_config() {
  $config = get_config_file(CACHE_PATH . '/globalconfig', false);
  if(!$config) {
    $config = array();
  }
  return array_merge(default_global_config(), $config);
}

function set_global_config($config) {
  set_config_file(CACHE_PATH . '/globalconfig', $config);
}

function photos_elements($onlyInvisibles = false) {
  global $pathurl;
  global $photospath;

  $path = str_replace(PHOTOS_PATH, '', $photospath);
  if(substr($path, 0, 1) == '/') {
    $path = substr($path, 1, strlen($path) - 1);
  }
  $photosUrl = PHOTOS_URL . '/' . ($path !== '' ? $path . '/' : '');
  $cachePath = CACHE_PATH . '/' . ($path !== '' ? $path . '/' : '');
  $cacheUrl = CACHE_URL . '/' . ($path !== '' ? $path . '/' : '');
  $sizes = photos_sizes();

  $result = get_config_file($cachePath . '/config');

  if(user_is_admin()) {
    $result['source_number_photos'] = number_photos(PHOTOS_PATH . '/' . $path);
    $result['source_number_videos'] = number_videos(PHOTOS_PATH . '/' . $path);
  }

  if(!$onlyInvisibles) {
    $result['number_photos'] = $result['number_photos_visible'];
    $result['number_videos'] = $result['number_videos_visible'];
    unset($result['number_photos_visible']);
    unset($result['number_videos_visible']);
  }

  for($i = count($result['items']) - 1; $i >= 0; $i--) {
    if((!$onlyInvisibles && !$result['items'][$i]['visible']) || ($onlyInvisibles && $result['items'][$i]['visible'])) {
      array_splice($result['items'], $i, 1);
    }
    else if(($onlyInvisibles && !$result['items'][$i]['visible']) || !!$result['items'][$i]['visible'] && $result['items'][$i]['visible']) {
      $result['items'][$i]['fileurl'] = $photosUrl . $result['items'][$i]['name'];
      $result['items'][$i]['sizes'] = array();
      $names = thumbs_names($result['items'][$i]['name']);
      for($j = 0; $j < count($sizes); $j++) {
        $result['items'][$i]['sizes'][$sizes[$j]] = $cacheUrl . $names[$j];
      }
    }
  }

  $result['albums'] = array();

  if($handle = opendir($cachePath)) {
    while(($fileSearch = readdir($handle)) !== false) {
      if($fileSearch != '.' && $fileSearch != '..') {
        $folderPath = $cachePath . $fileSearch;

        if(is_dir($folderPath)) {
          $folderUrl = array();
          $folderUrlArray = explode('/', str_replace(CACHE_PATH . '/', '', $cachePath . $fileSearch));
          foreach($folderUrlArray as $url) {
            $folderUrl []= photos_folder_name_to_url($url);
          }

          $date = explode(' ', str_replace(array('photos ', '.'), array('', '/'), strtolower($fileSearch)));
          $date = $date[0];
          if(count(explode('/', $date)) == 3) {
            $date = strtotime($date);
          }
          else {
            $date = intval($date);
          }

          $folderUrl = implode('/', $folderUrl);

          $config = get_config_file($folderPath . '/config');

          if($config && ((!$onlyInvisibles && $config['visible']) || $onlyInvisibles && ! $config['visible'])) {
            $poster = array();
            $names = thumbs_names('folder.jpg');
            for($j = 0; $j < count($sizes); $j++) {
              $poster[$sizes[$j]] = $cacheUrl . $fileSearch . '/' . $names[$j];
            }

            $url = $folderUrl;

            $result['albums'] []= array(
              'name' => $fileSearch,
              'title' => photos_folder_name_to_title($fileSearch),
              'description' => $config['description'],
              'url' => '/' . $folderUrl,
              'date' => $date,
              'poster' => $poster,
              'number_photos' => $config['number_photos_visible'],
              'number_videos' => $config['number_videos_visible'],
              'featured' => $config['featured'],
              'tags' => array_keys($config['tags']),
              'identities' => array_keys($config['identities'])
            );
          }
        }
      }
    }
    closedir($handle);
  }

  return $result;
}

function delete_folder($path) {
  $directory = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
  $files = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::CHILD_FIRST);
  foreach($files as $file) {
    if ($file->getFilename() === '.' || $file->getFilename() === '..') {
      continue;
    }
    if ($file->isDir()) {
      rmdir($file->getRealPath());
    }
    else {
      unlink($file->getRealPath());
    }
  }
  rmdir($path);
}

function delete_in_cache($path, $name, $type) {
  make_path_url($path);

  global $photospath;
  $path = str_replace(PHOTOS_PATH, CACHE_PATH, $photospath);

  if($type == 'album') {
    $albumPath = $path . '/' . $name;

    $globalConfig = get_global_config();

    $globalConfig['number_photos'] -= $albumConfig['number_photos'];
    $globalConfig['number_photos_visible'] -= $albumConfig['number_photos_visible'];
    $globalConfig['number_videos'] -= $albumConfig['number_videos'];
    $globalConfig['number_videos_visible'] -= $albumConfig['number_videos_visible'];

    set_global_config($globalConfig);

    delete_folder($albumPath);
  }
  else {

    $config = get_config_file($path . '/config');

    $items = array();
    $removeLength = 0;
    $removeType = false;

    foreach($config['items'] as $item) {
      if($item['name'] == $name)  {
        $removeType = $item['type'];
        $removeLength++;
      }
      else {
        $items []= $item;
      }
    }
    $config['items'] = $items;

    if($removeType) {
      $config['number_' . $removeType . 's'] -= $removeLength;
      $config['number_' . $removeType . 's_visible'] -= $removeLength;

      set_config_file($path . '/config', $config);

      $globalConfig = get_global_config();
      $globalConfig['number_' . $removeType . 's'] -= $removeLength;
      $globalConfig['number_' . $removeType . 's_visible'] -= $removeLength;

      set_global_config($globalConfig);
    }

    $names = thumbs_names($name);

    foreach($names as $thumbName) {
      @unlink($path . '/' . $thumbName);
    }
  }
}

function make_unique_thumb($type, $destinationName, $size, $path, $name, $parent) {
  $destinationPath = $parent ? get_parent_url($path) : $path;

  make_path_url($path);

  global $photospath;
  $path = $photospath;

  make_path_url($destinationPath);
  $destinationPath = str_replace(PHOTOS_PATH, CACHE_PATH, $photospath);
  $picture = $destinationPath . '/' . $destinationName;

  if($type == 'photo') {
    create_photo_thumb($path . '/' . $name, $picture, $size);
  }
  else if($type == 'video') {
    create_video_thumb($path . '/' . $name, $picture, $size);
  }

  $picture =  str_replace(CACHE_PATH, CACHE_URL, $picture);

  return $picture;
}

function make_logo($type, $path, $name, $parent) {
  $originPath = $path;
  $sizes = photos_sizes();
  $destinationPath = $parent ? get_parent_url($path) : $path;

  make_path_url($path);

  global $photospath;
  $path = $photospath;

  make_path_url($destinationPath);
  $destinationPath = str_replace(PHOTOS_PATH, CACHE_PATH, $photospath);

  for($i = 0; $i < count($sizes); $i++) {
    $fileName = 'folder_' . $sizes[$i] . '.jpg';

    if($type == 'photo') {
      create_photo_thumb($path . '/' . $name, $destinationPath . '/' . $fileName, $sizes[$i]);
    }
    else if($type == 'video') {
      create_video_thumb($path . '/' . $name, $destinationPath . '/' . $fileName, $sizes[$i]);
    }
  }

  return make_unique_thumb($type, 'logo.jpg', PHOTOS_LOGO_SIZE, $originPath, $name, $parent);
}

function make_banner($type, $path, $name, $parent) {
  return make_unique_thumb($type, 'banner.jpg', PHOTOS_BANNER_SIZE, $path, $name, $parent);
}

function set_tags_identities($type, $path, $name, $value) {
  $originPath = $path;

  $values = array_map(function($val) {
    return strtolower(trim($val));
  }, explode(',', $value));

  $values = array_filter($values, function($val) {
    return $val !== '';
  });

  make_path_url($path);

  global $photospath;
  $path = str_replace(PHOTOS_PATH, CACHE_PATH, $photospath);

  $config = get_config_file($path . '/config');

  $items = array();
  foreach($config['items'] as $item) {
    if($item['name'] == $name)  {
      $item[$type] = $values;
    }
    $items []= $item;
  }
  $config['items'] = $items;

  set_config_file($path . '/config', $config);

  $path = $originPath;
  $lastPath = '';
  $file = $originPath . '/' . $name;

  while($path != $lastPath) {
    $lastPath = $path;

    make_path_url($path);
    $pathFolder = str_replace(PHOTOS_PATH, CACHE_PATH, $photospath);
    $config = get_config_file($pathFolder . '/config');

    foreach($config[$type] as $identity => $files) {
      $pos = array_search($file, $config[$type][$identity]);
      if($pos !== false) {
        unset($config[$type][$identity][$pos]);
      }

      if(count($config[$type][$identity]) == 0) {
        unset($config[$type][$identity]);
      }
    }

    foreach($values as $value) {
      if(!isset($config[$type][$value])) {
        $config[$type][$value] = array();
      }

      $config[$type][$value] []= $file;
    }

    set_config_file($pathFolder . '/config', $config);

    $path = get_parent_url($path);
  }

  return $values;
}

function set_tags($path, $name, $value) {
  return set_tags_identities('tags', $path, $name, $value);
}

function set_identities($path, $name, $value) {
  return set_tags_identities('identities', $path, $name, $value);
}