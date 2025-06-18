<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\SupplierDeliveryRepositoryInterface;
use Illuminate\Http\Request;

class ScanBottlesController extends Controller
{
    protected SupplierDeliveryRepositoryInterface $supplierDeliveryRepository;

    public function __construct(SupplierDeliveryRepositoryInterface $supplierDeliveryRepository)
    {
        $this->supplierDeliveryRepository = $supplierDeliveryRepository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $supply_id)
    {
        $supply = $this->supplierDeliveryRepository->getWithProducts($supply_id);

        if (! $supply) {
            abort(404, 'Approvisionnement non trouvé');
        }

        return view('supplies.scan-bottles', [
            'supply' => $supply,
        ]);
    }
}
