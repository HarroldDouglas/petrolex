<?php

namespace App\View\Components\Order\Detail;

use App\Enums\OrderStatus;
use App\Models\Order;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Status extends Component
{
    public array $steps;
    public int $currentStep;
    public bool $canShowFeedback;
    public string $feedbackTitle;
    public string $centerCommentsLabel;

    /**
     * Create a new component instance.
     */
    public function __construct(public Order $order)
    {
        $this->steps = $this->buildTimelineSteps();
        $this->currentStep = $this->getCurrentStepIndex();
        $this->canShowFeedback = $this->canShowFeedback();
        $this->feedbackTitle = $this->getFeedbackTitle();
        $this->centerCommentsLabel = $this->getCenterCommentsLabel();
    }

    /**
     * Build timeline steps based on order status and dates
     */
    private function buildTimelineSteps(): array
    {
        $steps = [];

        // Step 1: Order Confirmed (always present)
        $steps[] = [
            'key' => OrderStatus::PAID()->value,
            'icon' => 'ti-shopping-cart',
            'title' => 'Commande '.OrderStatus::PAID()->label,
            'description' => 'Votre commande a été confirmée et est en cours de traitement.',
            'color' => 'primary',
            'date' => $this->order->paid_at,
            'completed' => true,
        ];

        // Step 2: Processing (if order reached processing status)
        if ($this->order->processing_at) {
            $steps[] = [
                'key' => OrderStatus::PROCESSING()->value,
                'icon' => 'ti-truck',
                'title' => 'Commande '.OrderStatus::PROCESSING()->label,
                'description' => 'Le livreur est en route pour livrer votre commande.',
                'color' => 'secondary',
                'date' => $this->order->processing_at,
                'completed' => true,
                'delivery_person' => $this->order->deliveryPerson,
            ];
        }

        // Step 3: Final status (delivered or cancelled)
        if ($this->order->delivered_at) {
            $steps[] = [
                'key' => OrderStatus::DELIVERED()->value,
                'icon' => 'ti-truck-delivery',
                'title' => 'Commande '.OrderStatus::DELIVERED()->label,
                'description' => 'Votre commande a été livrée avec succès.',
                'color' => 'success',
                'date' => $this->order->delivered_at,
                'completed' => true,
            ];
        } elseif ($this->order->cancelled_at) {
            $steps[] = [
                'key' => OrderStatus::CANCELLED()->value,
                'icon' => 'ti-x',
                'title' => 'Commande '.OrderStatus::CANCELLED()->label,
                'description' => 'Votre commande a été annulée.',
                'color' => 'danger',
                'date' => $this->order->cancelled_at,
                'completed' => true,
                'is_cancelled' => true,
                'order' => $this->order,
            ];
        }

        return $steps;
    }

    /**
     * Get current step index based on order status
     */
    private function getCurrentStepIndex(): int
    {
        return match ($this->order->status) {
            OrderStatus::PAID() => 0,
            OrderStatus::PROCESSING() => $this->hasProcessingStep() ? 1 : 0,
            OrderStatus::DELIVERED(), OrderStatus::CANCELLED() => count($this->steps) - 1,
            default => 0,
        };
    }

    /**
     * Check if order has processing step
     */
    private function hasProcessingStep(): bool
    {
        return $this->order->processing_at !== null;
    }

    /**
     * Check if order can show feedback section
     */
    private function canShowFeedback(): bool
    {
        return $this->order->canBeRated() &&
               ($this->order->comments || $this->order->rating || $this->order->center_comments);
    }

    /**
     * Get feedback section title
     */
    private function getFeedbackTitle(): string
    {
        return $this->order->status === OrderStatus::DELIVERED()
            ? 'Évaluation du client'
            : 'Détails de l\'annulation';
    }

    /**
     * Get center comments label
     */
    private function getCenterCommentsLabel(): string
    {
        return $this->order->status === OrderStatus::CANCELLED()
            ? 'Motif d\'annulation'
            : 'Commentaire centre';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.order.detail.status');
    }
}
