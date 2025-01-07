# Commerce Migrate Ubercart

This provides the necessary plugins to migrate a Drupal 6 or Drupal 7 site
using Ubercart to Drupal 8 with Commerce v2.

When using either the core Migrate Drupal UI interface or [Migrate
Upgrade](https://www.drupal.org/project/migrate_upgrade) to upgrade a Drupal 6
or 7 site, the Ubercart configuration and data will be automatically upgraded to
Commerce v2.

## List of supported functionality

The following will be migrated from a Drupal 6 or Drupal 7 site:

* Attributes
* Billing profiles
* Currencies
* Languages
* Orders
* Payments
* Product variations
* Products and translated products
* Stores
* Tax types

## Contributing

For any issues that are identified with the migration system, please review the [project's issue queue](https://www.drupal.org/project/issues/commerce_migrate).

### Included fixture file

This module includes two [test fixture files](https://www.drupal.org/docs/8/api/migrate-api/generating-database-fixtures-for-d8-migrate-tests), one each for the Drupal 6 and Drupal 7 releases of [Ubercart](https://www.drupal.org/project/ubercart).

The fixtures use the following Ubercart versions.

* [Ubercart v6.x-2.14](https://www.drupal.org/project/ubercart/releases/6.x-2.14).
* [Ubercart v7.x-3.1](https://www.drupal.org/project/ubercart/releases/7.x-3.1).

### Loading the fixture file

The easiest way to build the codebase for Ubercart 7 is as follows:

* Use the [Drush Make](https://docs.drush.org/en/8.x/make/) file, uc7.make, in the tests/fixtures directory.

Alternatively, the codebase may be prepared manually as follows:

* Download the relevant release of [Ubercart](https://www.drupal.org/project/ubercart/releases).
* Download the following Drupal modules:
  * For Drupal 6:
    * [Content Construction Kit (CCK)](https://www.drupal.org/project/cck)
    * [Email](https://www.drupal.org/project/email)
    * [FileField](https://www.drupal.org/project/filefield)
    * [Internationalization](https://www.drupal.org/project/i18n)
    * [Image API](https://www.drupal.org/project/imageapi)
    * [ImageField](https://www.drupal.org/project/imagefield)
    * [Link](https://www.drupal.org/project/link)
    * [Phone](https://www.drupal.org/project/phone)
    * [Token](https://www.drupal.org/project/token)
    * [Views](https://www.drupal.org/project/views)
  * For Drupal 7:
    * [Chaos Tool Suite](https://www.drupal.org/project/ctools)
    * [Entity API](https://www.drupal.org/project/entity)
    * [Rules](https://www.drupal.org/project/rules)
    * [Token](https://www.drupal.org/project/token)
    * [Ubercart Discount Coupons](https://www.drupal.org/project/uc_coupon)
    * [Views](https://www.drupal.org/project/views)

Once the codebase is ready, follow these instructions to load the exported
configuration (fixture) file:

* Prepare a web server to run the codebase, complete with a blank database.
  Note: Do not install Drupal or Ubercart.
* In an installation of Drupal 8, add a `$databases` array structure that
  points to the database created above for Ubercart; note the array
  key that defines the database, this will be used below.
* Use the `core/scripts/db-tools.php` script in Drupal 8 to load the
  `uc6.php` or `uc7.php` file from this module's codebase, for example,
  * `php core/scripts/db-tools.php import --database=NAMEOFDATABASE path/to/commerce_migrate/modules/commerce/tests/fixtures/uc7.php`
  * The "NAMEOFDATABASE" item above should be replaced with the key
    from the $databases array added to settings.php above.
* After a moment it should output `Import completed successfully.` If it does
  not, look for error messages indicating problems to resolve.

### Updating the fixture file

After making changes to the Ubercart installation created above, update the
fixture file so that changes can be included in a patch on the issue queue.

* Use Drupal 8 `db-tools.php` script to export the database, for example,
  * `php core/scripts/db-tools.php dump --database=NAMEOFDATABASE > path/to/commerce_migrate/modules/commerce/tests/fixtures/uc7.php`
  * The NAMEOFDATABASE item above should be replaced with the key from the
  * $databases array added to settings.php above.
* Use standard contribution processes to [create a patch](https://www.drupal.org/node/707484) of the differences in this file versus the one downloaded from drupal.org; make sure that the changes exported were all intended to be exported and that unnecessary changes were not included.
