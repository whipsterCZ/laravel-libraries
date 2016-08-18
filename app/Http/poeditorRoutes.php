<?php


//POEDitor
Route::get('poeditor',['as'=>'poeditor.index','uses'=>'POEditorController@index']);
Route::get('poeditor/update/{language}',['as'=>'poeditor.update','uses'=>'POEditorController@updateLanguage']);
Route::get('poeditor/update-all',['as'=>'poeditor.updateAll','uses'=>'POEditorController@updateLanguages']);
Route::get('poeditor/terms',['as'=>'poeditor.terms', 'uses' => 'POEditorController@showTerms']);
Route::get('poeditor/translations/{language}',['as'=>'poeditor.translations', 'uses' => 'POEditorController@showTranslations']);
Route::get('poeditor/cs',['as'=>'poeditor.cs', 'uses' => 'POEditorController@showCsTranslations']);
Route::get('poeditor/en',['as'=>'poeditor.en', 'uses' => 'POEditorController@showEnTranslations']);