CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Remote Config Sync module allows you to push the configuration files from
development to production site with a click of a button. By using this module
you don't have to waste your time with the manual export/import process.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/remote_config_sync

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/remote_config_sync


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

* Install the Remote Config Sync module as you would normally install a
  contributed Drupal module. Visit https://www.drupal.org/node/1897420 for
  further information.


CONFIGURATION
-------------

    1. Create your DEVELOPMENT site.
    2. Copy your DEVELOPMENT site to your PRODUCTION server. This will ensure
    that the sites UUIDs are the same.
    3. Install the Remote Config Sync module on both sites.
    4. On your DEVELOPMENT site go to the Remotes page:
    'admin/config/development/remote-config-sync/remotes' and enter the URL of
    your PRODUCTION site and copy the security token from your PRODUCTION.
    Settings page that can be found here:
    'admin/config/development/remote-config-sync/settings'.
    5. You can start pushing the configuration from DEVELOPMENT site to
    PRODUCTION.


MAINTAINERS
-----------

Current maintainers:
 * Goran Nikolovski (gnikolovski) - https://www.drupal.org/u/gnikolovski

This project has been sponsored by:
 * Studio Present - https://www.drupal.org/studio-present
