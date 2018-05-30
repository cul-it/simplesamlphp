SimpleSAMLphp
=============
[![Build Status](https://travis-ci.org/simplesamlphp/simplesamlphp.svg?branch=master)](https://travis-ci.org/simplesamlphp/simplesamlphp)
[![Coverage Status](https://img.shields.io/coveralls/simplesamlphp/simplesamlphp.svg)](https://coveralls.io/r/simplesamlphp/simplesamlphp)

This is a fork of the official repository of the SimpleSAMLphp software.

* [SimpleSAMLphp homepage](https://simplesamlphp.org)
* [SimpleSAMLphp Downloads](https://simplesamlphp.org/download)

Please, [contribute](CONTRIBUTING.md)!

=============

Cornell University Library modifications

We started after version 1.15.4.


How to use:
===========

1. If it's not already there, create a /private directory in your Drupal site root directory (same place as Drupal's index.php).
2. Download the .zip version of this repo and extract it into /private/cul-it-simplesamlphp. This readme file should end up at /private/cul-it-simplesamlphp/README.md
3. Create a symlink in your Drupal site root directory called simplesaml that leads to the www directory inside /private/cul-it-simplesamlphp. (1)
4. Add some code to your sites/default directory. Instructions for this are in the [pantheon-settings](https://github.com/cul-it/pantheon-settings) GitHub repo.
5. You'll need to download and install simplesamlphp_auth (and externalauth for Drupal 8) to get the Federated Login link.
6. For Drupal 8, add some code to the composer.json file in the document root directory. (2)
7. Push the changes to the remote Pantheon git repo.
8. Test at http://[sitename]/simplesaml/module.php/core/authenticate.php

(1)

```
ln -s ./private/cul-it-simplesamlphp/www simplesaml
```

Your Drupal directory should end up looking like this:

```
.
├── CHANGELOG.txt
├── COPYRIGHT.txt
├── INSTALL.mysql.txt
├── INSTALL.pgsql.txt
├── INSTALL.sqlite.txt
├── INSTALL.txt
├── LICENSE.txt
├── MAINTAINERS.txt
├── README.txt
├── UPGRADE.txt
├── authorize.php
├── cron.php
├── includes
├── index.php
├── install.php
├── misc
├── modules
├── pantheon.yml
├── private
├── profiles
├── robots.txt
├── scripts
├── simplesaml -> private/cul-it-simplesamlphp/www
├── sites
├── themes
├── update.php
├── web.config
└── xmlrpc.php
```

(2)
```
{
    ...
    "extra": {
        ...
        "merge-plugin": {
            "include": [
                "core/composer.json"
            ],
+           "require": [
+               "private/cul-it-simplesamlphp/composer.json"
+           ],
            "recurse": true,
            "replace": false,
            "merge-extra": false
        },
        ...
    }
    ...
}

+ add these three lines (without the plus signs) to
[docroot]/composer.json
then run

$> composer install

```


Details:
===========
There are 3 files that differ depending on whether you're in production or non-production (test) mode:

*   config/authsources.php
*   metadata/shib13-idp-remote.php
*   metadata/saml20-idp-remote.php

Each of these files has a default [filename].default, test [filename].test, and production [filename].prod version.

The files themselves contain logic to inclued one of the 3 versions depending on the state of some Pantheon environment variables.

The production version is only used on the live Pantheon site, and only WORKs when you use the official domain name of the site. (It works on https://hlm.library.cornell.edu, but not on https://live-hlmlibrarycornelledu.pantheonsite.io)
