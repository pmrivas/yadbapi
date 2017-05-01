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
Install gdcm:  http://gdcm.sourceforge.net

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
$product=[
            'version' => "0.1",
	'copyRght' => "Copyright &copy; YadBrowser: <a target=\"_blank\" href=\"https://github.com/pmrivas/yadbrowser\">Git Hub</a> ",
            'title'=> "YaDb: Yet another Dicom Browser",
            'LogoLg'=>"<b>YaDicom </b>Browser",
            'LogoMini'=>"<b>YaD</b>B",
            'lang'=>"en"
];
$pacs=[
	's1'=> [
		'qstring'=>"gdcmscu --find --studyroot --series 192.168.0.1 11112 --aetitle yadbrowser  --call DCM4CHEE "
	],
	's2'=> [
		'qstring'=>"gdcmscu --find --studyroot --series 192.168.0.2 11112 --aetitle PABLOHOME  --call diagpacext "
	]
]

?>
```
