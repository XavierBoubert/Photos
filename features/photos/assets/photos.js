$(function() {
  'use strict';

  window.PHOTOS_TYPES = {
    ALBUM: 'album',
    PHOTO: 'photo',
    VIDEO: 'video'
  };

  window.PHOTOS_TYPES_LIST = [];
  $.each(window.PHOTOS_TYPES, function(key, value) {
    window.PHOTOS_TYPES_LIST.push(value);
  });

  var _ids = -1;

  var AbstractItem = function(defaultConfig, config) {
    defaultConfig = defaultConfig || {};
    config = config || {};

    AbstractEvents.call(this);

    var _this = this,
        _id = ++_ids,
        _templates = {},
        _waitImages = {
          progress: 0,
          total: 0
        };

    $.extend(true, config, $.extend(true, defaultConfig, config));

    function _flatConfig(flat, configToProcess, pre) {
      flat = flat || {};
      configToProcess = configToProcess || config;
      pre = pre || '';

      $.each(configToProcess, function(key, value) {
        if($.isPlainObject(value)) {
          _flatConfig(flat, value, key + '_');
        }
        else {
          flat[pre + key] = value;
        }
      });

      return flat;
    };

    this.type = function() {
      return config.type;
    };

    this.id = function(context) {
      return context + '-' + _id;
    };

    this.config = function() {
      return config;
    };

    this.attachTemplate = function(templates, name, selector) {
      selector = selector || false;
      _templates[name] = {
        templates: templates,
        selector: selector
      };
    };

    function _imageLoaded(name) {
      _waitImages.progress++;
      if(_waitImages.progress == _waitImages.total) {
        _this.fire(name + '-rendered');
      }
    }

    this.render = function(name) {
      if(typeof name == 'undefined') {
        return false;
      }

      var id = _this.id(name);

      if(typeof _templates[name] != 'undefined' && $('#' + id).length === 0) {
        var templates = _templates[name].templates,
            selector = _templates[name].selector || templates.selector(),
            flatConfig = _flatConfig();

        flatConfig.id = id;

        $(selector).append(templates[name](flatConfig));

        var $el = $('#' + id);

        var imgs = $el.find('img');
        _waitImages.total = imgs.length;

        if(_waitImages.total > 0) {
          imgs.each(function(index, img) {
            if(img.complete) {
              _imageLoaded(name);
            }
            else {
              img.onload = function() {
                _imageLoaded(name);
              };
            }
          })
        }
        else {
          _this.fire('rendered');
        }

        return true;
      }

      return false;
    };

    this.view = function(name) {
      if(typeof name == 'undefined') {
        return false;
      }

      var id = _this.id(name),
          $el = $('#' + id);

      if($el.length > 0) {
        return $el;
      }

      return false;
    };

    this.refresh = function(name) {
      var $el = _this.view(name);
      if($el) {
        var templates = _templates[name].templates,
            flatConfig = _flatConfig();

        flatConfig.id = 'container';
        var div = $('<div />').append(templates[name](flatConfig));
        $el.html(div.find('#container').html());
        div.remove();
      }
    };

  };

  window.Album = function(config) {
    config = config || {};

    AbstractItem.call(this, {
      title: {
        title: '',
        folder: ''
      },
      description: [],
      url: '',
      poster: null,
      tags: [],
      identities: [],
      date: 0,
      number_photos: 0,
      number_videos: 0,
      type: 'album'
    }, config);

    config.hide_title = config.title.title === '' ? 'hide' : '';
    config.hide_description = config.description === '' ? 'hide' : '';

    config.hide_number_photos = config.number_photos === 0 ? 'hide' : '';
    config.hide_number_videos = config.number_videos === 0 ? 'hide' : '';
    config.hide_number_photos_number_videos = config.number_photos === 0 || config.number_videos === 0 ? 'hide' : '';

    var _this = this,
        _items = [];

    this.add = function(item, digest, callback) {
      _items.push(item);
      _this.fire('added', {
        item: item,
        digest: typeof(digest) == 'undefined' ? true : digest,
        callback: callback || false
      });

      return item;
    };

    this.count = function(type) {
      return _items.filter(function(item) {
        return item.type() == type;
      }).length;
    };

    this.items = function(types) {
      types = types || window.PHOTOS_TYPES_LIST;

      if(!$.isArray(types)) {
        types = [types];
      }

      return _items.filter(function(item) {
        return $.inArray(item.type(), types) > -1;
      });
    };

  };

  window.Photo = function(config) {
    config = config || {};

    AbstractItem.call(this, {
      type: 'photo',
      name: '',
      src: '',
      size: '',
      sizes: null,
      identities: [],
      tags: []
    }, config);

    var _this = this;

  };

  window.Video = function(config) {
    config = config || {};

    AbstractItem.call(this, {
      type: 'video',
      name: '',
      src: '',
      size: '',
      sizes: null,
      identities: [],
      tags: []
    }, config);

    var _this = this;

  };

});