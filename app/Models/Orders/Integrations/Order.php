<?php

namespace App\Models\Orders\Integrations;

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
    //Используется чтобы не применялся trait usesOrderNumber
    //Используется при синке заказов со стороны 1С
}
