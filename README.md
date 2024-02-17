<p align="center">
    <h1 align="center">Trial Task for Importing data(xml,csv) to the database</h1>
    <br>
    <h2>Console application</h2>
</p>

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
      result-screenshots  cointains some sample output



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

### Error-logs
Error log can be found on the provided directory
```
runtime/logs/import-errors.log
```

### Extendability of the Code based on file-type and database

This application can be extended based on the file type.
I have fully implemented the code for the xml file type. and Provided hints for the csv file type.
and based on the configuration of database, this also be possible to extend/save data to any type of database.
Only need to change the db.php file accordingly.


### Output from my local machine are given in the following directory
```
result-screenshots/
```
