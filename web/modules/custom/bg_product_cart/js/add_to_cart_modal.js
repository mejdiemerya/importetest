(function ($, Drupal, once) {
  Drupal.behaviors.addToCartModal = {
    attach: function (context, settings) {
      const modalEl = document.getElementById('customAddToCartModal');

      if (!modalEl) return;

      once('add-to-cart-modal', modalEl, context).forEach(function () {
        $(modalEl).fadeIn();

        // Supprimer le paramètre show_modal de l’URL après affichage
        const url = new URL(window.location);
        if (url.searchParams.has('show_modal')) {
          url.searchParams.delete('show_modal');
          window.history.replaceState({}, '', url.toString());
        }
      });

      // Fermeture manuelle
      once('add-to-cart-close', '[data-close-modal]', context).forEach(function (btn) {
        $(btn).on('click', function () {
          $('#customAddToCartModal').fadeOut();
        });
      });
    }
  };
})(jQuery, Drupal, once);
