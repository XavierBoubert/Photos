$(function() {
  'use strict';

  window.Explorer = window.Explorer || {};

  window.Explorer.Worker = new (function(window) {

    window.AbstractEvents.call(this);

    var _worker = this,
        _workerErrors = 0,
        $el = {
          butRole: $('#but-role')
        };

    function _closeWorkerBar() {
      $('.worker-bar').removeClass('visible');
      $('.element.album').removeClass('working');
    }

    function _progressWorkerBar(units, total, averageTime, averageItem) {
      averageTime = averageTime || '';
      averageItem = averageItem || '';

      if(averageTime) {
        averageTime = _formatTime(averageTime);
        averageTime = ' - ' + averageTime + ' restant';
      }

      if(averageItem) {
        averageItem = _formatTime(averageItem, true);
        averageItem = ' - ' + averageItem + ' par photo';
      }

      $('.worker-bar').addClass('visible');
      $('.worker-bar .progress').css('width', Math.round(units * 100 / total) + '%');

      var label = [
        'Traitement des nouvelles photos et vidéos (' + units + '/' + total
      ];

      if(window.Page.role() == window.ROLES.ADMIN) {
        label.push(averageTime);
        label.push(averageItem);
      }
      label.push(')');

      $('.worker-bar .label').html(label.join(''));
    }

    function _formatTime(time, detailed) {
      detailed = detailed || false;

      time /= 1000;
      var hours = parseInt(time / 3600, 10) % 24,
          minutes = parseInt(time / 60, 10) % 60,
          seconds = Math.floor(time % 60);

      if(time < 60 && !detailed) {
        return 'moins d\'une minute';
      }

      time = (hours > 0 ? hours + 'h ' : '') + (minutes > 0 ? minutes + 'min' : '');

      if(!detailed) {
        return time.trim();
      }

      return (time + ' ' + (seconds > 0 ? seconds + 'sec' : '')).trim();
    }

    var _stop = false,
        _times = [];

    function _photosWorker(firstTime) {
      firstTime = firstTime || false;

      var startTime = new Date();

      $.ajax({
        url: '/api/photos-worker',
        data: {
          url: window.Page.rootUrl(),
          path: window.Explorer.path(),
          firstTime: firstTime
        },
        dataType: 'json',
        success: function(data) {
          _workerErrors = 0;

          if(data.success && data.status != 'idle') {
            data.total_done = data.work.number_photos + data.work.number_videos;

            var averageTime = 0,
                averageItem = 0;

            if(!firstTime) {
              _times.push(new Date().getTime() - startTime.getTime());

              var nbTimes = Math.min(_times.length, 3);
              for(var i = _times.length - 1, len = _times.length - nbTimes; i >= len; i--) {
                averageItem += _times[i];
              }
              averageItem /= nbTimes;

              averageTime = averageItem * (data.work.total_to_make - data.total_done);
            }

            $('.info-column.number-albums span').html(data.work.number_albums_visible);
            $('.info-column.number-photos span').html(data.work.number_photos_visible);
            $('.info-column.number-videos span').html(data.work.number_videos_visible);
            $('.info-column.last-update span').html(data.work.last_update);

            if(!_stop) {
              _progressWorkerBar(data.total_done, data.work.total_to_make, averageTime, averageItem);
            }

            $('.element.album').removeClass('working');

            if(data.newFile) {
              window.Explorer.addFile(data.newFile);
            }
            else if(data.newAlbum) {
              var album = window.Explorer.addAlbum(data.newAlbum);
              album.view('explorer-album').addClass('working');
            }

            if(_stop) {
              _closeWorkerBar();
            }
            else {
              _photosWorker();
            }

          }
          else {
            _closeWorkerBar();
          }
        },
        error: function() {
          _workerErrors++;
          if(_workerErrors < 3) {
            _photosWorker();
          }
          else {
            _closeWorkerBar();
          }
        }
      });
    }

    this.start = function() {
      _stop = false;
      $('.worker-bar').removeClass('stopped');
      _photosWorker(true);
    };

    this.stop = function() {
      _stop = true;
    };

    if($el.butRole.length > 0) {
      if(window.Explorer.on) {
        window.Explorer.on('loaded', function() {
          _worker.start();
        });
      }
      else {
        _worker.start();
      }
    }

    $('.worker-bar .stop').click(function() {
      $('.worker-bar').addClass('stopped');
      _worker.stop();
      $('.worker-bar .label').html('Arret en cours...');
    });

  })(window);

});