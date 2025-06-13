<div class="col-lg-12">
    <div class="row">
        <x-dashboard.stat-block
            bg-color="light-primary"
            text-color="primary"
            icon="arrow-circle-down"
            title="Total des Entrées"
            subtitle="(Bouteilles Pleines)"
            :value="$totalSupplied"
        />
        
        <x-dashboard.stat-block
            bg-color="light-warning"
            text-color="warning"
            icon="arrow-circle-up"
            title="Total des Bouteilles"
            subtitle="Pleines Vendues"
            :value="$totalSoldBottles"
        />
        
        <x-dashboard.stat-block
            bg-color="light-success"
            text-color="success"
            icon="arrows-left-right"
            title="Total des Recharges"
            subtitle="___"
            :value="$totalExchanges"
        />
        
        <x-dashboard.stat-block
            bg-color="light-danger"
            text-color="danger"
            icon="package"
            title="Stock Actuel"
            subtitle="(Bouteilles pleines / vides)"
            :value="$fullBottles . ' / ' .$emptyBottles"
        />
    </div>
</div>