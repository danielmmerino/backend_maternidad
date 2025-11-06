<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/recepcion/agenda', 'agenda.recepcion')->name('recepcion.agenda');
