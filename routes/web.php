<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$router->get('/',  ['uses' => 'HelpController@showHelpMain']);
$router->get('/help',  ['uses' => 'HelpController@showHelpMain']);
$router->get('/version', function () use ($router) {
    return $router->app->version()  ; 
});
$router->post('login', ['uses' => 'UserController@login']); //TODO remove

/*$router->get('admin/profile', ['middleware' => 'auth', function () {
    //
}]);*/

$router->get('help',  ['uses' => 'HelpController@showHelpMain']);

//Users routes
$router->group(['prefix' => '/user'], function () use ($router) {
    $router->get('fwd', ['uses' => 'UserController@updateFwd']);
    $router->post('recover', ['uses' => 'UserController@recoverPassword']); 
    $router->get('', ['uses' => 'UserController@showAllUsers']);
    $router->get('{id}', ['uses' => 'UserController@showOneUser']);
    $router->post('', ['uses' => 'UserController@create']);
    $router->post('{id}', ['uses' => 'UserController@update']);
    $router->delete('{id}/{mail}', ['uses' => 'UserController@delete']);
});

// Messages routes
$router->get('/unpack/{arg}',  ['uses' => 'MessageController@unpackInboxMessage']);
$router->get('/messages',  ['uses' => 'MessageController@showAllMessages']);
$router->get('/messages/{type}',  ['uses' => 'MessageController@showAllMessagesByType']);

$router->group(['prefix' => '/message'], function () use ($router) {
    $router->get('', ['uses' => 'MessageController@showAllMessages']);
    $router->get('list',  ['uses' => '@showAllMessages']);
    $router->get('{id}', ['uses' => 'MessageController@showOneMessage']);
    $router->get('image/{id}', ['uses' => 'FileController@get']);
    $router->get('send/{id}',  ['uses' => 'MessageController@sendMessage']);
    $router->post('', ['uses' => 'MessageController@sendHMP']);
    $router->post('{id}', ['uses' => 'MessageController@update']);
    $router->delete('{id}', ['uses' => 'MessageController@deleteMessage']);
});

$router->group(['prefix' => '/inbox'], function () use ($router) {
    $router->get('', ['uses' => 'MessageController@showAllInboxMessages']);
    $router->get('{id}', ['uses' => 'MessageController@showOneInboxMessage']);
    $router->get('delete/{id}', ['uses' => 'MessageController@deleteInboxMessage']);
    $router->get('hide/{id}', ['uses' => 'MessageController@hideInboxMessage']);
    $router->get('unhide/{id}', ['uses' => 'MessageController@unhideInboxMessage']);
    $router->post('uncrypt/{id}', ['uses' => 'MessageController@unCrypt']);
});

// file upload, download, crypt, compress, uncompress
$router->group(['prefix' => '/file'], function () use ($router) {
    $router->get('{file}', ['uses' => 'FileController@downloadFile']);
    $router->post('', ['uses' => 'FileController@uploadFile']);
    $router->delete('', ['uses' => 'FileController@deleteLostFiles']);
	//TODO 
    // $router->get('', ['uses' => 'FileController@showAllFiles']);
});

// system commands

$router->group(['prefix' => '/sys'], function () use ($router) {
    $router->get('',  ['uses' => 'SystemController@getSysStatus']); //double hit for status
    $router->get('config',  ['uses' => 'SystemController@getSysConfig']);
    $router->post('config',  ['uses' => 'SystemController@setSysConfig']);
    $router->get('gw',  ['uses' => 'SystemController@getSysGw']);
    $router->get('nodename',  ['uses' => 'SystemController@getSysNodeName']);
    $router->get('queueerase',  ['uses' => 'SystemController@queueErase']);
    $router->get('ls',  ['uses' => 'SystemController@getFiles']);
    $router->get('list',  ['uses' => 'SystemController@systemDirList']);
    $router->post('login', ['uses' => 'UserController@login']); 
    $router->get('maildu',  ['uses' => 'SystemController@getMailDiskUsage']);
    $router->get('maillog',  ['uses' => 'SystemController@sysLogMail']);
    $router->get('run/{command}',  ['uses' => 'SystemController@exec_cli']);
    $router->get('stations',  ['uses' => 'SystemController@getSysStations']);
    $router->get('status',  ['uses' => 'SystemController@getSysStatus']);
    $router->get('sensors',  ['uses' => 'SystemController@getSensors']);
    $router->get('uuls',  ['uses' => 'SystemController@sysGetSpoolList']);
    $router->delete('uuka',  ['uses' => 'SystemController@uucpKillJobs']);
    $router->delete('mail/{host}/{id}',  ['uses' => 'SystemController@uucpKillMail']);
    $router->delete('uuk/{host}/{id}',  ['uses' => 'SystemController@uucpKillJob']);
    $router->get('uucall',  ['uses' => 'SystemController@uucpCall']);
    $router->get('uulog',  ['uses' => 'SystemController@sysLogUucp']);
    $router->get('uudebug',  ['uses' => 'SystemController@sysDebUucp']);
    $router->get('shutdown',  ['uses' => 'SystemController@sysShutdown']);
    $router->get('restart',  ['uses' => 'SystemController@sysRestart']);
    $router->get('reboot',  ['uses' => 'SystemController@sysReboot']);
    $router->get('restore',  ['uses' => 'SystemController@sysRestore']);
});

$router->group(['prefix' => '/caller'], function () use ($router) {
    $router->get('',  ['uses' => 'CallerController@showAll']);
    $router->post('', ['uses' => 'CallerController@createSched']);
    $router->put('{id}', ['uses' => 'CallerController@updateSched']);
    $router->get('{id}', ['uses' => 'CallerController@showSched']);
    $router->delete('{id}', ['uses' => 'CallerController@deleteSched']);
});

$router->group(['prefix' => '/radio'], function () use ($router) {
    $router->get('',  ['uses' => 'RadioController@getRadioStatus']);
    $router->get('power',  ['uses' => 'RadioController@getRadioPowerStatus']);
    $router->get('mode',  ['uses' => 'RadioController@getRadioMode']);
    $router->post('mode/{mode}',  ['uses' => 'RadioController@setRadioMode']);
    $router->get('freq',  ['uses' => 'RadioController@getRadioFreq']);
    $router->post('freq/{freq}',  ['uses' => 'RadioController@setRadioFreq']);
    $router->get('bfo',  ['uses' => 'RadioController@getRadioBfo']);
    $router->post('bfo/{freq}',  ['uses' => 'RadioController@setRadioBfo']);
    $router->get('fwd',  ['uses' => 'RadioController@getRadioFwd']);
    //$router->post('fwd/{freq}',  ['uses' => 'RadioController@setRadioFwd']);
    $router->get('led',  ['uses' => 'RadioController@getRadioLedStatus']);
    $router->post('led/{status}',  ['uses' => 'RadioController@setRadioLedStatus']);
    $router->get('ref',  ['uses' => 'RadioController@getRadioRef']);
    $router->post('ptt/{status}', ['uses' => 'RadioController@setRadioPtt']);
    $router->post('tone/{par}', ['uses' => 'RadioController@setRadioTone']);
    //$router->post('ref/{freq}',  ['uses' => 'RadioController@setRadioRef']);
    $router->get('txrx',  ['uses' => 'RadioController@getRadioTxrx']);
    $router->get('mastercal',  ['uses' => 'RadioController@getRadioMasterCal']);
    $router->post('mastercal/{freq}',  ['uses' => 'RadioController@setRadioMasterCal']);
    $router->get('protection',  ['uses' => 'RadioController@getRadioProtection']);
    $router->get('bypass',  ['uses' => 'RadioController@getRadioBypassStatus']);
    $router->post('bypass/{status}',  ['uses' => 'RadioController@setRadioBypassStatus']);
    $router->get('serial',  ['uses' => 'RadioController@getRadioSerial']);
    $router->get('refthreshold',  ['uses' => 'RadioController@getRadioRefThreshold']);
    $router->post('refthreshold/{value}',  ['uses' => 'RadioController@setRadioRefThreshold']);
    $router->post('refthresholdv/{value}',  ['uses' => 'RadioController@setRadioRefThresholdV']);
    $router->post('protection',  ['uses' => 'RadioController@resetRadioProtection']);
    $router->post('setdefaults',  ['uses' => 'RadioController@setRadioDefaults']);
    $router->post('restoredefaults',  ['uses' => 'RadioController@restoreRadioDefaults']);
});
