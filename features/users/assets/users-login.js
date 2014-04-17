$(function() {
  'use strict';

  function _clearError() {
    $('.login-error')
      .removeClass('show')
      .html('');
  }

  function _showError(error) {
    $('.login-error')
      .html(error)
      .addClass('show');
  }

  $('#loginform').on('submit', function() {

    _clearError();

    $.ajax({
      url: $(this).attr('action'),
      type: $(this).attr('method'),
      data: $(this).serialize(),
      dataType: 'json',
      success: function(data) {
        if(!data.success) {
          _showError(data.error);
          return;
        }

        $('.login-background').addClass('close');
        $('.login-form').addClass('close');

        setTimeout(function() {
          location.reload();
        }, 1000);

      },
      error: function() {
        _showError('Problème de connexion avec le serveur.');
      }
    });

    return false;

  });

  setTimeout(function() {
    $('#user_login').focus();
  }, 500);

  $('.login-background').addClass('show');

  setTimeout(function() {
    $('.login-form').addClass('show');
  }, 200);

});