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
          butRole: $('#but-role')
        };

    this.role = function() {
      return _role;
    };

    this.rootUrl = function() {
      return _photosRoot;
    }

    $el.window.scroll(function(e) {
      $el.bannerCover.css({'top': $el.window.scrollTop() * 0.4});
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

    $el.butRole.click(function() {
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
              _page.fire('roleChanged', data);
            }
          });

          return;
        }
      }

      _page.fire('roleChanged', {
        firstTime: false,
        role: role
      });
    };

  })(window);

});