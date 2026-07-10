<?php

/*
 * Manager mobile app API — endpoints used by the dedicated Flutter app
 * for supply & order bottle scanning by managers/admins.
 *
 * Allowed roles: super_admin, admin, manager, gas_manager, center_manager.
 *
 * Logic mirrors the existing web Livewire components and reuses the
 * OrderBottleScanService for orders — to guarantee identical behavior
 * to the web scanner (no double-implementation drift).
 */

use App\Enums\BottleStatus;
use App\Enums\OrderStatus;
use App\Enums\SupplierDeliveryBottleMovementType;
use App\Exceptions\BottleScanException;
use App\Models\Bottle;
use App\Models\Order;
use App\Models\Product;
use App\Models\SupplierDelivery;
use App\Models\SupplierDeliveryBottle;
use App\Services\Order\OrderBottleScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/** Guard helper — aborts with 403 if the caller is not a manager-level user. */
$ensureManager = function (Request $request): void {
    $user = $request->user();
    $allowed = ['super_admin', 'admin', 'manager', 'gas_manager', 'center_manager'];
    if (! $user || ! $user->hasAnyRole($allowed)) {
        abort(403, 'Réservé aux gestionnaires.');
    }
};

Route::middleware('auth:sanctum')
    ->prefix('manager')
    ->name('api.manager.')
    ->group(function () use ($ensureManager) {

        // ----- SUPPLIES --------------------------------------------------

        // GET /api/manager/supplies — list active supplies the manager can scan into.
        Route::get('/supplies', function (Request $request) use ($ensureManager) {
            $ensureManager($request);
            $supplies = SupplierDelivery::query()
                ->where('status', 'in_progress')
                ->with('distributionCenter:id,name')
                ->orderByDesc('supply_date')
                ->limit(50)
                ->get(['id', 'distribution_center_id', 'delivery_number', 'title', 'supplier_name', 'supply_date', 'status']);

            return response()->json([
                'data' => $supplies->map(fn ($s) => [
                    'id' => $s->id,
                    'delivery_number' => $s->delivery_number,
                    'title' => $s->title,
                    'supplier_name' => $s->supplier_name,
                    'supply_date' => $s->supply_date?->toIso8601String(),
                    'status' => (string) $s->status,
                    'distribution_center' => $s->distributionCenter?->only(['id', 'name']),
                ]),
            ]);
        })->name('supplies.index');

        // GET /api/manager/supplies/{supply} — detail + scan progress +
        // scanned bottles per product type per direction. Mirrors the web
        // Livewire's loadBottles() shape: each scan row { id, barcode, timestamp }.
        Route::get('/supplies/{supply}', function (Request $request, SupplierDelivery $supply) use ($ensureManager) {
            $ensureManager($request);
            $supply->load([
                'distributionCenter:id,name',
                'productTypes.productCategory',
                'productTypes.deliveryBottles.bottle:id,barcode',
            ]);

            return response()->json([
                'data' => [
                    'id' => $supply->id,
                    'delivery_number' => $supply->delivery_number,
                    'title' => $supply->title,
                    'supplier_name' => $supply->supplier_name,
                    'supply_date' => $supply->supply_date?->toIso8601String(),
                    'status' => (string) $supply->status,
                    'distribution_center' => $supply->distributionCenter?->only(['id', 'name']),
                    'product_types' => $supply->productTypes->map(function ($pt) {
                        $category = $pt->productCategory;
                        $typeInstance = $category?->productTypeInstance;

                        $mapScan = fn ($row) => [
                            'id' => $row->id,
                            'barcode' => $row->bottle?->barcode,
                            'timestamp' => $row->created_at?->toIso8601String(),
                        ];

                        // deliveryBottles is the relation that holds ALL
                        // SupplierDeliveryBottle rows for this product type.
                        $allBottles = $pt->deliveryBottles ?? collect();
                        $incomingBottles = $allBottles
                            ->filter(fn ($r) => (string) $r->movement_type === 'incoming')
                            ->values()
                            ->map($mapScan);
                        $outgoingBottles = $allBottles
                            ->filter(fn ($r) => (string) $r->movement_type === 'outgoing')
                            ->values()
                            ->map($mapScan);

                        return [
                            'id' => $pt->id,
                            'product_category_id' => $pt->product_category_id,
                            'category_name' => $category?->name,
                            'bottle_type_name' => $typeInstance?->name,
                            'capacity' => $typeInstance?->capacity ?? null,
                            'incoming_expected' => (int) $pt->expected_quantity,
                            'incoming_scanned' => $pt->incoming_scanned_count,
                            'incoming_done' => (bool) $pt->incoming_done,
                            'outgoing_expected' => (int) $pt->bottles_out_quantity,
                            'outgoing_scanned' => $pt->outgoing_scanned_count,
                            'outgoing_done' => (bool) $pt->outgoing_done,
                            'scanned_bottles_incoming' => $incomingBottles,
                            'scanned_bottles_outgoing' => $outgoingBottles,
                        ];
                    }),
                ],
            ]);
        })->name('supplies.show');

        // POST /api/manager/supplies/{supply}/remove-bottles
        // Body: { scan_ids: int[] }  (SupplierDeliveryBottle row IDs)
        // Mirrors the Livewire removeSelected() — soft-deletes the rows.
        Route::post('/supplies/{supply}/remove-bottles', function (Request $request, SupplierDelivery $supply) use ($ensureManager) {
            $ensureManager($request);
            $validated = $request->validate([
                'scan_ids' => ['required', 'array', 'min:1'],
                'scan_ids.*' => ['integer'],
            ]);

            if (! $supply->canBeEdited()) {
                return response()->json(['ok' => false, 'message' => 'Cet approvisionnement ne peut plus être modifié.'], 422);
            }

            try {
                DB::beginTransaction();
                // Constrain to scans that belong to this supply (don't trust client IDs).
                $scanQuery = SupplierDeliveryBottle::whereIn('id', $validated['scan_ids'])
                    ->whereIn('supplier_delivery_product_type_id', $supply->productTypes()->pluck('id'));
                $bottleIds = (clone $scanQuery)->pluck('bottle_id');
                $deleted = $scanQuery->delete();

                app(\App\Services\Supply\BottleReleaseService::class)
                    ->releaseOrphanedBottles($bottleIds);

                DB::commit();
                return response()->json([
                    'ok' => true,
                    'message' => "$deleted bouteille(s) supprimée(s).",
                    'deleted_count' => $deleted,
                ]);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('manager.supplies.remove-bottles failed', ['error' => $e->getMessage()]);
                return response()->json(['ok' => false, 'message' => 'Erreur: '.$e->getMessage()], 500);
            }
        })->name('supplies.remove-bottles');

        // POST /api/manager/supplies/{supply}/scan-bottle
        // Body: { product_type_id: int, barcode: string, direction: "incoming"|"outgoing" }
        // Mirrors app/Livewire/Supply/ScanBottles@addBottle exactly.
        Route::post('/supplies/{supply}/scan-bottle', function (Request $request, SupplierDelivery $supply) use ($ensureManager) {
            $ensureManager($request);
            $validated = $request->validate([
                'product_type_id' => ['required', 'integer'],
                'barcode' => ['required', 'string', 'max:60'],
                'direction' => ['required', 'in:incoming,outgoing'],
            ]);

            if (! $supply->canBeEdited()) {
                return response()->json(['ok' => false, 'message' => 'Cet approvisionnement ne peut plus être modifié.'], 422);
            }

            $productType = $supply->productTypes()->where('id', $validated['product_type_id'])->first();
            if (! $productType) {
                return response()->json(['ok' => false, 'message' => 'Produit introuvable dans cet approvisionnement.'], 404);
            }

            $isIncoming = $validated['direction'] === 'incoming';
            $movementType = $isIncoming
                ? SupplierDeliveryBottleMovementType::INCOMING()
                : SupplierDeliveryBottleMovementType::OUTGOING();
            $current = $isIncoming ? $productType->incoming_scanned_count : $productType->outgoing_scanned_count;
            $max = $isIncoming ? (int) $productType->expected_quantity : (int) $productType->bottles_out_quantity;
            if ($current >= $max) {
                return response()->json(['ok' => false, 'message' => 'Quantité maximale atteinte pour ce sens.'], 422);
            }

            $barcode = trim($validated['barcode']);

            DB::beginTransaction();
            try {
                $bottle = Bottle::where('barcode', $barcode)->first();

                // Look up any existing scan (active or soft-deleted) for this
                // bottle on this supply product-type. A soft-deleted scan is
                // restored instead of re-created (unique constraint on
                // product_type + bottle covers trashed rows too).
                $existing = null;
                if ($bottle) {
                    $existing = SupplierDeliveryBottle::withTrashed()
                        ->where('supplier_delivery_product_type_id', $productType->id)
                        ->where('bottle_id', $bottle->id)
                        ->first();
                }

                if ($isIncoming) {
                    if (! $bottle) {
                        $product = Product::firstOrCreate(
                            ['product_category_id' => $productType->product_category_id],
                        );
                        $bottle = Bottle::create([
                            'barcode' => $barcode,
                            'product_id' => $product->id,
                            'distribution_center_id' => $supply->distribution_center_id,
                            'is_filled' => true,
                            'status' => BottleStatus::PENDING_RECEPTION(),
                        ]);
                    } else {
                        // The guard rejects bottles already held by an active
                        // scan of ANY in-progress supply (one bottle = one open
                        // supply at a time) and bottles in circulation.
                        $reason = app(\App\Services\Supply\IncomingScanGuard::class)
                            ->rejectionReason($bottle, (int) $productType->id);
                        if ($reason !== null) {
                            DB::rollBack();
                            return response()->json(['ok' => false, 'message' => $reason], 409);
                        }
                        $bottle->update([
                            'status' => BottleStatus::PENDING_RECEPTION(),
                            'is_filled' => true,
                            'distribution_center_id' => $supply->distribution_center_id,
                        ]);
                    }
                } else {
                    // OUTGOING: bottle must already exist.
                    if (! $bottle) {
                        DB::rollBack();
                        return response()->json(['ok' => false, 'message' => "Bouteille {$barcode} introuvable dans le système."], 404);
                    }
                }

                if ($existing) {
                    if ($existing->trashed()) {
                        $existing->restore();
                        $existing->update(['movement_type' => $movementType]);
                    } else {
                        DB::rollBack();
                        return response()->json(['ok' => false, 'message' => 'Cette bouteille a déjà été scannée pour cet approvisionnement.'], 409);
                    }
                } else {
                    SupplierDeliveryBottle::create([
                        'supplier_delivery_product_type_id' => $productType->id,
                        'bottle_id' => $bottle->id,
                        'movement_type' => $movementType,
                    ]);
                }

                DB::commit();
                $productType->refresh();

                return response()->json([
                    'ok' => true,
                    'message' => 'Bouteille scannée.',
                    'progress' => [
                        'incoming_scanned' => $productType->incoming_scanned_count,
                        'incoming_expected' => (int) $productType->expected_quantity,
                        'outgoing_scanned' => $productType->outgoing_scanned_count,
                        'outgoing_expected' => (int) $productType->bottles_out_quantity,
                    ],
                ]);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('manager.supplies.scan-bottle failed', [
                    'supply_id' => $supply->id,
                    'barcode' => $barcode,
                    'error' => $e->getMessage(),
                ]);
                return response()->json(['ok' => false, 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
            }
        })->name('supplies.scan-bottle');

        // ----- ORDERS ----------------------------------------------------

        // GET /api/manager/orders — list orders ready for bottle preparation.
        // Only 'paid' status is actionable by managers (canScanBottles returns
        // true only when status == paid). All other statuses are managed by
        // delivery person / customer.
        Route::get('/orders', function (Request $request) use ($ensureManager) {
            $ensureManager($request);
            $orders = Order::query()
                ->where('status', OrderStatus::PAID()->value)
                ->with([
                    'customer:id,user_id',
                    'customer.user:id,first_name,last_name',
                    'distributionCenter:id,name',
                ])
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(['id', 'order_number', 'customer_id', 'distribution_center_id', 'status', 'created_at']);

            return response()->json([
                'data' => $orders->map(function ($o) {
                    $user = $o->customer?->user;
                    $name = $user ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : null;
                    return [
                        'id' => $o->id,
                        'order_number' => $o->order_number,
                        'status' => (string) $o->status,
                        'status_label' => $o->status->label,
                        'customer_name' => $name ?: 'Client',
                        'distribution_center' => $o->distributionCenter?->only(['id', 'name']),
                        'created_at' => $o->created_at?->toIso8601String(),
                    ];
                }),
            ]);
        })->name('orders.index');

        // GET /api/manager/orders/{order} — detail + per-item scan progress.
        // The Order model's items relation is named `items` (not `orderItems`).
        Route::get('/orders/{order}', function (Request $request, Order $order) use ($ensureManager) {
            $ensureManager($request);
            $order->load([
                'customer.user:id,first_name,last_name',
                'distributionCenter:id,name',
                'items.productCategory',
                'items.bottles:id,barcode',
            ]);

            $user = $order->customer?->user;
            $customerName = $user ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : 'Client';

            return response()->json([
                'data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => (string) $order->status,
                    'status_label' => $order->status->label,
                    'can_scan_bottles' => $order->canScanBottles(),
                    'customer_name' => $customerName,
                    'distribution_center' => $order->distributionCenter?->only(['id', 'name']),
                    'created_at' => $order->created_at?->toIso8601String(),
                    'order_items' => $order->items->map(function ($item) {
                        $cat = $item->productCategory;
                        $typeInstance = $cat?->productTypeInstance;
                        $scanned = $item->bottles->count();
                        return [
                            'id' => $item->id,
                            'product_category_id' => $item->product_category_id,
                            'category_name' => $cat?->name,
                            'bottle_type_name' => $typeInstance?->name,
                            'capacity' => $typeInstance?->capacity,
                            'expected' => (int) $item->quantity,
                            'scanned' => $scanned,
                            'done' => $scanned >= (int) $item->quantity,
                            'scanned_barcodes' => $item->bottles->pluck('barcode')->all(),
                        ];
                    }),
                ],
            ]);
        })->name('orders.show');

        // POST /api/manager/orders/{order}/scan-full-bottle
        // Body: { barcode: string }
        // Delegates to OrderBottleScanService — the same service the web
        // Livewire scanner uses, so behavior is guaranteed identical.
        // The service decides which order_item the bottle goes to (based on
        // its type), validates eligibility, prevents dups, etc.
        Route::post('/orders/{order}/scan-full-bottle', function (Request $request, Order $order, OrderBottleScanService $service) use ($ensureManager) {
            $ensureManager($request);
            $validated = $request->validate([
                'barcode' => ['required', 'string', 'max:60'],
            ]);

            try {
                $item = $service->scanBottle($order, trim($validated['barcode']));
                $scanned = $item->bottles()->count();
                return response()->json([
                    'ok' => true,
                    'message' => 'Bouteille liée à la commande.',
                    'progress' => [
                        'order_item_id' => $item->id,
                        'scanned' => $scanned,
                        'expected' => (int) $item->quantity,
                    ],
                ]);
            } catch (BottleScanException $e) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            } catch (\Throwable $e) {
                Log::error('manager.orders.scan-full-bottle failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                return response()->json(['ok' => false, 'message' => 'Erreur serveur: '.$e->getMessage()], 500);
            }
        })->name('orders.scan-full-bottle');
    });
