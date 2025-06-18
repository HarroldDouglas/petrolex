<?php

namespace App\View\Components\Order\Detail;

use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use Illuminate\View\Component;
use Illuminate\View\View;

class CancellationInfo extends Component
{
    public ?User $user = null;
    public ?string $cancellationReason = null;
    public ?string $cancellationDate = null;
    public ?Refund $refund = null;

    /**
     * Create a new component instance.
     */
    public function __construct(Order $order)
    {
        if ($order->cancelled_by) {
            $user = User::find($order->cancelled_by);
            $this->user = $user;
        }

        $this->cancellationReason = $order->cancelled_reason;
        $this->cancellationDate = $order->cancelled_at ? $order->cancelled_at->format('D, d M Y - H:i') : null;

        // Get refund information if available
        if ($order->hasRefunds()) {
            $this->refund = $order->refunds()
                ->where('status', \App\Enums\PaymentStatus::PAID())
                ->latest()
                ->first();
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.order.detail.cancellation-info');
    }
}
