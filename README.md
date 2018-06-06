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
===

We forked the [simplesamlphp](https://github.com/simplesamlphp/simplesamlphp) development version somewhere after version 1.15.4.

/cert
---

Identity Management now requires a certificate to encrypt the communication. The certificate has to be stored in the /cert directory in the simplesamlphp top directory, however this directory is excluded by .gitignore, because it contains the private key.
See the [simplesamlphp documentation on the certificate](https://simplesamlphp.org/docs/stable/simplesamlphp-sp#section_1_1)

(I’ve created a /cert directory for this purpose, and stored it on my local disk until I can find a better place for it.)

Storing private files in Pantheon:

(Pantheon documentation)[https://pantheon.io/docs/private-paths/]  "Private Path for Files” says to put files in /files/private (symlinked by sites/default/files/private) and they stay out of source code control but are distributed to other environments (test/live) by the 'Clone Files' process.

However, the simplesamlphp /web directory needs to be web accessible. So, we're storing a copy of that directory from the simplesamlphp repo where we used to store the entire thing, in /code/private, as /code/private/www, then making the symlink "simplesaml" point to it. This /www directory & symlink will be included in the Pantheon git repo for each web site needing federated login.

The entire body of the simplesamlphp code has to be placed in /files/private/cul-it-simplesamlphp. This is a manual process using [sftp or rsync as described here](https://pantheon.io/docs/rsync-and-sftp/). Once the files are on dev, they can be moved to other environments via the Pantheon Database/Files > Clone Files command.

Example shell script code using rsync:

```
export ENV=dev
# Usually dev, test, or live
export SITE=[uuid]
# Site UUID from dashboard URL: https://dashboard.pantheon.io/sites/[uuid]

# To Upload/Import
rsync -rLvz --size-only --ipv4 --progress -e 'ssh -p 2222' ./cul-it-simplesamlphp --temp-dir=~/tmp/ $ENV.$SITE@appserver.$ENV.$SITE.drush.in:files/private/
```

However, the /cert directory is not part of the cul-it/simplesamlphp repo, so that directory has to be manually added. (In practice, it’s easier to get a local copy of the repo, and add the /cert directory to it before the rsync.)

Customizations of simplesamlphp

Pantheon port problem

Setting up metadata 
https://annex.library.cornell.edu/simplesaml/module.php/saml/sp/metadata.php/default-sp?output=xhtml


Old version of readme below is being replaced
===========
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
