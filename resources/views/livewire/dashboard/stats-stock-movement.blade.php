<div class="col-lg-12">
    <div class="row">
        <x-dashboard.stat-block
            bg-color="light-primary"
            text-color="primary"
            icon="arrow-circle-down"
            title="Total des Entrées"
            subtitle="(Consignes Pleines)"
            :value="$totalSupplied"
            :url="$this->totalSuppliedUrl"
        />
        
        <x-dashboard.stat-block
            bg-color="light-warning"
            text-color="warning"
            icon="arrow-circle-up"
            title="Total des Consignes"
            subtitle="Vendues"
            :value="$totalSoldBottles"
            :url="$this->totalSoldBottlesUrl"
        />
        
        <x-dashboard.stat-block
            bg-color="light-success"
            text-color="success"
            icon="arrows-left-right"
            title="Total des Recharges"
            subtitle="___"
            :value="$totalExchanges"
            :url="$this->totalExchangesUrl"
        />
        
        <x-dashboard.stat-block
            bg-color="light-danger"
            text-color="danger"
            icon="package"
            title="Stock Actuel"
            subtitle="(Consignes pleines / vides)"
            :value="$fullBottles . ' / ' .$emptyBottles"
            :url="$this->currentStockUrl"
        />
    </div>
</div>