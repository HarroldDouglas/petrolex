<?php

namespace App\Http\Controllers\Supply;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\SupplierDeliveryRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RegisterProductsController extends Controller
{
    protected SupplierDeliveryRepositoryInterface $supplierDeliveryRepository;

    public function __construct(SupplierDeliveryRepositoryInterface $supplierDeliveryRepository)
    {
        $this->supplierDeliveryRepository = $supplierDeliveryRepository;
    }

    /**
     * Register products for a specific supply.
     *
     * Route: GET /supplies/{supply_id}/register-products
     * Name: supplies.register-products
     */
    public function __invoke(Request $request, $supply_id): View
    {
        $supply = $this->supplierDeliveryRepository->getWithProducts($supply_id);

        if (! $supply) {
            abort(404, 'Approvisionnement non trouvé');
        }

        return view('supplies.register-products', [
            'supply' => $supply,
        ]);
    }
}
