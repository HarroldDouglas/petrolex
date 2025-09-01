<?php

namespace App\Console\Commands\Test;

use App\Enums\OrderStatus;
use App\Models\DeliveryTracking;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetOrderTrackingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:reset-tracking {order_number? : Le numéro de commande à réinitialiser}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialise le tracking d\'une commande (remet le statut à CONFIRMED et supprime le tracking)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_number');

        if (! $orderId) {
            $orderId = $this->ask('Quel est le numéro de commande à réinitialiser ?');
        }

        if (! $orderId) {
            $this->error('Numéro de commande requis !');

            return 1;
        }

        // Rechercher la commande
        $order = Order::find($orderId);

        if (! $order) {
            $this->error("Commande {$orderId} introuvable !");

            return 1;
        }

        $this->info("📦 Commande trouvée : {$order->order_number} (ID: {$order->id})");
        $this->info("📊 Statut actuel : {$order->status->value}");

        // Confirmer l'action
        if (! $this->confirm('Voulez-vous vraiment réinitialiser cette commande ?')) {
            $this->info('Opération annulée.');

            return 0;
        }

        DB::beginTransaction();

        try {
            // 1. Supprimer le tracking existant
            $deletedTracking = DeliveryTracking::where('order_id', $order->id)->delete();

            if ($deletedTracking > 0) {
                $this->info("🗑️  Tracking supprimé ({$deletedTracking} enregistrement(s))");
            } else {
                $this->info('ℹ️  Aucun tracking à supprimer');
            }

            // 2. Remettre le statut à CONFIRMED
            $oldStatus = $order->status->value;
            $order->status = OrderStatus::CONFIRMED();
            $order->save();

            $this->info("✅ Statut mis à jour : {$oldStatus} → {$order->status->value}");

            DB::commit();

            $this->info("🎉 Commande {$orderId} réinitialisée avec succès !");
            $this->info('📋 Vous pouvez maintenant retester le tracking sur cette commande.');

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Erreur lors de la réinitialisation : '.$e->getMessage());

            return 1;
        }
    }
}
