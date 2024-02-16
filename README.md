<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii 2 Basic Project Template</h1>
    <br>
</p>

Yii 2 Basic Project Template is a skeleton [Yii 2](https://www.yiiframework.com/) application best for
rapidly creating small projects.

The template contains the basic features including user login/logout and a contact page.
It includes all commonly used configurations that would allow you to focus on adding new
features to your application.

[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-app-basic.svg)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-app-basic.svg)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![build](https://github.com/yiisoft/yii2-app-basic/workflows/build/badge.svg)](https://github.com/yiisoft/yii2-app-basic/actions?query=workflow%3Abuild)

DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)/ feedController
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime and error log file
      tests/              contains various tests for the basic application
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources



REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 8.0


INSTALLATION
------------

### Install via Composer

If you do not have [Composer](https://getcomposer.org/), you may install it by following the instructions
at [getcomposer.org](https://getcomposer.org/doc/00-intro.md#installation-nix).

You can then install this project template using the following command:


CONFIGURATION
-------------

### Database

Edit the file `config/db.php` with real data, for example:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=inventory-management',
    'username' => 'admin',
    'password' => 'pass',
    'charset' => 'utf8',
];
```

**Important**
1. Please goto the database of your localhost
2. and create new database named with 'inventory-management'
3. then create user with name admin(any-name-you-want) password='your-wish', keep in mind to change accordingly in the `config/db.php` directory
4. check all privileges to that created user, and save.
5. good to go.

**NOTES:**
- Yii won't create the database for you, this has to be done manually before you can access it.
- Check and edit the other files in the `config/` directory to customize your application as required.
- Refer to the README in the `tests` directory for information specific to basic application tests.


TESTING
-------

Tests are located in `tests` directory. They are developed with [Codeception PHP Testing Framework](https://codeception.com/).

- `functional`


Tests can be executed by running

```
vendor/bin/codecept run functional
```

The command above will execute functional tests. Unit tests are testing the system components, while functional
tests are for testing user interaction.

You can see code coverage output under the `tests/_output` directory.
Sample xml data for code testing are under `tests/_data` directory.

Requirements:
-------
1. php 8.0
2. composer.phar
3. mysql database

Run the application
-------
from the command line / terminal run the command:
```
php yii controller/action arguments/for/the/action/file.xml
```
example in my case:
```
php yii feed/data /home/muhmmad/Desktop/task-kauf/process-data/feed-data/feed.xml
```

Run the Test-case in this application
-------
from the command line / terminal run the command:

```
vendor/bin/codecept run functional
```

### Test-Database configuration

Edit the file `config/test_db.php` with real data, for example:

```php
$db['dsn'] = 'mysql:host=localhost;dbname=test_inventory-management';
```