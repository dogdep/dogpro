<?php

Route::group(['prefix' => 'internal'], function() {
    Route::controller('auth', 'AuthController');

    Route::group(['prefix' => 'hook'], function() {
        Route::post('{repo}', 'HookController@pull');
    });
});

Route::group(['prefix'=>'api', 'middleware' => 'jwt.auth'], function() {
    Route::group(['prefix'=>'repo', 'middleware'=>'access.repo'], function() {
        Route::get('/', 'RepoController@index');
        Route::post('/', 'RepoController@create');
        Route::post('/{repo}', 'RepoController@update');
        Route::get('/{repo}', 'RepoController@get');
        Route::get('/{repo}/commit/{hash}', 'RepoController@commit');
        Route::get('/{repo}/commit/query/{page}', 'RepoController@commits');
        Route::post('/{repo}/pull', 'RepoController@pull');

        Route::get('/{repo}/release/{release}', 'ReleaseController@get');
        Route::post('/{repo}/release/{release}', 'ReleaseController@update');
        Route::get('/{repo}/release', 'ReleaseController@all');
        Route::post('/{repo}/release', 'ReleaseController@create');
        Route::get('/{repo}/release/{release}/log', 'ReleaseController@log');
        Route::get('/{repo}/release/{commit}/config', 'ReleaseController@config');

        Route::group(['middleware'=>'auth.admin'], function() {
            Route::post('/{repo}/user/{user}', 'RepoController@postUser');
            Route::delete('/{repo}/user/{user}', 'RepoController@deleteUser');
            Route::delete('/{repo}', 'RepoController@delete');
        });
    });

    Route::group(['prefix'=>'inventory'], function() {
        Route::get('/', 'InventoryController@index');
        Route::post('/', 'InventoryController@create');
        Route::post('/{inventory}', 'InventoryController@update');
        Route::delete('/{inventory}', 'InventoryController@delete');
    });

    Route::get('roles', 'RepoController@roles');
    Route::get('config', 'ConfigController@get');

    Route::group(['prefix'=>'user', 'middleware'=>'auth.admin'], function() {
        Route::get('', 'UserController@all');
    });
});
