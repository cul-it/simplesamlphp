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

Identity Management now requires a certificate to encrypt the communication. The certificate is usually stored in the /cert directory in the simplesamlphp top directory, however this directory is excluded by .gitignore, because it contains the private key.
See the [simplesamlphp documentation on the certificate](https://simplesamlphp.org/docs/stable/simplesamlphp-sp#section_1_1)

(I’ve created a /cert directory for this purpose, and stored it in a LastPass secure note.)

Storing private files in Pantheon:
---

[Pantheon documentation](https://pantheon.io/docs/private-paths/)  "Private Path for Files” says to put files in /files/private (symlinked by sites/default/files/private) and they stay out of source code control but are distributed to other environments (test/live) by the 'Clone Files' process.

Moving files to /files/private is a manual process using [sftp or rsync as described here](https://pantheon.io/docs/rsync-and-sftp/). Since it's slow, we're only putting the /cert directory here: [pantheon-site-path]/files/private/cul-it-simplesaml/cert
Once the files are on dev, they can be moved to other environments via the Pantheon Database/Files > Clone Files command.

The simplesamlphp /web directory needs to be web accessible, so we're placing this repo here:
[pantheon-site-path]/code/private/cul-it-simplesamlphp
(When you use git to download a Pantheon site, you're downloading [pantheon-site-path]/code.)

Simplesamlphp uses composer to manage dependencies. Drupal 8 also uses composer, but Drupal 7 does not. To keep from requiring the use of composer everywhere, we include the cul-it-simplesaml/vendor directory and use the autoload.php from that. This may require a modification to the .gitignore that comes with Drupal.

Customizations
==============

* Originally, /config/ and /metadata/ were excluded by .gitignore. We have included them in this repo to hold customizations for CUL.
* simplesamlphp uses $_SERVER['SERVER_PORT'] to form the redirect URL it uses after the login is complete. This is set to strange numbers in code that's running on Pantheon, causing the redirect to fail. Here is some code to [Set SERVER_PORT Correctly](https://pantheon.io/docs/server_name-and-server_port/#set-server_port-correctly). This had to be placed in [settings.cornell.library.php](https://github.com/cul-it/pantheon-settings/blob/master/settings.cornell.library.php) and also in simplesamlphp in [/www/_include.php](https://github.com/cul-it/simplesamlphp/blob/master/www/_include.php).
* Since the simplesamlphp code is now split between code/private/www and files/private/cul-it-simplesamlphp, finding the library is tricky. We had to use the Pantheon siteroot variable $_ENV['HOME'] in [settings.cornell.library.php](https://github.com/cul-it/pantheon-settings/blob/master/settings.cornell.library.php), in simplesamlphp in [/www/_include.php](https://github.com/cul-it/simplesamlphp/blob/master/www/_include.php) and also in [/config/config.php](https://github.com/cul-it/simplesamlphp/blob/master/config/config.php).
* Use the [Shibboleth Integration Request](https://confluence.cornell.edu/x/3lHHF) documentation and form to [get metadata in xml for the dev & test sites](https://shibidp-test.cit.cornell.edu/idp/shibboleth). Convert that xml to php code using [the conversion tool](https://annex.library.cornell.edu/simplesaml/admin/metadata-converter.php), and insert the php into [/metadata/saml20-idp-remote.php.test](https://github.com/cul-it/simplesamlphp/blob/master/metadata/saml20-idp-remote.php.test)
* Use the [Shibboleth Integration Request](https://confluence.cornell.edu/x/3lHHF) documentation and form to [get metadata in xml for the live site](https://shibidp-test.cit.cornell.edu/idp/shibboleth). Convert that xml to php code using [the conversion tool](https://annex.library.cornell.edu/simplesaml/admin/metadata-converter.php), and insert the php into [/metadata/saml20-idp-remote.php.prod](https://github.com/cul-it/simplesamlphp/blob/master/metadata/saml20-idp-remote.php.prod)

How to use:
===========

1. If it's not already there, create a [mysite]/private directory in your Drupal site root directory (same place as Drupal's index.php).
2. You can modify this shell script (5) to eliminate steps 3-6 below.
3. Clone this repo into [mysite]/private/cul-it-simplesamlphp, then delete the .git directory [mysite]/private/cul-it-simplesamlphp/.git
4. Remove the old version of the code from /private/pantheon-simplesaml
5. Create a symlink in your Drupal site root directory called simplesaml that leads to /private/cul-it-simplesamlphp/www. (1)
6. Add some code to your sites/default directory. Instructions for this are in the [pantheon-settings](https://github.com/cul-it/pantheon-settings) GitHub repo.
7. If the domain name of the site does NOT follow the pattern xxx.library.cornell.edu (eg. copyright.cornell.edu), update the $settings['trusted_host_patterns'] array in settings.cornell.library.php.
8. For Drupal 8, add some code to the composer.json file in the document root directory. (3) Then run composer update --lock
9. For Drupal 7, *IF .gitignore CONTAINS /vendor THEN* add some lines to .gitignore to allow the cul-it-simplesamlphp/vendor directory to be uploaded (4)
10. You'll need to download and install simplesamlphp_auth (and externalauth for Drupal 8) to get the Federated Login link.
11. Make a full backup of the dev site.
12. Push the changes to the remote Pantheon git repo for the dev site.
13. Clone the Files & Database from the Pantheon production site to the dev site
14. Modify the shell script (2) for your site rsync the /cul-it-simplesamlphp directory into your site at /files/private/cul-it-simplesamlphp and run it
15. Confirm Federated Log In works on the dev site
16. Deploy code to the test site the usual way (all 3 items checked).
17. Clone just the Files from the dev site to test site, with update and clear caches checked. 
18. Confirm Federated Log In works on the test site.
19. Clone just the files from dev to the live site with update and clear caches checked.
20. Deploy code from test to live, with update and clear checked.
21. Confirm Federated Log In works on the real domain name of the live site.

(1)

```
ln -s ./private/cul-it-simplesamlphp/www simplesaml
```

(2)

Example shell script code using rsync:

```
export ENV=dev
# Usually dev, test, or live
export SITE=[uuid]
# Site UUID from dashboard URL: https://dashboard.pantheon.io/sites/[uuid]#someotherstuff

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
│   └──www
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
then run (in [docroot]):

$> composer update --lock

```

(4)
```
### Composer ###
composer.phar
vendor/
+ # run composer install in cul-it-simplesamlphp to build /vendor
+ # be sure it's included on server
+ !**/cul-it-simplesamlphp/vendor/

+ add these three lines (without the plus signs) to
[docroot]/.gitignore

(You'll have to do this whenever core is updated.)

```

(5)
```
export SAML=~/Documents/simplesaml-merge/cul-it-simplesamlphp
# target
export TARGET=~/Documents/pantheon-git/lawlibrarycornelledu
#drupal version
export DRUPAL=7

mkdir -p "${TARGET}/private"
rsync -azvh $SAML "${TARGET}/private" --exclude=.git
rm -r "${TARGET}/private/pantheon-simplesamlphp"
rm "${TARGET}/simplesaml"
cd $TARGET
ln -s ./private/cul-it-simplesamlphp/www simplesaml

if [ "$DRUPAL" -eq 8 ]; then
cp ~/Documents/pantheon-settings/d8.settings.php "${TARGET}/sites/default/settings.php"
else
cp ~/Documents/pantheon-settings/d7.settings.php "${TARGET}/sites/default/settings.php"
fi
cp ~/Documents/pantheon-settings/settings.cornell.library.php "${TARGET}/sites/default/settings.cornell.library.php"

echo $TARGET
grep vendor "${TARGET}/.gitignore" || echo "no vendor in .gitignore"
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
