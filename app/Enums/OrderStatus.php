<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self PENDING()
 * @method static self CONFIRMED()
 * @method static self PROCESSING()
 * @method static self ASSIGNED()
 * @method static self IN_TRANSIT()
 * @method static self DELIVERED()
 * @method static self COMPLETED()
 * @method static self CANCELLED()
 * @method static self RETURNED()
 */
class OrderStatus extends Enum {}
