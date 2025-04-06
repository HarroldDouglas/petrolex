<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self PLANNED()
 * @method static self IN_PROGRESS()
 * @method static self COMPLETED()
 * @method static self CANCELLED()
 */
class DeliveryStatus extends Enum {}
