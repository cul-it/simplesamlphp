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
---

(Pantheon documentation)[https://pantheon.io/docs/private-paths/]  "Private Path for Files” says to put files in /files/private (symlinked by sites/default/files/private) and they stay out of source code control but are distributed to other environments (test/live) by the 'Clone Files' process.

However, the simplesamlphp /web directory needs to be web accessible. So, we're storing a copy of that directory from the simplesamlphp repo where we used to store the entire thing, in /code/private, as /code/private/www, then making the symlink "simplesaml" point to it. This /www directory & symlink will be included in the Pantheon git repo for each web site needing federated login.

The entire body of the simplesamlphp code has to be placed in /files/private/cul-it-simplesamlphp. This is a manual process using [sftp or rsync as described here](https://pantheon.io/docs/rsync-and-sftp/). Once the files are on dev, they can be moved to other environments via the Pantheon Database/Files > Clone Files command.

However, the /cert directory is not part of the cul-it/simplesamlphp repo, so that directory has to be manually added. (In practice, it’s easier to get a local copy of the repo, and add the /cert directory to it before the rsync.)

Customizations
==============

* simplesamlphp uses $_SERVER['SERVER_PORT'] to form the redirect URL it uses after the login is complete. This is set to strange numbers in code that's running on Pantheon, causing the redirect to fail. Here is some code to [Set SERVER_PORT Correctly](https://pantheon.io/docs/server_name-and-server_port/#set-server_port-correctly). This had to be placed in [settings.cornell.library.php](https://github.com/cul-it/pantheon-settings/blob/master/settings.cornell.library.php) and also in simplesamlphp in [/www/_include.php](https://github.com/cul-it/simplesamlphp/blob/master/www/_include.php).
* Since the simplesamlphp code is now split between code/private/www and files/private/cul-it-simplesamlphp, finding the library is tricky. We had to use the Pantheon siteroot variable $_ENV['HOME'] in [settings.cornell.library.php](https://github.com/cul-it/pantheon-settings/blob/master/settings.cornell.library.php), in simplesamlphp in [/www/_include.php](https://github.com/cul-it/simplesamlphp/blob/master/www/_include.php) and also in [/config/config.php](https://github.com/cul-it/simplesamlphp/blob/master/config/config.php).
* Use the [Shibboleth Integration Request](https://confluence.cornell.edu/x/3lHHF) documentation and [form to get metadata in xml for the dev & test sites](https://shibidp-test.cit.cornell.edu/idp/shibboleth). Convert that xml to php code using [the conversion tool](https://annex.library.cornell.edu/simplesaml/admin/metadata-converter.php), and insert the php into [/metadata/saml20-idp-remote.php.test](https://github.com/cul-it/simplesamlphp/blob/master/metadata/saml20-idp-remote.php.test)
* Use the [Shibboleth Integration Request](https://confluence.cornell.edu/x/3lHHF) documentation and [form to get metadata in xml for the live site](https://shibidp-test.cit.cornell.edu/idp/shibboleth). Convert that xml to php code using [the conversion tool](https://annex.library.cornell.edu/simplesaml/admin/metadata-converter.php), and insert the php into [/metadata/saml20-idp-remote.php.prod](https://github.com/cul-it/simplesamlphp/blob/master/metadata/saml20-idp-remote.php.prod)

How to use:
===========

1. If it's not already there, create a [mysite]/private directory in your Drupal site root directory (same place as Drupal's index.php).
2. Clone this repo into [somewhere]/cul-it-simplesamlphp.
3. Make a copy of [somewhere]/cul-it-simplesamlphp/www and put it in [mysite]/private/
4. Create a symlink in your Drupal site root directory called simplesaml that leads to /private/www. (1)
5. Add the /cert directory to [somewhere]/cul-it-simplesamlphp
6. Modify the shell script (2) for your site and rsync the /cul-it-simplesamlphp directory into your site at /files/private/cul-it-simplesamlphp
7. Add some code to your sites/default directory. Instructions for this are in the [pantheon-settings](https://github.com/cul-it/pantheon-settings) GitHub repo.
8. You'll need to download and install simplesamlphp_auth (and externalauth for Drupal 8) to get the Federated Login link.
9. For Drupal 8, add some code to the composer.json file in the document root directory. (3)
10. Push the changes to the remote Pantheon git repo.
11. Test at http://[sitename]/simplesaml/module.php/core/authenticate.php

(1)

```
ln -s ./private/www simplesaml
```

(2)

Example shell script code using rsync:

```
export ENV=dev
# Usually dev, test, or live
export SITE=[uuid]
# Site UUID from dashboard URL: https://dashboard.pantheon.io/sites/[uuid]

# To Upload/Import
rsync -rLvz --size-only --ipv4 --progress -e 'ssh -p 2222' ./cul-it-simplesamlphp --temp-dir=~/tmp/ $ENV.$SITE@appserver.$ENV.$SITE.drush.in:files/private/
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
|──────www
├── profiles
├── robots.txt
├── scripts
├── simplesaml -> private/www
├── sites
├── themes
├── update.php
├── web.config
└── xmlrpc.php
```

(3)
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
