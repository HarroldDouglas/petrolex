<?php

namespace App\Http\Controllers\Bottle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bottle;
use App\Models\BottleMovement;
use \App\Services\Bottle\BottleService;
class GetBottleHistoryController extends Controller
{

    public function __construct(private BottleService $bottleService)
    {
        $this->bottleService = $bottleService;
    }

    /**
     * Return the history of a bottle as JSON.
     */
    public function __invoke($bottle_id)
    {
        $bottle = $this->bottleService->findOrFail($bottle_id);

        $history = $this->bottleService->getBottleHistory($bottle);

        $history = collect(value: $history)->map(fn($dto) => (array) $dto)->toArray();

        return response()->json([
            'bottle' => $bottle,
            'history' => $history,
        ]);
    }
}