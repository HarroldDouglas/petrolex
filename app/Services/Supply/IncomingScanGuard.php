<?php

declare(strict_types=1);

namespace App\Services\Supply;

use App\Enums\BottleStatus;
use App\Enums\SupplierDeliveryStatus;
use App\Models\Bottle;
use App\Models\SupplierDeliveryBottle;
use Illuminate\Database\Eloquent\Builder;

class IncomingScanGuard
{
    /**
     * Check whether an existing bottle can be received (incoming scan) into
     * the given supply product line. Returns the rejection message, or null
     * when the scan is allowed.
     *
     * The check is based on active scans, not only on the bottle status:
     * a bottle can be held by at most ONE active incoming scan across all
     * in-progress supplies. Status alone is not enough — historical
     * soft-deleted scans and released bottles (returned_to_supplier) would
     * otherwise let the same bottle be received into several supplies at
     * once.
     */
    public function rejectionReason(Bottle $bottle, int $productTypeId): ?string
    {
        $activeScan = SupplierDeliveryBottle::query()
            ->incoming()
            ->where('bottle_id', $bottle->id)
            ->whereHas('bottleType.supplierDelivery', function (Builder $supply): void {
                $supply->where('status', SupplierDeliveryStatus::IN_PROGRESS()->value);
            })
            ->with('bottleType.supplierDelivery:id,delivery_number')
            ->first();

        if ($activeScan) {
            if ((int) $activeScan->supplier_delivery_product_type_id === $productTypeId) {
                return 'Cette bouteille a déjà été scannée pour cet approvisionnement.';
            }

            $number = $activeScan->bottleType?->supplierDelivery?->delivery_number ?? 'inconnu';

            return "Bouteille déjà scannée dans l'approvisionnement {$number}. Retirez-la de cet approvisionnement avant de la scanner ici.";
        }

        $inCirculation = [
            BottleStatus::IN_STOCK()->value,
            BottleStatus::WITH_DELIVERY_PERSON()->value,
            BottleStatus::WITH_CLIENT()->value,
        ];

        if (in_array($bottle->status->value, $inCirculation, true)) {
            return "Bouteille déjà active dans le système (statut: {$bottle->status->label}).";
        }

        // returned_to_supplier, lost_stolen, or an orphaned pending_reception
        // (no active scan holds it) → receivable.
        return null;
    }
}
