<?php

namespace App\Http\Api\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @method static static success(mixed $data = null, ?string $message = null, int $statusCode = 200)
 * @method static static error(?string $message = null, mixed $data = null, int $statusCode = 400)
 */
class ApiResponse implements Responsable
{
    protected bool $success;

    /**
     * @var mixed
     */
    protected $data;

    protected ?string $message;

    protected int $statusCode;

    protected array $additional = [];

    protected array $meta = [];

    /**
     * Create a new API response instance.
     *
     * @param  mixed  $data
     */
    public function __construct(
        $data = null,
        ?string $message = null,
        bool $success = true,
        int $statusCode = 200
    ) {
        $this->data = $data;
        $this->message = $message;
        $this->success = $success;
        $this->statusCode = $statusCode;
    }

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

    /**
     * Add additional data to the response.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function with(string $key, $value): self
    {
        $this->additional[$key] = $value;

        return $this;
    }

    /**
     * Add meta data to the response.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function addMeta(string $key, $value): self
    {
        $this->meta[$key] = $value;

        return $this;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): JsonResponse
    {
        $data = $this->data instanceof JsonResource
            ? $this->data->resolve($request)
            : $this->data;

        $response = [
            '_metadata' => array_merge([
                'success' => $this->success,
            ], $this->meta),
            'data' => $data,
        ];

        if ($this->message) {
            $response['_metadata']['message'] = $this->message;
        }

        if (! empty($this->additional)) {
            $response = array_merge($response, $this->additional);
        }

        return response()->json($response, $this->statusCode);
    }
}
