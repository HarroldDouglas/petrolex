<?php

namespace App\Listeners\Order;

use App\Events\OrderCreatedEvent;
use App\Services\DeliveryPersonService;
use App\Services\Order\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AssignDeliveryPersonToOrderListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly DeliveryPersonService $deliveryPersonService,
        private readonly OrderService $orderService
    ) {}

    public function handle(OrderCreatedEvent $event): void
    {
        $order = $event->order;
        if ($order->delivery_person_id === null) {
            // ==================================================================================
            // ⚠️ CODE TEMPORAIRE POUR LES TESTS - À SUPPRIMER AVANT LA MISE EN PRODUCTION ⚠️
            // ==================================================================================
            // Force l'affectation au livreur delivery1@test.com pour faciliter les tests
            // TODO: SUPPRIMER CE CODE APRÈS LES TESTS !
            $testDeliveryPerson = \App\Models\DeliveryPerson::whereHas('user', function ($query) {
                $query->where('email', 'delivery1@test.com');
            })->first();

            if ($testDeliveryPerson) {
                $this->orderService->assignDeliveryPerson($order, $testDeliveryPerson->id);

                return;
            }
            // ==================================================================================
            // FIN DU CODE TEMPORAIRE DE TEST
            // ==================================================================================

            // Code normal de production (décommenter après les tests)
            $deliveryPerson = $this->deliveryPersonService->findLeastBusyDeliveryPerson($order->distribution_center_id);

            if ($deliveryPerson) {
                $this->orderService->assignDeliveryPerson($order, $deliveryPerson->id);
            }
        }
    }
}
