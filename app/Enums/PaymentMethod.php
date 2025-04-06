<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self CASH()
 * @method static self CREDIT_CARD()
 * @method static self BANK_TRANSFER()
 * @method static self MOBILE_MONEY()
 */
class PaymentMethod extends Enum {}
