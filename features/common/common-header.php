<?php

  global $siteConfig;

  $globalConfig = get_global_config();

?><!doctype html>
<html lang="fr">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="author" content="Xavier Boubert http://xavierboubert.fr" />
  <title><?php echo $siteConfig['page-title']; ?></title>

  <link rel="icon" type="image/png" href="/favicon.ico" />
  <link rel="shortcut icon" href="/favicon.png" />
  <link rel="apple-touch-icon" href="/common/assets/images/touch-icon.png" />

  <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
  <link rel="icon" type="image/png" href="/favicon-196x196.png" sizes="196x196">
  <link rel="icon" type="image/png" href="/favicon-160x160.png" sizes="160x160">
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
  <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
  <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-TileImage" content="/mstile-144x144.png">

  <link rel="stylesheet" href="/vendor/fontello/css/fontello.css" type="text/css" media="screen" charset="utf-8" />
  <link rel="stylesheet" href="/vendor/magicsuggest/magicsuggest-1.3.1-min.css" type="text/css" media="screen" charset="utf-8" />
  <link rel="stylesheet" href="/features/common/assets/common.css" type="text/css" media="screen" charset="utf-8" />
  <style type="text/css">
    .banner .banner-cover { background-image: url('<?php echo photos_banner(); ?>'); }
  </style>
  <?php css(); ?>
</head>
<body class="<?php echo $siteConfig['body_css']; ?>" <?php html_data(); ?>>

  <div class="banner">
    <div class="banner-container">
      <div class="banner-cover"></div>
    </div>
    <div class="banner-mask"></div>

    <div class="banner-title">
      <h1><?php echo $siteConfig['title']; ?></h1>
      <h2><?php echo $siteConfig['site-description']; ?></h2>
    </div>

    <a class="banner-logo" href="/" title="Revenir à l'accueil">
      <img src="<?php echo photos_logo(); ?>" alt="<?php echo $siteConfig['title']; ?>" />
      <div class="back-button">
        <i class="icon-left"></i>
      </div>
    </a>

    <?php if(user_connected()) { ?>
    <div class="banner-infos">
      <div class="info-column number-albums"><span><?php echo $globalConfig['number_albums_visible']; ?></span> albums</div>
      <div class="info-column number-photos"><span><?php echo $globalConfig['number_photos_visible']; ?></span> photos</div>
      <div class="info-column number-videos"><span><?php echo $globalConfig['number_videos_visible']; ?></span> vidéos</div>
      <div class="info-column last-update"><span><?php echo strftime('%e %B %Y', $globalConfig['last_update']); ?></span> dernier ajout</div>
      <div class="clear"></div>
    </div>
    <?php } ?>

    <div class="banner-menu-container">

      <div class="banner-menu">

        <?php fire_hook_menu(); ?>

        <div class="right-menu">
          <?php if(user_is_admin()) { ?>

          <div id="but-worker-container" class="but-worker-container">
            <input id="but-worker" class="but-worker" type="button" value="" />
          </div>

          <input id="but-role" class="visitor" type="button" value="visiteur" />

          <div id="but-role-loading" class="but-role-loading"><div></div></div>

          <?php } ?>

          <?php if(user_connected()) { ?>
          <input id="but-logout" type="button" value="déconnexion" />
          <?php } ?>
        </div>

        <div class="clear"></div>
      </div>

    </div>

  </div>

  <div id="worker-bar" class="worker-bar">
    <div class="progress"></div>
    <div class="label"></div>
    <div class="stop">x</div>
  </div>