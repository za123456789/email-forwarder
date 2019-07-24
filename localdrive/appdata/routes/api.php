
<?php

Route::group(['prefix' => '/v1', 'namespace' => 'Api\V1', 'as' => 'api.'], function () {

        Route::resource('emails', 'EmailsController', ['except' => ['create', 'edit']]);

});

Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');
Route::group(['middleware' => 'auth:api'], function(){
Route::post('details', 'API\UserController@details');
Route::get("/email/add" , "Admin\EmailsController@insert");
Route::post("/email/add" , "Admin\EmailsController@insert");
Route::post("/email/update" , "Admin\EmailsController@updateemail");
Route::post("/email/delete" , "Admin\EmailsController@delete");
});

Route::get("/" , "Admin\EmailsController@index");

// Route::get("/email/add" , "Admin\EmailsController@insert");
// Route::post("/email/add" , "Admin\EmailsController@insert");

// Route::post("/email/update" , "Admin\EmailsController@updateemail");

// Route::post("/email/delete" , "Admin\EmailsController@delete");
//Route::delete("/email/delete" , "Admin\EmailsController@delete");
