<div class="col-lg-12">
    <div class="row">
        <x-dashboard.stat-block
            bg-color="light-primary"
            text-color="primary"
            icon="currency-circle-dollar"
            title="Chiffre d'affaires"
            subtitle="(en CFA)"
            :value="$revenue"
        />
        
        <x-dashboard.stat-block
            bg-color="light-warning"
            text-color="warning"
            icon="clock-countdown"
            title="Commandes"
            subtitle="en attente"
            :value="$pendingOrders"
        />
        
        <x-dashboard.stat-block
            bg-color="light-success"
            text-color="success"
            icon="check-circle"
            title="Commandes"
            subtitle="livrées"
            :value="$deliveredOrders"
        />
        
        <x-dashboard.stat-block
            bg-color="light-danger"
            text-color="danger"
            icon="x-circle"
            title="Commandes"
            subtitle="annulées"
            :value="$canceledOrders"
        />
    </div>
</div>