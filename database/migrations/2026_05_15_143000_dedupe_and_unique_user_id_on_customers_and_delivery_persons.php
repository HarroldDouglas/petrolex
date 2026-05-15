<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $this->reassignAndDedupeDeliveryPersons();
            $this->reassignAndDedupeCustomers();
        });

        Schema::table('delivery_persons', function (Blueprint $table) {
            $table->unique('user_id', 'delivery_persons_user_id_unique');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unique('user_id', 'customers_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_persons', function (Blueprint $table) {
            $table->dropUnique('delivery_persons_user_id_unique');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_user_id_unique');
        });
    }

    private function reassignAndDedupeDeliveryPersons(): void
    {
        $duplicates = DB::table('delivery_persons')
            ->select('user_id', DB::raw('MIN(id) as keep_id'), DB::raw('GROUP_CONCAT(id) as all_ids'))
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $keepId = (int) $dup->keep_id;
            $deleteIds = array_diff(array_map('intval', explode(',', $dup->all_ids)), [$keepId]);

            if (empty($deleteIds)) {
                continue;
            }

            DB::table('orders')->whereIn('delivery_person_id', $deleteIds)->update(['delivery_person_id' => $keepId]);
            DB::table('bottle_movements')->whereIn('delivery_person_id', $deleteIds)->update(['delivery_person_id' => $keepId]);
            DB::table('delivery_persons')->whereIn('id', $deleteIds)->delete();
        }
    }

    private function reassignAndDedupeCustomers(): void
    {
        $duplicates = DB::table('customers')
            ->select('user_id', DB::raw('MIN(id) as keep_id'), DB::raw('GROUP_CONCAT(id) as all_ids'))
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $keepId = (int) $dup->keep_id;
            $deleteIds = array_diff(array_map('intval', explode(',', $dup->all_ids)), [$keepId]);

            if (empty($deleteIds)) {
                continue;
            }

            DB::table('orders')->whereIn('customer_id', $deleteIds)->update(['customer_id' => $keepId]);
            DB::table('customer_delivery_addresses')->whereIn('customer_id', $deleteIds)->update(['customer_id' => $keepId]);
            DB::table('wallet_transactions')->whereIn('customer_id', $deleteIds)->update(['customer_id' => $keepId]);
            DB::table('bottle_movements')->whereIn('customer_id', $deleteIds)->update(['customer_id' => $keepId]);
            DB::table('customers')->whereIn('id', $deleteIds)->delete();
        }
    }
};
