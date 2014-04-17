$(function() {
  'use strict';

  function _clearError() {
    $('.passwords-error')
      .css('display', 'none')
      .html('');
  }

  function _showError(error) {
    $('.passwords-error')
      .html(error)
      .css('display', 'block');
  }

  $('#passwordsform').on('submit', function() {

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

        $('.passwords-result').removeClass('visible');
        setTimeout(function() {
          $('.passwords-result-data').html(data.password);
          $('.passwords-result').addClass('visible');
        });

      },
      error: function() {
        _showError('Problème de connexion avec le serveur.');
      }
    });

    return false;

  });

  setTimeout(function() {
    $('#password').focus();
  }, 2000);

});