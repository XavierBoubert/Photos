<?php

global $siteConfig;
global $passwords;
global $admins;

$siteConfig = array(
  'site-title'        => 'Mon site de photos',
  'site-description'  => 'Toutes mes photos et vidéos',
  'photos-path'       => 'photos', // (optionnal) relative path start at webservice root directory
  'footer'            => '&copy ' . date('Y')
);

$passwords = array(
  'admin' => '$2y$10$llz4SSd8XPW24FQvsZsyCet7fg9t7E.oWEF.Mvxcdi8QTa5e.ZG/2' // admin:admin
);

$admins = array('admin');

function add_javascript($url) {
  global $jsurls;

  if(is_null($jsurls)) {
    $jsurls = array();
  }

  $jsurls []= $url;
}

function javascripts() {
  global $jsurls;

  if(is_null($jsurls)) {
    $jsurls = array();
  }

  foreach ($jsurls as $url) {
    echo '<script type="text/javascript" src="'.$url.'"></script>'."\n";
  }
}

function add_css($url) {
  global $css;

  if(is_null($css)) {
    $css = array();
  }

  $css []= $url;
}

function css() {
  global $css;

  if(is_null($css)) {
    $css = array();
  }

  foreach ($css as $url) {
    echo '<link rel="stylesheet" href="'.$url.'" type="text/css" media="screen" charset="utf-8" />'."\n";
  }
}

function add_html_data($dataName, $data) {
  global $htmlData;

  if(is_null($htmlData)) {
    $htmlData = array();
  }

  $htmlData[$dataName] = $data;
}

function html_data() {
  global $htmlData;

  if(is_null($htmlData)) {
    echo '';
    return;
  }

  foreach ($htmlData as $name => $data) {
    echo ' data-' . $name . '="' . htmlentities(json_encode($data)) . '"';
  }

}

function hook_menu($func) {
  global $cacheVars;

  if(!isset($cacheVars['menu'])) {
    $cacheVars['menu'] = array();
  }

  $cacheVars['menu'] []= $func;
}

function fire_hook_menu() {
  global $cacheVars;

  if(isset($cacheVars['menu'])) {
    foreach($cacheVars['menu'] as $menuFunc) {
      $menuFunc();
    }
  }
}

function get_parent_url($url = false) {
  if(!$url) {
    $url = $_SERVER['REQUEST_URI'];
  }

  if($url != '/') {
    if(substr($url, strlen($url) - 1, 1) == '/') {
      $url = substr($url, 0, strlen($url) - 1);
    }
    $url = substr($url, 0, strrpos($url, '/'));
    if($url === '') {
      $url = '/';
    }
  }

  return $url;
}

function make_path_url($url = false) {
  global $pathurl;
  global $photospath;

  if(!$url) {
    $url = $_SERVER['REQUEST_URI'];
  }

  $pathurl = array_merge(array(), array_filter(explode('/', $url), function($value) {
    return $value !== '';
  }));

  $pathurl = array_map(function($value) {
    $value = explode('?', $value);
    return $value[0];
  }, $pathurl);

  $photospath = photos_path($pathurl);

  return $pathurl;
}