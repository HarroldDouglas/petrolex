<?php

namespace App\Http\Controllers\Bottle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bottle;

class MarkBottleFoundController extends Controller
{
    public function __invoke($id)
    {
        $bottle = Bottle::findOrFail($id);
        $bottle->status = \App\Enums\BottleStatus::RETURNED_TO_SUPPLIER()->value;
        $bottle->save();

        return response()->json(['success' => true, 'message' => 'Bouteille marquée comme retrouvée.']);
    }
}