(function (Drupal, once) {
  Drupal.behaviors.submitOnEnter = {
    attach: function (context, settings) {
      // Utilisez la méthode once() pour éviter les doublons.
      once('submit-on-enter', '#keyword-search-field', context).forEach(function (form) {
        form.addEventListener('keypress', function (e) {
          if (e.key === 'Enter' || e.keyCode === 13) {
            // Force un clic sur le bouton avec l'ID spécifique.
            const button = document.getElementById('edit-show-results');
            if (button) {
              button.click();
              e.preventDefault();
            }
          }
        });
      });
    }
  };
})(Drupal, once);
