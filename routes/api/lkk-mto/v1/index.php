<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth.basic:api'], function () {

    require 'orders.php';
    require 'references.php';
    require 'consignment_registers.php';
    require 'consignments.php';
    require 'payment_registers.php';
    require 'provider_orders.php';
    require 'requirement_corrections.php';
    require 'order_corrections.php';
    require 'organization_notifications.php';
    require 'request_additions.php';
    require 'price_negotiations.php';
});
