<?php
  global $siteConfig;
  global $photospath;
  global $explorerElements;

  $title = str_replace(PHOTOS_PATH, '', $photospath);
  if($title !== '') {
    $title = explode('/', $title);
    $title = photos_folder_name_to_title($title[count($title) -1]);
    if($title['title'] !== '') {
      $title = implode(' - ', $title);
    }
    else {
      $title = $title['folder'];
    }
    $siteConfig['title'] = $title;
  }

  $explorerElements = photos_elements();

  if(isset($explorerElements['description']) && $explorerElements['description'] !== '') {
    $siteConfig['site-description'] = $explorerElements['description'];
  }

  $globalConfig = get_global_config();

  $pathFolder = str_replace(PHOTOS_PATH, CACHE_PATH, $photospath);
  $config = get_config_file($pathFolder . '/config');

  $search = array('data' => []);
  foreach (array('identities', 'tags') as $type) {
    foreach($config[$type] as $value => $files) {
      $search['data'] []= array(
        'id' => $type . '-' . implode('_', explode(' ', $value)),
        'name' => $value,
        'cls' => $type
      );
    }
  }

  add_html_data('search', $search);

  $path = str_replace(PHOTOS_PATH, '', $photospath);
  $path = $path === '' ? '/' : $path;
  add_html_data('path', array('path' => $path));

  if(user_connected()) {
    hook_menu(function() {
?>
    <input class="but-filter-toggle but-filter-photos" data-filter="photo" type="button" value="" />
    <input class="but-filter-toggle but-filter-videos" data-filter="video" type="button" value="" />

    <input class="but-menu" type="button" value="..." />

    <div class="banner-menu-select">
      <div class="select-search"></div>
    </div>
<?php
    });
  }

  add_css('/vendor/isotope/css/style.css');
  add_css('/features/explorer/assets/explorer-viewer.css');
  add_css('/features/explorer/assets/explorer.css');
  add_css('/features/explorer/assets/explorer-worker.css');

  require ROOT_PATH . '/features/common/common-header.php';
?>

  <?php if($siteConfig['body_css'] == 'page-index' && count($globalConfig['featured']) > 0) { ?>

  <div class="explorer-featured">

    <?php
      $index = -1;
      $globalConfig['featured'] = array_reverse($globalConfig['featured']);

      foreach($globalConfig['featured'] as $featured) {
        $index++
    ?>

    <a href="<?php echo $featured['url']; ?>" class="featured-item <?php echo $index % 2 ? 'odd' : ''; ?>">
      <img class="featured-preview" src="<?php echo $featured['picture']; ?>" />
      <div class="featured-mask">
        <div class="featured-mask-left"></div>
        <div class="featured-mask-right"></div>
      </div>
      <div class="featured-details">
        <span class="featured-title"><span><?php echo $featured['title']; ?></span></span>
        <span class="featured-description"><?php echo $featured['description']; ?></span>
      </div>
    </a>

    <?php } ?>

  </div>

  <?php } ?>

  <div class="explorer-container" data-album="<?php echo htmlentities(json_encode($explorerElements)); ?>">

    <div data-template="explorer-empty" class="template">
      <p class="explorer-empty">Cet album est vide</p>
    </div>

    <div class="explorer">

      <?php function admin_menu($type) { ?>
        <?php if(user_is_admin()) { ?>
          <div class="explorer-element-admin">
            <i class="icon-star" title="Mettre en avant"></i>
            <i class="icon-eye" title="Rendre invisible"></i>
            <i class="icon-eye-off" title="Rendre visible"></i>

            <?php if($type == 'album') { ?>

            <i class="icon-cw" title="Lancer la régénération de l'album"></i>

            <?php } else { ?>

            <i class="icon-cw" title="Lancer la régénération des miniatures"></i>
            <i class="icon-picture" title="Devenir le logo du répertoire"></i>
            <i class="icon-photo" title="Devenir la bannière du répertoire"></i>
            <i class="icon-tags" title="Modifier les mots clé"></i>
            <i class="icon-group" title="Modifier les identités"></i>

            <?php if($type == 'video') { ?>

            <i class="icon-video-alt" title="Régénérer les miniatures en fonction d'une frame spécifique"></i>

            <?php } ?>

            <?php } ?>
          </div>

          <div class="explorer-element-ask">

          <div class="ask ask-featured">
            <p>Titre</p>
            <input class="ask-title" type="text" />
            <p>Description</p>
            <textarea class="ask-input"></textarea>
            <input class="ask-submit-1" type="button" value="✔" />
            <div class="ask-submit-cancel-container">
              <input class="ask-submit-cancel" type="button" value="Annuler" />
            </div>
            <div class="clear"></div>
          </div>

          <?php if($type != 'album') { ?>

            <div class="ask ask-logo">
              <p>Devenir le logo de cette page ou de la page parente ?</p>
              <input class="ask-submit-1" type="button" value="Cette page" /><input class="ask-submit-2" type="button" value="Page parente" />
              <div class="ask-submit-cancel-container">
                <input class="ask-submit-cancel" type="button" value="Annuler" />
              </div>
            </div>

            <div class="ask ask-banner">
              <p>Devenir la bannière de cette page ou de la page parente ?</p>
              <input class="ask-submit-1" type="button" value="Cette page" /><input class="ask-submit-2" type="button" value="Page parente" />
              <div class="ask-submit-cancel-container">
                <input class="ask-submit-cancel" type="button" value="Annuler" />
              </div>
            </div>

            <div class="ask ask-tags">
              <p>Mots clés</p>
              <textarea class="ask-input">{{tagsValue}}</textarea>
              <input class="ask-submit-1" type="button" value="✔" />
              <div class="ask-submit-cancel-container">
                <input class="ask-submit-cancel" type="button" value="Annuler" />
              </div>
              <div class="clear"></div>
            </div>

            <div class="ask ask-identities">
              <p>Identités</p>
              <textarea class="ask-input">{{identitiesValue}}</textarea>
              <input class="ask-submit-1" type="button" value="✔" />
              <div class="ask-submit-cancel-container">
                <input class="ask-submit-cancel" type="button" value="Annuler" />
              </div>
              <div class="clear"></div>
            </div>

            <?php if($type == 'video') { ?>

            <div class="ask ask-video-frame">
              <p>Régénérer les miniatures de la vidéo en fonction du numéro de la frame :</p>
              <input class="ask-frame" type="text" value="100" />
              <input class="ask-submit-1" type="button" value="✔" />
              <div class="ask-submit-cancel-container">
                <input class="ask-submit-cancel" type="button" value="Annuler" />
              </div>
            </div>

            <?php } ?>

          <?php } ?>

          </div>

          <div class="loading"><div></div></div>

        <?php } ?>
      <?php } ?>

      <div data-template="explorer-album" class="template">
        <div class="element album no-transition beforeopen {{visible}} {{featured}} {{search_filters}}" id="{{id}}" data-order="{{order}}">
          <a href="{{url}}">
            <img template-src="{{poster_300x300}}" />
            <div class="working loading" title="Traitement du répertoire en cours"><div></div></div>
            <div class="element-title">
              <h3><span class="{{hide_title}}">{{title_title}}</span>{{title_folder}}</h3>
              <span class="description {{hide_description}}">{{description}}</span>
              <span class="details">
                <span class="{{hide_number_photos}}">{{number_photos}} {{_photos}}</span>
                <span class="{{hide_number_photos_number_videos}}">|</span>
                <span class="{{hide_number_videos}}">{{number_videos}} {{_videos}}</span>
              </span>
            </div>
          </a>
          <?php admin_menu('album'); ?>
        </div>
      </div>

      <div data-template="explorer-photo" class="template">
        <div class="element photo no-transition beforeopen {{visible}} {{featured}} {{search_filters}}" id="{{id}}" data-order="{{order}}">
          <a data-src="{{src}}" data-cachesrc="{{sizes_300x300}}" data-type="photo" data-size="{{size}}">
            <img template-src="{{sizes_300x300}}" />
          </a>
          <?php admin_menu('photo'); ?>
        </div>
      </div>

      <div data-template="explorer-video" class="template">
        <div class="element video no-transition beforeopen {{visible}} {{featured}} {{search_filters}}" id="{{id}}" data-order="{{order}}">
          <a data-src="{{src}}" data-cachesrc="{{sizes_300x300}}" data-type="video">
            <img template-src="{{sizes_300x300}}" />
            <i class="video-icon icon-play-circled2"></i>
          </a>
          <?php admin_menu('video'); ?>
        </div>
      </div>

    </div>

  </div>

  <div class="explorer-viewer">

    <div class="explorer-viewer-items">

      <div data-template="viewer-photo" class="template">
        <div class="explorer-viewer-item" id="{{id}}">
          <i class="loading icon-cog"></i>
          <div class="photo"></div>
        </div>
      </div>

      <div data-template="viewer-video" class="template">
        <div class="explorer-viewer-item" id="{{id}}">
          <video x-webkit-airplay="allow" controls="controls" preload="none" template-poster="{{sizes_1920x1080}}" template-src="{{src}}"></video>
        </div>
      </div>

    </div>

    <div class="explorer-viewer-menu">
      <div class="viewer-menu-item viewer-but-download">
        <i class="viewer-but-download icon-download-alt"></i>
        <span class="view-menu-text viewer-but-download-size"></span>
      </div>
      <!--div class="viewer-menu-item viewer-but-info">
        <i class="icon-info"></i>
        <span class="view-menu-text">infos</span>
      </div-->
    </div>

    <input class="explorer-viewer-menu-button" type="button" value="..." />

  </div>

  <?php add_javascript('/vendor/jquery.hashchange/jquery.hashchange.min.js') ?>
  <?php add_javascript('/vendor/isotope/jquery.isotope.min.js') ?>
  <?php add_javascript('/vendor/hammer.js/hammer.min.js') ?>
  <?php add_javascript('/features/photos/assets/photos.js') ?>
  <?php add_javascript('/features/explorer/assets/explorer-viewer.js') ?>
  <?php add_javascript('/features/explorer/assets/explorer.js') ?>
  <?php add_javascript('/features/explorer/assets/explorer-worker.js') ?>

<?php require ROOT_PATH . '/features/common/common-footer.php'; ?>