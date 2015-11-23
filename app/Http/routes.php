<?php

Route::group(['prefix' => 'git/{repo}/{inv}'], function() {
    Route::get('HEAD', 'Git\GitInfoController@getHead');
    Route::get('info/refs', 'Git\GitInfoController@getRefs');
    Route::post('git-receive-pack', 'Git\GitInfoController@receivePack');
});

Route::group(['prefix' => 'internal'], function() {
    Route::controller('auth', 'AuthController');

    Route::group(['prefix' => 'hook'], function() {
        Route::post('{repo}', 'HookController@pull');
    });
});

Route::group(['prefix'=>'api', 'middleware' => 'jwt.auth'], function() {
    Route::group(['prefix'=>'release', 'middleware'=>'access.repo'], function() {
        Route::get('/{release}', 'ReleaseController@get');
        Route::post('/{release}', 'ReleaseController@update');
        Route::get('/', 'ReleaseController@all');
        Route::post('/', 'ReleaseController@create');
        Route::get('/{release}/log', 'ReleaseController@log');
    });

    Route::group(['prefix'=>'repo', 'middleware'=>'access.repo'], function() {
        Route::get('/', 'RepoController@index');
        Route::post('/', 'RepoController@create');
        Route::post('/{repo}', 'RepoController@update');
        Route::get('/{repo}', 'RepoController@get');
        Route::get('/{repo}/commit/{hash}', 'RepoController@commit');
        Route::get('/{repo}/commit/query/{page}', 'RepoController@commits');
        Route::post('/{repo}/pull', 'RepoController@pull');
        Route::get('/{repo}/config/{commit}', 'RepoController@config');

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
