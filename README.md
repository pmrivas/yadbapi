#yadbapi:  slim 3 based API

based on ...

## Slim Framework 3 Skeleton Application

### Fast start...

```
. git clone/pull/etc etc
```
then, via composer add neccesary applications...

```
composer require slim/slim "^3.0"
composer require adodb/adodb-php
composer require monolog/monolog
```
### Configuration files:
Create src/dbconfig.php:

```
<?php
/***********************
**  dbconfig.php Database Configuration...
*************************/
$db=[
            'dbname' => 'yadbapi',
            'dbpass' => 'yadbpasword',
            'dbuser' => 'yadbuser',
        ];
?>
```
