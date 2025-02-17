(function (Drupal, $, once) {
  Drupal.behaviors.forgotPassword = {
    attach: function (context, settings) {
      once('forgotPassword', '#forgot-password-link', context).forEach((element) => {
        $(element).on('click', function (e) {
          e.preventDefault();
          $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
              step: 3,
              form_id: 'membership_basic_signup_form',
              _drupal_ajax: 1,
            },
            success: function (response) {
              const updatedForm = $(response).find('#signup-form-wrapper');
              if (updatedForm.length) {
                $('#signup-form-wrapper').replaceWith(updatedForm);
              }
            }
          });
        });
      });
    }
  };
})(Drupal, jQuery, once);
