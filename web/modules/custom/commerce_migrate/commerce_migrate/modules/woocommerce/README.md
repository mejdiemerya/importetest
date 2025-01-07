# Commerce Migrate WooCommerce

This provides example migrations and plugins to migrate WooCommerce data to
Drupal Commerce. There are migrations for taxonomy vocabulary, terms and
categories. These were created with a basic Woo Commerce site and, as such, will
require modification for more complex sites.

## Included fixtures files

This module includes a product export of WooCommerce data in CSV format. It was
created using the export options available in a standard Woo Commerce site, no
extra tools or plugins were used.

## Copy migrations to config/install

To copy the migrations to config/install for use with the Migrate Plus module
use the provided script, `cp_migrations`.

`$ ./scripts/cp_migrations woocommerce`
