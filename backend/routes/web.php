<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['msg' => 'Car Rental API']));
