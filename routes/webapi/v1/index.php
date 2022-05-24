<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

require 'admin.php';
Route::group(['middleware' => ['auth:webapi', 'get_per_page_number']], function () {

    Route::get('me', [AuthController::class, 'me']);
    require 'references.php';
    require 'orders.php';
    require 'provider_orders.php';
    require 'consignments.php';
    require 'consignment_registers.php';
    require 'payment_registers.php';
    require 'contractor_notifications.php';
    require 'organization_notifications.php';
    require 'price_negotiations.php';
    require 'request_additions.php';
    require 'notifications.php';
});
