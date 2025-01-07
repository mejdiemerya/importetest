# Commerce Migrate Magento

This provides example migrations and plugins to migrate Magento 2 data to Drupal
Commerce. There are migrations for products, categories, images and user
profiles. These were created with a basic Magento site and, as such, will
require modification for more complex sites.

## Included fixtures files

This module includes files of exported Magento data in CSV format. They were
created using the export options available in a standard Magento site, no extra
tools or plugins were used.

## Copy migrations to config/install

To copy the migrations to config/install for use with the Migrate Plus module
use the provided script, `cp_migrations`.

`$ ./scripts/cp_migrations magento`
