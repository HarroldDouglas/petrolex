<?php

namespace App\Http\Controllers\Bottle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bottle;
use App\Models\BottleMovement;

class GetBottleHistoryController extends Controller
{
    /**
     * Return the history of a bottle as JSON.
     */
    public function __invoke($bottle_id)
    {
        // Fetch the bottle
        $bottle = Bottle::findOrFail($bottle_id);

        // Fetch the history records for this bottle, most recent first
        $history = BottleMovement::where('bottle_id', $bottle_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($record) {
            return [
                'moved_at' => $record->movement_date,
                'from_location' => $record->from_location,
                'to_location' => $record->to_location,
                'user' => $record->user ? $record->user->name : null,
                'notes' => $record->notes,
            ];
            });

        return response()->json([
            'bottle' => [
                'barcode' => $bottle->barcode,
                'type' => $bottle->bottleType->name,
                'status' => \App\Enums\BottleStatus::from($bottle->status)->label,
            ],
            'history' => $history,
        ]);
    }
}