<?php

namespace App\Http\Controllers\Accessory;

use App\Http\Controllers\Controller;
use App\Services\Accessory\AccessoryTypeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EditAccessoryController extends Controller
{
    public function __construct(
        private readonly AccessoryTypeService $accessoryTypeService
    ) {}

    /**
     * Show the form for editing the specified accessory.
     *
     * Route: GET /accessories/edit/{id}
     * Name: accessories.edit
     */
    public function __invoke(Request $request, int $accessoryTypeId): View
    {
        $accessoryType = $this->accessoryTypeService->find($accessoryTypeId);

        return view('accessories.edit', compact('accessoryType'));
    }
}
