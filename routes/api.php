<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * uploadFiles function on uploadsController 
 */
Route::post('uploadFiles', 'uploadsController@uploadFiles');

/**
 * getUploads & uploadFiles
 */
Route::get('getUploads','uploadsController@getUploads');

/**
 * createArticles function on articlesController
 */
Route::post('createArticles','articlesController@createArticles');

/**
 * getArticle
 */
Route::get('getArticle','articlesController@getArticle');