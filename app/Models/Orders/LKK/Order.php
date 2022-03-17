<?php

namespace App\Models\Orders\LKK;

use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Orders\AbstractOrder;
use App\Models\Orders\OrderPositions\OrderPosition;
use App\Models\Provider;
use App\Traits\UsesOrderNumber;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends AbstractOrder
{
    use UsesOrderNumber;
    //используется для создания заказов в ЛКК
}
