<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth.basic'], function () {

    require 'orders.php';
    require 'references.php';
});
