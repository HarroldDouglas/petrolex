<?php

namespace App\Http\Controllers\Accessory;

use App\Http\Controllers\Controller;
use App\Services\Accessory\AccessoryTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GetAccessoriesController extends Controller
{
    public function __construct(
        private readonly AccessoryTypeService $AccessoryTypeService
    ) {}

    /**
     * Display a listing of accessories.
     *
     * Route: GET /accessories
     * Name: accessories.index
     */
    public function __invoke(Request $request): View
    {
        $accessoryStats = $this->AccessoryTypeService->getAccessoryStats();

        return view('accessories.index', [
            'accessoryStats' => $accessoryStats,
        ]);
    }
}
