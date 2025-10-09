<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Storage;


Route::get('/', function () {
    return response('Bienvenidos', 200)
        ->header('Content-Type', 'text/plain');
});
