# Commerce Migrate Shopify

This provides example migrations and plugins to migrate Shopify data to Drupal
Commerce. There are migrations for products and taxonomy. These were created
with a basic Shopify site and, as such, will require modification for more
complex sites.

## Included fixtures files

This module includes a product export of Shopify data in CSV format. It was
created using the export options available in a standard Shopify site, no extra
tools or plugins were used.

## Copy migrations to config/install

To copy the migrations to config/install for use with the Migrate Plus module
use the provided script, `cp_migrations`.

`$ ./scripts/cp_migrations shopify`
