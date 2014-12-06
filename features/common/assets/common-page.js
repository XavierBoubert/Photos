$(function() {
  'use strict';

  window.ROLES = {
    VISITOR: 'VISITOR',
    ADMIN: 'ADMIN'
  };

  window.ROLESNAMES = {
    VISITOR: 'visiteur',
    ADMIN: 'admin'
  };

  window.Page = new (function(window) {

    window.AbstractEvents.call(this);

    var _page = this,
        _role = window.ROLES.VISITOR,
        _firstChangeToAdmin = true,
        _photosRoot = document.location.pathname,
        $el = {
          window: $(window),
          body: $(document.body),
          bannerCover: $('.banner-cover'),
          butRole: $('#but-role'),
          butRoleLoading: $('#but-role-loading')
        };

    this.role = function() {
      return _role;
    };

    this.rootUrl = function() {
      return _photosRoot;
    };

    $el.window.scroll(function(e) {
      var top = 0;
      if($el.body.width() > 1000) {
        top = $el.window.scrollTop() * 0.4;
      }
      $el.bannerCover.css({'top': top});
    });

    $('.but-menu').click(function() {
      if($('#but-logout').css('display') == 'block') {
        $('.banner-menu-select').css('display', 'none');
        $('#but-logout').css('display', 'none');
      }
      else {
        $('.banner-menu-select').css('display', 'block');
        $('#but-logout').css('display', 'block');
      }
    });

    function _startRoleLoading() {
      $el.butRole.addClass('hide');
      $el.butRoleLoading.addClass('visible');
    }

    function _stopRoleLoading() {
      $el.butRoleLoading.removeClass('visible');
      $el.butRole.removeClass('hide');
    }

    $el.butRole.click(function() {
      _startRoleLoading();

      $el.butRole.removeClass(_role.toLowerCase());
      $el.body.removeClass('role-' + _role.toLowerCase());
      _role = _role == window.ROLES.VISITOR ? window.ROLES.ADMIN : window.ROLES.VISITOR;

      _page.changeRole(_role);

      $el.butRole.val(window.ROLESNAMES[_role]);
      $el.butRole.addClass(_role.toLowerCase());
      $el.body.addClass('role-' + _role.toLowerCase());
    });

    $('#but-logout').click(function() {
      $.ajax({
        url: '/api/logout',
        dataType: 'json',
        success: function(data) {
          location.reload();
        },
        error: function() {
          location.reload();
        }
      });
    });

    this.changeRole = function(role) {
      if(role == window.ROLES.ADMIN) {
        if(_firstChangeToAdmin) {
          _firstChangeToAdmin = false;

          $.ajax({
            url: '/api/role-admin',
            data: {
              path: _photosRoot
            },
            dataType: 'json',
            success: function(data) {
              data.firstTime = true;
              data.role = role;
              data.callback = function() {
                _stopRoleLoading();
              }
              _page.fire('roleChanged', data);
            },
            error: function() {
              _stopRoleLoading();
            }
          });

          return;
        }
      }

      _page.fire('roleChanged', {
        firstTime: false,
        role: role
      });

      _stopRoleLoading();
    };

  })(window);

});