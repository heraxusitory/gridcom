<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth.basic'], function () {

    require 'orders.php';
    require 'references.php';
    require 'consignment_registers.php';
    require 'consignments.php';
    require 'payment_registers.php';
});
