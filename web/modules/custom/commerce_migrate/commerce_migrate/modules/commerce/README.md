# Commerce Migrate Commerce

This provides the necessary plugins to migrate a Commerce v1 site to Drupal 8.
When using either core's Migrate Drupal UI interface or [Migrate
Upgrade](https://www.drupal.org/project/migrate_upgrade) to upgrade a Drupal 7
site, the Commerce v1 configuration and data will be automatically upgraded to
Commerce v2.

## List of supported functionality

The following will be migrated from a Drupal 7 site:

* Attributes
* Currencies
* Default store selection
* Messages
* Orders
* Order items
* Payments
* Payment gateways
* Products
* Product attributes
* Product types
* Product variations
* Product variation types
* Products
* Profiles
* Shipping flat rates
* Tax types

## Contributing

For any issues that are identified with the migration system, please review the [project's issue queue](https://www.drupal.org/project/issues/commerce_migrate).

### Included fixture file

This module includes a [test fixtures file](https://www.drupal.org/docs/8/api/migrate-api/generating-database-fixtures-for-d8-migrate-tests) which contains a database dump from a [Commerce Kickstart 2](https://www.drupal.org/project/commerce_kickstart) site.

The fixture file was built using [Commerce Kickstart v7.x-2.67](https://www.drupal.org/project/commerce_kickstart/releases/7.x-2.67).

### Loading the fixture file

The easiest way to build the codebase for Commerce Kickstart v2 is as follows:

* Use the [Drush Make](https://docs.drush.org/en/8.x/make/) file, ck2.make, in the tests/fixtures directory.

Alternatively, the codebase may be prepared manually as follows:

* Download the latest release of [Commerce Kickstart v2](https://www.drupal.org/project/commerce_kickstart) (not v1!).
* Prepare a web server to run the codebase, complete with a blank database.
  Note: Do not install Drupal or Commerce Kickstart.
* In an installation of Drupal 8, add a `$databases` array structure that
  points to the database created above for Commerce Kickstart; note the array
  key that defines the database, this will be used below.
* Use the `core/scripts/db-tools.php` script in Drupal 8 to load the
  `ck2.php` file from this module's codebase, for example,
  * `php core/scripts/db-tools.php import --database=NAMEOFDATABASE path/to/commerce_migrate/modules/commerce/tests/fixtures/ck2.php`
  * The "NAMEOFDATABASE" item above should be replaced with the key
    from the $databases array added to settings.php above.
* After a moment it should output `Import completed successfully.` If it does
  not, look for error messages indicating problems to resolve.

### Updating the fixtures file

After making changes to the Commerce Kickstart installation created above, the
fixtures file may be updated in order to provide changes for a patch on the
issue queue.

* Use the Drupal `db-tools.php` script to export the database, for example,
  * `php core/scripts/db-tools.php dump --database=NAMEOFDATABASE > path/to/commerce_migrate/modules/commerce/tests/fixtures/ck2.php`
  * The "NAMEOFDATABASE" item above should be replaced with the key
    from the $databases array added to settings.php above.
* Use standard contribution processes to [create a patch](https://www.drupal.org/node/707484) of the differences in this file versus the one downloaded from drupal.org; make sure that the changes  exported were all intended to be exported and that unnecessary changes were not included.
