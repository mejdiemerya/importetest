(function (Drupal, once) {
  Drupal.behaviors.forgotPasswordLink = {
    attach(context) {
      once('forgot-password-handler', '#forgot-password-link', context).forEach(function (link) {
        link.addEventListener('click', function (e) {
          e.preventDefault();
          const submitButton = document.getElementById('forgot-password-submit');
          if (submitButton) {
            submitButton.click();
          }
        });
      });
    }
  };
})(Drupal, once);
