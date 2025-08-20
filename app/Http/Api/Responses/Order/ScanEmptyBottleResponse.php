<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\Order;

use App\Http\Api\Responses\ApiResponse;

final class ScanEmptyBottleResponse extends ApiResponse
{
    /**
     * Create a success response.
     *
     * @param  mixed  $data
     */
    public static function success($data = null, ?string $message = null, int $statusCode = 200): self
    {
        return new self($data, $message, true, $statusCode);
    }

    /**
     * Create an error response.
     *
     * @param  mixed  $data
     */
    public static function error(?string $message = null, $data = null, int $statusCode = 400): self
    {
        return new self($data, $message, false, $statusCode);
    }
}
