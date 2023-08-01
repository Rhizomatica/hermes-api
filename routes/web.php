<?php

/** @var \Laravel\Lumen\Routing\Router * $router */
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$router->get('/',  ['uses' => 'HelpController@showHelpMain']);
$router->get('/version', function () use ($router) {
    return $router->app->version();
});

$router->post('login', ['uses' => 'UserController@login']);

$router->get('/unpack/{arg}',  ['uses' => 'MessageController@unpackInboxMessage']); //MESSAGE CONTROLLER?

$router->group(['prefix' => '/user'], function () use ($router) {
    $router->get('', ['uses' => 'UserController@showAllUsers']);
    $router->get('{id}', ['uses' => 'UserController@showOneUser']);
    $router->post('', ['uses' => 'UserController@create']);
    $router->post('{id}', ['uses' => 'UserController@update']);
    $router->delete('{id}/{mail}', ['uses' => 'UserController@delete']);
});

$router->group(['prefix' => '/message'], function () use ($router) {
    $router->get('{id}', ['uses' => 'MessageController@showOneMessage']);
    $router->get('/type/{type}',  ['uses' => 'MessageController@showAllMessagesByType']);
    $router->get('image/{id}', ['uses' => 'FileController@get']); //FILE CONTROLLER?
    $router->post('', ['uses' => 'MessageController@sendHMP']);
    $router->delete('{id}', ['uses' => 'MessageController@deleteMessage']);
    $router->post('uncrypt/{id}', ['uses' => 'MessageController@unCrypt']);
});

// file upload, download, crypt, compress, uncompress
$router->group(['prefix' => '/file'], function () use ($router) {
    $router->get('{file}', ['uses' => 'FileController@downloadFile']);
    $router->post('', ['uses' => 'FileController@uploadFile']);
    $router->delete('', ['uses' => 'FileController@deleteLostFiles']);
});

$router->group(['prefix' => '/sys'], function () use ($router) {
    $router->get('',  ['uses' => 'SystemController@getSysStatus']);
    $router->get('config',  ['uses' => 'SystemController@getSysConfig']);
    $router->post('config',  ['uses' => 'SystemController@setSysConfig']);
    $router->get('maillog',  ['uses' => 'SystemController@sysLogMail']);
    $router->get('stations',  ['uses' => 'SystemController@getSysStations']);
    $router->get('status',  ['uses' => 'SystemController@getSysStatus']);//REPEATED ?
    $router->get('uuls',  ['uses' => 'SystemController@sysGetSpoolList']);
    $router->delete('mail/{host}/{id}/{language}',  ['uses' => 'SystemController@uucpKillMail']);
    $router->delete('uuk/{host}/{id}',  ['uses' => 'SystemController@uucpKillJob']);
    $router->get('uucall',  ['uses' => 'SystemController@uucpCall']);
    $router->get('uucall/{uuidhost}',  ['uses' => 'SystemController@uucpCallForHost']);//TODO - CALLER?
    $router->get('uulog',  ['uses' => 'SystemController@sysLogUucp']);//TODO - CALLER?
    $router->get('uudebug',  ['uses' => 'SystemController@sysDebUucp']);
    $router->get('shutdown',  ['uses' => 'SystemController@sysShutdown']);
    $router->get('reboot',  ['uses' => 'SystemController@sysReboot']);
    $router->get('language',  ['uses' => 'SystemController@language']);
});

$router->group(['prefix' => '/caller'], function () use ($router) { //TODO - RENAME TO SCHEDULE?
    $router->get('',  ['uses' => 'CallerController@showAll']);
    $router->post('', ['uses' => 'CallerController@createSched']);
    $router->put('{id}', ['uses' => 'CallerController@updateSched']);
    $router->get('{id}', ['uses' => 'CallerController@showSched']);
    $router->delete('{id}', ['uses' => 'CallerController@deleteSched']);
});

$router->group(['prefix' => '/radio'], function () use ($router) {
    $router->get('',  ['uses' => 'RadioController@getRadioStatus']);    
    $router->get('power',  ['uses' => 'RadioController@getRadioPowerStatus']);
    $router->post('mode/{mode}',  ['uses' => 'RadioController@setRadioMode']);
    $router->get('freq',  ['uses' => 'RadioController@getRadioFreq']);
    $router->post('freq/{freq}',  ['uses' => 'RadioController@setRadioFreq']);
    $router->get('bfo',  ['uses' => 'RadioController@getRadioBfo']);
    $router->post('bfo/{freq}',  ['uses' => 'RadioController@setRadioBfo']);
    $router->post('led/{status}',  ['uses' => 'RadioController@setRadioLedStatus']);
    $router->post('ptt/{status}', ['uses' => 'RadioController@setRadioPtt']);
    $router->post('tone/{par}', ['uses' => 'RadioController@setRadioTone']);
    $router->post('mastercal/{freq}',  ['uses' => 'RadioController@setRadioMasterCal']);
    $router->get('protection',  ['uses' => 'RadioController@getRadioProtection']);
    $router->post('connection/{status}',  ['uses' => 'RadioController@setRadioConnectionStatus']);
    $router->get('refthreshold',  ['uses' => 'RadioController@getRadioRefThreshold']);
    $router->post('refthreshold/{value}',  ['uses' => 'RadioController@setRadioRefThreshold']);
    $router->post('refthresholdv/{value}',  ['uses' => 'RadioController@setRadioRefThresholdV']);
    $router->post('protection',  ['uses' => 'RadioController@resetRadioProtection']);
    $router->post('default',  ['uses' => 'RadioController@restoreRadioDefaults']);
});

$router->group(['prefix' => '/geolocation'], function () use ($router) {
    $router->get('calibration',  ['uses' => 'GeoLocationController@startGPSCalibration']);
});

$router->group(['prefix' => '/frequency'], function () use ($router) {
    $router->get('',  ['uses' => 'FrequenciesController@getFrequencies']);
    $router->get('{id}',  ['uses' => 'FrequenciesController@getFrequency']);
    $router->get('/alias/{alias}',  ['uses' => 'FrequenciesController@getFrequencyByAlias']);
    $router->put('{id}', ['uses' => 'FrequenciesController@updateFrequency']);
});

$router->group(['prefix' => '/customerrors'], function () use ($router) {
    $router->get('',  ['uses' => 'ErrorController@getCustomErrors']);
    $router->delete('{id}', ['uses' => 'ErrorController@deleteCustomError']);
    $router->delete('', ['uses' => 'ErrorController@deleteCustomError']);
    $router->post('', ['uses' => 'ErrorController@saveError']);
});

$router->group(['prefix' => '/wifi'], function () use ($router) {
    $router->get('',  ['uses' => 'WiFiController@getWiFiConfigurations']);
    $router->post('',  ['uses' => 'WiFiController@saveWiFiConfigurations']);
});

