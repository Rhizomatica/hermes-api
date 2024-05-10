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
    $router->get('status',  ['uses' => 'SystemController@getSysStatus']); //REPEATED ?
    $router->get('uuls',  ['uses' => 'SystemController@sysGetSpoolList']);
    $router->delete('mail/{host}/{id}/{language}',  ['uses' => 'SystemController@uucpKillMail']);
    $router->delete('uuk/{host}/{id}',  ['uses' => 'SystemController@uucpKillJob']);
    $router->get('uucall',  ['uses' => 'SystemController@uucpCall']);
    $router->get('uucall/{uuidhost}',  ['uses' => 'SystemController@uucpCallForHost']); //TODO - CALLER?
    $router->get('uulog',  ['uses' => 'SystemController@sysLogUucp']); //TODO - CALLER?
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
    $router->get('power/{profile}',  ['uses' => 'RadioController@getRadioPowerStatus']);
    $router->post('mode/{mode}/{profile}',  ['uses' => 'RadioController@setRadioMode']);
    $router->get('freq/{profile}',  ['uses' => 'RadioController@getRadioFreq']);
    $router->post('freq/{freq}/{profile}',  ['uses' => 'RadioController@setRadioFreq']);
    $router->get('bfo/{profile}',  ['uses' => 'RadioController@getRadioBfo']);
    $router->post('bfo/{freq}/{profile}',  ['uses' => 'RadioController@setRadioBfo']);
    $router->post('led/{status}/{profile}',  ['uses' => 'RadioController@setRadioLedStatus']);
    $router->post('ptt/{status}/{profile}', ['uses' => 'RadioController@setRadioPtt']);
    $router->post('tone/{par}', ['uses' => 'RadioController@setRadioTone']);
    $router->post('tone/sbitx/{par}/{profile}', ['uses' => 'RadioController@setRadioToneSBitx']);
    $router->post('mastercal/{freq}/{profile}',  ['uses' => 'RadioController@setRadioMasterCal']);
    $router->get('protection/{profile}',  ['uses' => 'RadioController@getRadioProtection']);
    $router->post('connection/{status}/{profile}',  ['uses' => 'RadioController@setRadioConnectionStatus']);
    $router->get('refthreshold/{profile}',  ['uses' => 'RadioController@getRadioRefThreshold']);
    $router->post('refthreshold/{value}/{profile}',  ['uses' => 'RadioController@setRadioRefThreshold']);
    $router->post('refthresholdv/{value}/{profile}',  ['uses' => 'RadioController@setRadioRefThresholdV']);
    $router->post('protection/{profile}',  ['uses' => 'RadioController@resetRadioProtection']);
    $router->post('default/{profile}',  ['uses' => 'RadioController@restoreRadioDefaults']);
    $router->get('step',  ['uses' => 'RadioController@getStep']);
    $router->post('step/{step}',  ['uses' => 'RadioController@updateStep']);
    $router->get('volume',  ['uses' => 'RadioController@getVolume']);
    $router->post('volume/{volume}',  ['uses' => 'RadioController@changeVolume']);
    $router->post('sosemergency',  ['uses' => 'RadioController@sosEmergency']);
    $router->post('profile/{profile}',  ['uses' => 'RadioController@setRadioProfile']);
    $router->get('{profile}',  ['uses' => 'RadioController@getRadioStatus']);
    $router->post('/voice/timeout',  ['uses' => 'RadioController@restartVoiceTimeout']);
    $router->get('/voice/timeout/config',  ['uses' => 'RadioController@getTimeoutConfig']);
    $router->post('/voice/timeout/config/{seconds}',  ['uses' => 'RadioController@setTimeoutConfig']);
});

$router->group(['prefix' => '/geolocation'], function () use ($router) {
    $router->get('calibration',  ['uses' => 'GeoLocationController@startGPSCalibration']);
    $router->get('status',  ['uses' => 'GeoLocationController@getGPSStatus']);
    $router->get('files',  ['uses' => 'GeoLocationController@getStoredLocationFilesFromPath']);
    $router->get('files/all',  ['uses' => 'GeoLocationController@getStoredLocationAllFiles']);
    $router->get('file/{name}',  ['uses' => 'GeoLocationController@getStoredLocationFileByName']);
    $router->get('coordinates',  ['uses' => 'GeoLocationController@getCurrentCoordinates']);
    $router->post('interval/{seconds}',  ['uses' => 'GeoLocationController@setGPSStoringInterval']);
    $router->post('file/range/{seconds}',  ['uses' => 'GeoLocationController@setGPSFileRangeTime']);
    $router->post('status/{status}',  ['uses' => 'GeoLocationController@setStoringGPSStatus']);
    $router->delete('delete',  ['uses' => 'GeoLocationController@deleteStoredFiles']);
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
    $router->post('', ['uses' => 'ErrorController@saveErrorApi']);
});

$router->group(['prefix' => '/wifi'], function () use ($router) {
    $router->get('',  ['uses' => 'WiFiController@getWiFiConfigurations']);
    $router->post('',  ['uses' => 'WiFiController@saveWiFiConfigurations']);
    $router->post('/mac/filter',  ['uses' => 'WiFiController@macFilter']);
    $router->post('/mac/address',  ['uses' => 'WiFiController@macAddress']);
    $router->delete('/mac/address/{address}',  ['uses' => 'WiFiController@deleteMacAddress']);
});
