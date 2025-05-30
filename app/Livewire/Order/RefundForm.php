<?php

namespace App\Livewire\Order;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Refund;
use Livewire\Component;

class RefundForm extends Component
{
    public Order $order;

    public $refund_method = '';
    public $refund_identifier = '';
    public $reason = '';
    public $confirmation = false;
    public $availablePaymentMethods = [];

    protected $rules = [
        'refund_method' => 'required',
        'refund_identifier' => 'required|string|max:100',
        'reason' => 'nullable|string|max:500',
        'confirmation' => 'required|accepted',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function getIsFormValidProperty()
    {
        return ! empty($this->refund_method) &&
               ! empty($this->refund_identifier) &&
               $this->confirmation === true;
    }

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->availablePaymentMethods = [
            PaymentMethod::ORANGE_MONEY()->value => PaymentMethod::ORANGE_MONEY()->label,
            PaymentMethod::MOBILE_MONEY()->value => PaymentMethod::MOBILE_MONEY()->label,
        ];
    }

    public function processRefund()
    {
        $this->validate();

        try {
            Refund::create([
                'order_id' => $this->order->id,
                'initiated_by' => auth()->id(),
                'refund_method' => PaymentMethod::from($this->refund_method),
                'refund_identifier' => $this->refund_identifier,
                'amount' => $this->order->total_amount,
                'reason' => $this->reason,
                'status' => PaymentStatus::PENDING(),
                'initiated_at' => now(),
            ]);

            $this->reset(['refund_method', 'refund_identifier', 'reason', 'confirmation']);

            session()->flash('success', 'Remboursement initié avec succès !');
            $this->dispatch('close-modal');

        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de l\'initiation du remboursement: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.order.refund-form');
    }
}
