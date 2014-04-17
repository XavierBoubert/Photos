$(function() {
  'use strict';

  window.Viewer = new (function(window) {

    var _viewer = this,
        _isInit = false,
        _opened = false,
        _items = [],
        _selected = -1,
        _downloadUrl = '',
        _stopCheckHash = false,
        _displayWidth = 0,
        _scrollTop = 0,
        $el = {
          window: $(window),
          document: $(document),
          body: $(document.body),
          items: $('.explorer-viewer-items'),
          viewer: $('.explorer-viewer'),
          banner: $('.banner'),
          explorer: $('.explorer-container'),
          menu: $('.explorer-viewer-menu'),
          menuButton: $('.explorer-viewer-menu-button'),
          downloadButton: $('.viewer-but-download'),
          downloadButtonSize: $('.viewer-but-download-size')
        },
        _hammer = new Hammer($el.items.get(0), {
          drag_lock_to_axis: true
        });

    this.Templates = null;

    function _checkHash() {
      if(!_stopCheckHash) {

        var hash = location.hash.replace('#', '');
        if(hash === '') {
          _viewer.close();
        }
        else {
          _viewer.open(parseInt(hash, 10));
        }

      }
      _stopCheckHash = false;
    }

    function _refreshViewer(justPosition) {
      justPosition = justPosition || false;

      if(_items.length > 0) {
        _displayWidth = $el.body.width()

        $el.items.css({
          width: _displayWidth * _items.length,
          left: -(_selected * _displayWidth)
        });

        $.each(_items, function(i, item) {
          item.view('viewer-' + item.type()).css({
            width: _displayWidth,
            left: _displayWidth * i
          });
        });
      }
    }

    function _loadIndex(index) {
      var item = _viewer.item(index);
      if(item && item.type() == window.PHOTOS_TYPES.PHOTO) {
        var el = _viewer.itemView(index);
        if(el && el.find('.photo').css('background-image') === 'none') {

          var elPhoto = el.find('.photo'),
              elLoading = el.find('.loading'),
              src = item.config().sizes['1920x1080'];

          $('<img/>').attr('src', src).load(function() {
            $(this).remove();

            el.addClass('before-open');

            setTimeout(function() {
              elLoading.remove();

              elPhoto.css('background-image', 'url("' + src + '")');
              el.removeClass('before-open');

            }, 350);

          });

        }
      }
    }

    function _select(index, justPosition) {
      justPosition = justPosition || false;

      if(index >= 0 && index < _items.length) {
        var item = _viewer.item(index);

        if(index != _selected) {
          _stopVideos();
          _viewer.closeMenu();
          if(item.type() == PHOTOS_TYPES.PHOTO) {
            $el.downloadButtonSize.html(item.config().size.split('x').join(' x '));
            _downloadUrl = '/download' + item.config().src;
            _viewer.showMenuButton();
          }
          else {
            _viewer.hideMenuButton();
          }
        }

        _selected = index;

        _loadIndex(index);
        if(index - 1 >= 0) {
          _loadIndex(index - 1);
        }
        if(index + 1 < _items.length) {
          _loadIndex(index + 1);
        }
      }

      _refreshViewer(justPosition);
    }

    function _stopVideos() {
      $el.items.find('video').each(function(index, el) {
        el.pause();
      });
    }

    this.init = function() {
      if(!_isInit) {
        _isInit = true;
        _viewer.Templates = new Templates('.explorer-viewer-items');

        _items = Explorer.album().items([PHOTOS_TYPES.PHOTO, PHOTOS_TYPES.VIDEO]);

        _items.sort(function(a, b) {
          var order = [a.config().order, b.config().order];
          if(order[0] < order[1]) {
            return -1;
          }
          if (order[0] > order[1]) {
            return 1;
          }
          return 0;
        });

        $.each(_items, function(index, item) {
          item.attachTemplate(_viewer.Templates, 'viewer-' + item.type());
          item.render('viewer-' + item.type());

          var $explorerView = item.view('explorer-' + item.type());

          $explorerView.data('urlindex', index);

          $explorerView.find('a').click(function() {
            _viewer.open(index);
          })
        });

        _refreshViewer();
        _checkHash();
      }
    };

    this.item = function(index) {
      if(_items.length > 0) {
        return _items[index];
      }
      return false;
    };

    this.itemView = function(index) {
      var item = _viewer.item(index);

      if(item) {
        var el = item.view('viewer-' + item.type());
        return el || false;
      }

      return false;
    };

    this.open = function(index) {
      _stopCheckHash = true;
      _opened = true;
      var el = _viewer.itemView(index);

      location.hash = index;

      _scrollTop = $el.body.scrollTop();
      $el.banner.addClass('disable');
      $el.explorer.addClass('disable');

      _select(index);

      el.addClass('before-open');
      $el.viewer.addClass('open');

      setTimeout(function() {
        el.removeClass('before-open');
      });
    };

    this.close = function() {
      _opened = false;

      if(_selected > -1) {
        _stopVideos();
        _viewer.closeMenu();

        var el = _viewer.itemView(_selected);
        el.addClass('before-close');

        setTimeout(function() {
          el.addClass('before-open');

          setTimeout(function() {
            $el.viewer.removeClass('open');
            el
              .removeClass('before-close')
              .removeClass('before-open');

            $el.banner.removeClass('disable');
            $el.explorer.removeClass('disable');
            $el.body.scrollTop(_scrollTop);

            _selected = -1;
          }, 350);

        });
      }
    };

    this.next = function() {
      $el.items.addClass('animate');

      _select(_selected + 1, true);

      setTimeout(function() {
        $el.items.removeClass('animate');
      }, 300);
    };

    this.previous = function() {
      $el.items.addClass('animate');

      _select(_selected - 1, true);

      setTimeout(function() {
        $el.items.removeClass('animate');
      }, 300);
    };

    function _setContainerOffset(percent, animate) {
      $el.items.removeClass('animate');

      if(animate) {
        $el.items.addClass('animate');
      }

      var px = ((_displayWidth * _items.length) / 100) * percent;
      $el.items.css('left', px + 'px');
    }

    _hammer.on('release dragleft dragright swipeleft swiperight', function(ev) {
      //ev.gesture.preventDefault();

      switch(ev.type) {
        case 'dragright':
        case 'dragleft':
          // stick to the finger
          var pane_offset = -(100 / _items.length) * _selected,
              drag_offset = ((100 / _displayWidth) * ev.gesture.deltaX) / _items.length;

          // slow down at the first and last pane
          if((_selected == 0 && ev.gesture.direction == 'right') || (_selected == _items.length - 1 && ev.gesture.direction == 'left')) {
            drag_offset *= .4;
          }

          _setContainerOffset(drag_offset + pane_offset);

          break;

        case 'swipeleft':

          _viewer.next();
          ev.gesture.stopDetect();

          break;

        case 'swiperight':

          _viewer.previous();
          ev.gesture.stopDetect();

          break;

        case 'release':
          // more then 5% moved, navigate
          if(Math.abs(ev.gesture.deltaX) > _displayWidth / 20) {
            if(ev.gesture.direction == 'right') {
              _viewer.previous();
            }
            else {
              _viewer.next();
            }
          }
          else {
            _select(_selected, true);
          }

          break;
        }
    });

    this.showMenuButton = function() {
      $el.menuButton.removeClass('invisible');
    };

    this.hideMenuButton = function() {
      $el.menuButton.addClass('invisible');
    };

    this.openMenu = function() {
      $el.menuButton.addClass('open');
      $el.menu.addClass('open');
    };

    this.closeMenu = function() {
      $el.menuButton.removeClass('open');
      $el.menu.removeClass('open');
    };

    $el.menuButton.click(function() {
      if($el.menuButton.hasClass('open')) {
        _viewer.closeMenu();
      }
      else {
        _viewer.openMenu();
      }
    });

    $el.window.on('resize', function() {
      _refreshViewer();
    });

    $el.window.hashchange(function() {
      _checkHash();
    });

    $el.document.keydown(function(e) {
      if(_opened) {
        if(e.keyCode == 27) { // escape
          history.back();
        }
        if(e.keyCode == 37) { // left
          _viewer.previous();
        }
        else if(e.keyCode == 39) { // right
          _viewer.next();
        }
      }
    });

    $el.downloadButton.click(function() {
      window.location.href = _downloadUrl;
    });

  })(window);

});