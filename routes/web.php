<?php


Route::view('/', 'welcome');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::resource('backup', 'BackupController')->middleware('auth')->only(['index', 'show']);
Route::get('delete-file/{id}', 'BackupController@deleteFile')->name('backup.delete')->middleware('auth');
Route::get('download/{id}', 'BackupController@download')->middleware('auth')->name('backup.download');
Route::get('backup/load/{id}', 'BackupController@load')->name('backup.load')->middleware('auth');
Route::resource('schedule', 'BackupScheduler')->except('destroy');
Route::get('schedule/delete/{id}', 'BackupScheduler@destroy')->name('schedule.destroy')->middleware('auth');
Route::get('schedule/toggle/{id}/{toggle}', 'BackupScheduler@toggle')->name('schedule.toggle')->middleware('auth');
