# Built with Drush 8.4.9-dev.
core: 7.x
api: 2
projects:
  drupal:
    version: 7.74
  commerce_kickstart:
    type: module
    version: '2.68'
  views_bulk_operations:
    version: '3.5'
  addressfield:
    version: '1.3'
  admin_views:
    version: '1.7'
  advanced_help:
    version: '1.5'
  service_links:
    version: 2.x-dev
  ctools:
    version: '1.15'
  chosen:
    version: '2.1'
  cloud_zoom:
    version: 1.x-dev
  commerce:
    version: '1.15'
  commerce_add_to_cart_confirmation:
    version: 1.0-rc3
  commerce_addressbook:
    version: 2.0-rc9
  commerce_amex:
    version: '1.1'
  commerce_autosku:
    version: '1.2'
  commerce_backoffice:
    version: '1.5'
  commerce_checkout_progress:
    version: '1.5'
  commerce_checkout_redirect:
    version: '2.0'
  commerce_discount:
    version: 1.0-beta5
  commerce_extra_price_formatters:
    version: 1.x-dev
  commerce_fancy_attributes:
    version: '1.0'
  commerce_features:
    version: '1.3'
  commerce_flat_rate:
    version: 1.0-beta2
  commerce_message:
    version: '1.0'
  commerce_migrate:
    version: '1.2'
    patch:
      - 'https://www.drupal.org/files/issues/reference_fields_should-2701333-3.patch'
  commerce_search_api:
    version: '1.6'
  commerce_shipping:
    version: '2.3'
  connector:
    version: 1.0-beta2
  countries:
    version: '2.3'
  crumbs:
    version: '1.10'
  facetapi:
    version: '1.6'
  date:
    version: '2.10'
  distro_update:
    version: 1.0-beta4
  entity:
    version: '1.9'
  entityreference:
    version: '1.5'
  eva:
    version: '1.4'
  features:
    version: '2.11'
    patch:
      - 'http://drupal.org/files/issues/features-fix-modules-enabled-2143765-1.patch'
      - 'https://www.drupal.org/files/issues/ignore_hidden_modules-2479803-1.patch'
  fences:
    version: '1.2'
  field_extractor:
    version: '1.3'
  http_client:
    version: '2.4'
  image_delta_formatter:
    version: 1.0-rc1
  inline_conditions:
    version: '1.0'
  inline_entity_form:
    version: '1.8'
  libraries:
    version: '2.5'
  link:
    version: '1.6'
  mailsystem:
    version: '2.35'
  menu_attributes:
    version: '1.0'
  message:
    version: '1.12'
  message_notify:
    version: '2.5'
  migrate:
    version: '2.11'
  migrate_extras:
    version: '2.5'
    patch:
      - 'http://drupal.org/files/migrate_extras-fix-destid2-array-1951904-4.patch'
  mimemail:
    version: '1.1'
  module_filter:
    version: '2.2'
  oauthconnector:
    version: 1.0-beta2
  oauth:
    version: '3.4'
  pathauto:
    version: '1.3'
  rules:
    version: '2.12'
  search_api:
    version: '1.26'
  search_api_db:
    version: '1.7'
  search_api_ranges:
    version: '1.5'
    patch:
      - 'https://drupal.org/files/issues/search_api_ranges-rewrite-data-alteration-callback-2001846-4.patch'
  search_api_sorts:
    version: '1.7'
  special_menu_items:
    version: '2.1'
  strongarm:
    version: '2.0'
  taxonomy_menu:
    version: '1.6'
  title:
    version: 1.0-beta3
    patch:
      - 'https://www.drupal.org/files/issues/title-fix_description_empty_on_submit-2075041-7.patch'
  token:
    version: '1.7'
    patch:
      - 'http://drupal.org/files/token-token_asort_tokens-1712336_0.patch'
  views:
    version: '3.23'
  views_megarow:
    version: '1.7'
  omega:
    version: '3.1'
  omega_kickstart:
    version: '3.4'
  shiny:
    version: '1.7'
libraries:
  jquery.bxslider:
    directory_name: jquery.bxslider
    type: library
  selectnav.js:
    directory_name: selectnav.js
    type: library
  jquery_expander:
    directory_name: jquery_expander
    type: library
  colorbox:
    directory_name: colorbox
    type: library
  cloud-zoom:
    directory_name: cloud-zoom
    type: library
  jquery_ui_spinner:
    directory_name: jquery_ui_spinner
    type: library
  yotpo-php:
    directory_name: yotpo-php
    type: library
  chosen:
    directory_name: chosen
    type: library
  paymill:
    directory_name: paymill
    type: library
