<?php
require __DIR__ . "/dbconfig.php";
return [
	'settings' => [
		'displayErrorDetails' => true, // set to false in production
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header

		// Renderer settings
		'renderer' => [
				'template_path' => __DIR__ . '/../templates/',
		],

		// Monolog settings
		'logger' => [
				'name' => 'slim-app',
				'path' => __DIR__ . '/../logs/app.log',
				'level' => \Monolog\Logger::DEBUG,
		],
		'db'=> $db,
		'product'=>$product,
		'pacs'=>$pacs,
		'wado'=>$wado,
		'dctags'=> [
			'0010,0010'=>'patName',
			'0010,0020'=>'patId',
			'0010,0021'=>'patIssuer',
			'0008,0018'=>'insUID',
			'0008,0020'=>'stDate',
			'0008,0021'=>'serDate',
			'0008,0030'=>'stTime',
			'0008,0031'=>'serTime',
			'0008,0060'=>'modality',
			'0008,0090'=>'refDoc',
			'0008,0080'=>'institution',
			'0008,0052'=>'QRLevel',
			'0008,0056'=>'availability',
			'0008,0054'=>'retAET',
			'0008,103e'=>'serDesc',
			'0008,1030'=>'stDescr',
			'0010,0030'=>'patBirth',
			'0010,0040'=>'pathSex',
			'0020,000d'=>'stIUID',
			'0020,000e'=>'serUID',
			'0020,0011'=>'seriesNumber',
			'0020,0013'=>'instNumber',
			'0020,1206'=>'relseries',
			'0020,1208'=>'relinstances',
			'0020,1209'=>'instCount',
		]
	],
];
