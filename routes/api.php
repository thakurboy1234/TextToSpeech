<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\SpeechController;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('upload-file', [FileController::class, 'uploadFile']);
Route::get('fetch-file/{id}', [FileController::class, 'fetchFile']);


Route::get('fetch-records', [FileController::class, 'fetchRecords']);


// Route::get('/articles/{id}/text', function ($id) {
//     $article = File::findOrFail($id);
//     return response()->json(['text' => $article->filename]);
// });


// Route::post('convert-to-speech', [SpeechController::class, 'uploadFile']);

// Route::post('convert-file-to-speech', [SpeechController::class, 'convertFileToSpeech']);
