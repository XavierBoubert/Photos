$(function() {
  'use strict';

  window.Explorer = new (function(window) {

    var _explorer = this,
        _hasInit = false,
        _album = null,
        _filters = [],
        _isotopeInitiated = false,
        _numberToRender = 0,
        _numberRendered = 0,
        _numbers = {
          source: {
            photos: 0,
            videos: 0
          },
          cache: {
            photos: 0,
            videos: 0
          }
        },
        _path = $(document.body).data('path').path,
        $el = {
          window: $(window),
          body: $(document.body),
          container: $('.explorer-container'),
          explorer: $('.explorer')
        };

    AbstractEvents.call(this);

    this.Templates = null;

    this.init = function() {
      _explorer.Templates = new Templates('.explorer-container');

      var featuredItemsLen = $('.featured-item').length;
      if(featuredItemsLen > 0) {
        _slideFeatured(0, featuredItemsLen);
      }

      $('.but-filter-toggle').click(function() {
        var $this = $(this);
        if($this.hasClass('active')) {
          $this.removeClass('active');
          _explorer.removeFilter($this.data('filter'));
        }
        else {
          $('.but-filter-toggle').each(function(el, el2) {
            if($(this).hasClass('active')) {
              _explorer.removeFilter($(this).data('filter'));
            }
          });
          $('.but-filter-toggle').removeClass('active');
          $this.addClass('active');
          _explorer.addFilter($this.data('filter'));
        }
      });

      var searchCombo = $('.select-search').magicSuggest({
        width: 300,
        selectionPosition: 'right',
        emptyText: 'Recherche mots clés ou personnes...',
        noSuggestionText: 'Aucune suggestion',
        allowFreeEntries: false,
        useZebraStyle: false,
        data: $(document.body).data('search').data
      });

      $(searchCombo).on('selectionchange', function(event, combo, selection) {
        _filters = _filters.filter(function(filter) {
          return filter.indexOf('tags-') === -1 && filter.indexOf('identities-') === -1;
        });

        selection.map(function(item) {
          _filters.push(item.id);
        });

        _refreshFilters();
      });

      var data = $el.container.data('album');
      $el.container.removeAttr('data-album');

      _album = new Album({
        description: data.description,
        tags: data.tags,
        identities: data.identities
      });

      _numberToRender = data.albums.length + data.items.length;
      _numberRendered = 0;

      _album.on('added', function(args) {
        $('.explorer-empty').remove();

        var item = args.item;
        item.on('explorer-' + item.type() + '-rendered', function() {
          _numberRendered++;

          var view = item.view('explorer-' + item.type());

          function _startLoading() {
            view.addClass('loading-display');
            setTimeout(function() {
              view.addClass('loading-visible');
            });
          }

          function _stopLoading() {
            view.removeClass('loading-visible');
            setTimeout(function() {
              view.removeClass('loading-display');
            }, 1000);
          }

          view.find('.icon-star').click(function() {
            if(view.hasClass('featured')) {

              _startLoading();

              $.ajax({
                url: '/api/item-remove-featured',
                dataType: 'json',
                data: {
                  path: window.Page.rootUrl(),
                  name: item.config().name || '',
                  url: item.config().url || '',
                  type: item.type()
                },
                success: function(data) {
                  view.removeClass('featured');
                  _stopLoading();
                },
                error: function() {
                  _stopLoading();
                }
              });
            }
            else {
              _openAsk('featured');
              setTimeout(function() {
                view.find('.ask-featured .ask-title').focus();
              }, 350);
            }
          });

          view.find('.explorer-element-ask .ask-featured .ask-submit-1').click(function() {

            _startLoading();

            $.ajax({
              url: '/api/item-featured',
              dataType: 'json',
              data: {
                path: window.Page.rootUrl(),
                name: item.config().name || '',
                url: item.config().url || '',
                urlindex: item.view('explorer-' + item.type()).data('urlindex'),
                type: item.type(),
                title: view.find('.explorer-element-ask .ask-featured .ask-title').val(),
                description: view.find('.explorer-element-ask .ask-featured .ask-input').val()
              },
              success: function(data) {
                view.find('.explorer-element-ask .ask-featured .ask-title').val(''),
                view.find('.explorer-element-ask .ask-featured .ask-input').val('')
                view.addClass('featured');

                _closeAsk();
                _stopLoading();
              },
              error: function() {
                _closeAsk();
                _stopLoading();
              }
            });
          });

          view.find('.icon-eye').click(function() {
            $.ajax({
              url: '/api/item-hide',
              dataType: 'json',
              data: {
                path: window.Page.rootUrl(),
                name: item.config().name || '',
                url: item.config().url || '',
                type: item.type()
              },
              success: function(data) {
                view.removeClass('visible');
                view.addClass('invisible');
              }
            });
          });

          view.find('.icon-eye-off').click(function() {
            $.ajax({
              url: '/api/item-show',
              dataType: 'json',
              data: {
                path: window.Page.rootUrl(),
                name: item.config().name || '',
                url: item.config().url || '',
                type: item.type()
              },
              success: function(data) {
                view.removeClass('invisible');
                view.addClass('visible');
              }
            });
          });

          view.find('.icon-cw').click(function() {
            _startLoading();

            $.ajax({
              url: '/api/item-remake',
              dataType: 'json',
              data: {
                path: window.Page.rootUrl(),
                name: item.config().name || '',
                type: item.type()
              },
              success: function(data) {
                window.location.reload();
              }
            });
          });

          function _openAsk(name) {
            var $askContainer = view.find('.explorer-element-ask');
            $askContainer.addClass('display');
            setTimeout(function() {
              $askContainer.addClass('visible');
              var $ask = $askContainer.find('.ask-' + name);
              $ask.addClass('display');
              setTimeout(function() {
                $ask.addClass('visible');
              });
            });
          }

          function _closeAsk() {
            view.find('.explorer-element-ask').removeClass('visible');
            view.find('.ask-logo').addClass('close');
            view.find('.ask-banner').addClass('close');
            setTimeout(function() {
              view.find('.explorer-element-ask').removeClass('display');
              view.find('.ask')
                .removeClass('close')
                .removeClass('visible')
                .removeClass('display');
            }, 350);
          }

          function _makeLogoBanner(type, parent) {

            _startLoading();

            $.ajax({
              url: '/api/item-make-' + type,
              dataType: 'json',
              data: {
                path: window.Page.rootUrl(),
                name: item.config().name || '',
                type: item.type(),
                parent: parent
              },
              success: function(data) {

                if(data.success) {

                  if(!parent && type == 'logo') {
                    $('.banner-logo img').attr('src', data.src + '?_r=' + new Date().getTime());
                  }
                  else if(!parent && type == 'banner') {
                    $('.banner-cover').css('background-image', 'url("' + data.src + '?_r=' + new Date().getTime() + '")');
                  }
                }

                _closeAsk();
                _stopLoading();
              },
              error: function() {
                _closeAsk();
                _stopLoading();
              }
            });
          }

          function _submitTagsIdentities(type, value) {

            _startLoading();

            $.ajax({
              url: '/api/item-' + type,
              dataType: 'json',
              data: {
                path: window.Page.rootUrl(),
                name: item.config().name || '',
                type: item.type(),
                value: value
              },
              success: function(data) {

                _closeAsk();
                _stopLoading();

                if(data.success) {

                  var config = item.config();
                  config[type] = data[type];
                  config[type + 'Value'] = data[type].join(', ');

                  view.find('.explorer-element-ask .ask-' + type + ' .ask-input').val(config[type + 'Value']);
                }
              },
              error: function() {
                _closeAsk();
                _stopLoading();
              }
            });
          }

          view.find('.icon-picture').click(function() {
            _openAsk('logo');
          });

          view.find('.icon-photo').click(function() {
            _openAsk('banner');
          });

          view.find('.ask-submit-cancel').click(function() {
            _closeAsk();
          });

          view.find('.explorer-element-ask .ask-logo .ask-submit-1').click(function() {
            _makeLogoBanner('logo', false);
          });

          view.find('.explorer-element-ask .ask-logo .ask-submit-2').click(function() {
            _makeLogoBanner('logo', true);
          });

          view.find('.explorer-element-ask .ask-banner .ask-submit-1').click(function() {
            _makeLogoBanner('banner', false);
          });

          view.find('.explorer-element-ask .ask-banner .ask-submit-2').click(function() {
            _makeLogoBanner('banner', true);
          });

          view.find('.icon-tags').click(function() {
            _openAsk('tags');
            setTimeout(function() {
              view.find('.ask-tags .ask-input').focus();
            }, 350);
          });

          view.find('.icon-group').click(function() {
            _openAsk('identities');
            setTimeout(function() {
              view.find('.ask-identities .ask-input').focus();
            }, 350);
          });

          view.find('.explorer-element-ask .ask-tags .ask-submit-1').click(function() {
            _submitTagsIdentities('tags', view.find('.explorer-element-ask .ask-tags .ask-input').val());
          });

          view.find('.explorer-element-ask .ask-identities .ask-submit-1').click(function() {
            _submitTagsIdentities('identities', view.find('.explorer-element-ask .ask-identities .ask-input').val());
          });

          if(_isotopeInitiated) {
            view
              .removeClass('no-transition')
              .removeClass('beforeopen');
            $el.explorer.isotope('insert', view);
          }
          else if(_numberRendered == _numberToRender) {
            _updateIsotope();

            setTimeout(function() {
              _explorer.refresh();
              setTimeout(function() {
                $el.explorer.find('.beforeopen')
                  .removeClass('no-transition')
                  .removeClass('beforeopen');

                $el.body.addClass('loaded');
              });
            });

            Viewer.init();

            _explorer.fire('loaded');
          }
        });
        item.render('explorer-' + item.type());
      });

      _numbers.source.photos = data.source_number_photos || 0;
      _numbers.source.videos = data.source_number_videos || 0;

      if(_numberToRender === 0) {

        $el.container.append(_explorer.Templates['explorer-empty']());

        _numbers.cache.photos = 0;
        _numbers.cache.videos = 0;

        _explorer.updateToggleFilters();

        _updateIsotope();

        _explorer.fire('loaded');
      }
      else {

        for(var i = 0, len = data.albums.length; i < len; i++) {
          _album.add(_makeAlbum(data.albums[i]));
        }

        for(var i = 0, len = data.items.length; i < len; i++) {
          _album.add(_makeItem(data.items[i]));
        }

        _numbers.cache.photos = _album.count(window.PHOTOS_TYPES.PHOTO);
        _numbers.cache.videos = _album.count(window.PHOTOS_TYPES.VIDEO);
        _explorer.updateToggleFilters();
      }

      _hasInit = true;
    };

    function _slideFeatured(index, max, closeBefore) {
      closeBefore = closeBefore || false;

      if(closeBefore) {
        var previous = index - 1 > -1 ? index - 1 : max - 1,
            $previous = $($('.featured-item').get(previous));

        $previous.removeClass('open');
        $previous.find('.featured-mask').css('left', 0);
        $previous.find('.featured-description').removeClass('open');

        setTimeout(function() {
          $previous.find('.featured-title').removeClass('open');
        }, 200);

        setTimeout(function() {
          $previous.removeClass('activate');
          _slideFeaturedOpen(index, max);
        }, 1000);
      }
      else {
        _slideFeaturedOpen(index, max);
      }
    }

    function _slideFeaturedOpen(index, max) {
      var $index = $($('.featured-item').get(index)),
          width = $index.width(),
          height = $index.height(),
          maskBlurWidth = $index.find('.featured-mask-left').width(),
          previewBaseSize = {
            width: 1920,
            height: 1080
          },
          previewSize = {
            width: width + 150,
            height: Math.round((width + 150) * previewBaseSize.height / previewBaseSize.width)
          },
          previewPosition = {
            top: -Math.round((height + previewSize.height) / 2)
          };

      maskBlurWidth = Math.floor(maskBlurWidth - (maskBlurWidth * 57 / 100));

      $index.find('.featured-preview').css($.extend($.extend({}, previewSize), previewPosition));

      $index
        .addClass('activate')
        .addClass('open');

      $index.find('.featured-mask').css('left', $index.hasClass('odd') ? width - maskBlurWidth : -width + maskBlurWidth);

      setTimeout(function() {
        $index.find('.featured-title').addClass('open');
        setTimeout(function() {
          $index.find('.featured-description').addClass('open');
        }, 200);
      }, 800);

      if(max > 1) {
        setTimeout(function() {
          index++;
          index = index == max ? 0 : index;

          _slideFeatured(index, max, true);
        }, 7000);
      }
    }

    function _makeAlbum(album) {
      var tagsCls = album.tags.map(function(tag) {
        return 'tags-' + tag;
      }).join(' ');

      var identitiesCls = album.identities.map(function(identity) {
        return 'identities-' + identity;
      }).join(' ');

      var album = new Album({
        name: album.name,
        title: album.title,
        description: album.description,
        order: '0_' + (5000000000 - album.date),
        visible: typeof album.visible == 'undefined' || album.visible ? 'visible' : 'invisible',
        featured: album.featured ? 'featured' : '',
        url: album.url,
        poster: album.poster,
        date: album.date,
        number_photos: album.number_photos,
        number_videos: album.number_videos,
        _photos: album.number_photos < 2 ? 'photo' : 'photos',
        _videos: album.number_videos < 2 ? 'vidéo' : 'vidéos',
        search_filters: tagsCls + ' ' + identitiesCls
      });

      album.attachTemplate(_explorer.Templates, 'explorer-album', '.explorer');

      return album;
    }

    function _makeItem(item) {
      var tagsCls = item.tags.map(function(tag) {
        return 'tags-' + tag;
      }).join(' ');

      var identitiesCls = item.identities.map(function(identity) {
        return 'identities-' + identity;
      }).join(' ');

      var config = {
        type: item.type,
        name: item.name,
        order: '1_' + item.name,
        visible: typeof item.visible == 'undefined' || item.visible ? 'visible' : 'invisible',
        featured: item.featured ? 'featured' : '',
        src: item.fileurl,
        size: item.size,
        sizes: item.sizes,
        identities: item.identities,
        identitiesValue: item.identities.join(', '),
        tags: item.tags,
        tagsValue: item.tags.join(', '),
        search_filters: tagsCls + ' ' + identitiesCls
      };

      if(item.type == window.PHOTOS_TYPES.PHOTO) {
        item = new Photo(config);
        item.attachTemplate(_explorer.Templates, 'explorer-photo', '.explorer');
      }
      else if(item.type == window.PHOTOS_TYPES.VIDEO) {
        item = new Video(config);
        item.attachTemplate(_explorer.Templates, 'explorer-video', '.explorer');
      }

      return item;
    }

    this.path = function() {
      return _path;
    };

    this.refresh = function() {
      $el.explorer.isotope('reLayout');
    };

    function _updateIsotope() {
      _isotopeInitiated = true;
      $el.explorer.isotope({
        layoutMode: 'fitRows',
        getSortData: {
          order: function($elem) {
            return $elem.data('order');
          }
        }/*,
        filter: _filters.map(function(filter) {
          return '.' + filter;
        }).join('')*/
      });
      $el.explorer.isotope({sortBy: 'order'});
    }

    function _refreshFilters() {
      $el.explorer.isotope({filter: _filters.map(function(filter) {
        return '.' + filter;
      }).join('')});
    }

    this.addFilter = function(filter) {
      if($.inArray(filter, _filters) === -1) {
        _filters.push(filter);
        _refreshFilters();
      }
    };

    this.removeFilter = function(filter) {
      for(var i = 0; i < _filters.length; i++) {
        if(_filters[i] == filter) {
          _filters.splice(i, 1);
          _refreshFilters();
          break;
        }
      }
    };

    this.album = function() {
      return _album;
    };

    this.updateToggleFilters = function() {
      var numberPhotos = _numbers.cache.photos,
          numberVideos = _numbers.cache.videos;

      if(window.Page.role() == window.ROLES.ADMIN) {
        var numberPhotosSource = _numbers.source.photos,
            numberVideosSource = _numbers.source.videos;

        numberPhotos += '/' + numberPhotosSource + ' photos';
        numberVideos += '/' + numberVideosSource + ' vidéos';

        $('.but-filter-photos').attr('title', 'Photos générées / Photos dans le répertoire source');
        $('.but-filter-videos').attr('title', 'Vidéos générées / Vidéos dans le répertoire source');
      }
      else {
        numberPhotos = numberPhotos < 1 ? 'aucune photo' : (numberPhotos === 1 ? numberPhotos + ' photo' : numberPhotos + ' photos');
        numberVideos = numberVideos < 1 ? 'aucune vidéo' : (numberVideos === 1 ? numberVideos + ' vidéo' : numberVideos + ' vidéos');

        $('.but-filter-photos').attr('title', 'Affiche uniquement les photos du répertoire');
        $('.but-filter-videos').attr('title', 'Affiche uniquement les vidéo du répertoire');
      }

      $('.but-filter-photos').val(numberPhotos);
      $('.but-filter-videos').val(numberVideos);
    };

    this.addFile = function(file) {
      var returnItem = _album.add(_makeItem(file));

      _numbers.cache.photos = _album.count(window.PHOTOS_TYPES.PHOTO),
      _numbers.cache.videos = _album.count(window.PHOTOS_TYPES.VIDEO);

      _explorer.updateToggleFilters();

      return returnItem;
    };

    this.addAlbum = function(album) {
      var found = false,
          returnItem = null;

      $.each(_album.items(), function(index, item) {
        if(item.type() == 'album' && item.config().name == album.name) {
          found = true;

          var config = item.config();

          $.extend(true, config, album);
          config._photos = config.number_photos < 2 ? 'photo' : 'photos';
          config._videos = config.number_videos < 2 ? 'vidéo' : 'vidéos';

          var albumTemp = _makeAlbum(config);
          albumTemp.render('explorer-album')
          var details = albumTemp.view('explorer-album').find('.details').html();
          item.view('explorer-album').find('.details').html(details);
          returnItem = item;
        }
      });

      if(!found) {
        returnItem = _album.add(_makeAlbum(album));
      }

      return returnItem;
    };

    window.Page.on('roleChanged', function(args) {
      if(args.firstTime) {
        if(args.success) {
          for(var i = 0, len = args.invisibles.albums.length; i < len; i++) {
            var album = args.invisibles.albums[i];
            album.visible = false;
            _album.add(_makeAlbum(album));
          }
          for(var i = 0, len = args.invisibles.items.length; i < len; i++) {
            var item = args.invisibles.items[i];
            item.visible = false;
            _album.add(_makeItem(item));
          }
        }
      }
      else {
        if(args.role == window.ROLES.ADMIN) {
          _explorer.removeFilter('visible');
        }
        else {
          _explorer.addFilter('visible');
        }
      }

      _explorer.updateToggleFilters();
    });

    setTimeout(function() {
      _explorer.init();
    });

  })(window);

});