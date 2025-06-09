<div class="col-lg-12">
    <div class="row">
        <div class="col-lg-3">
            <div class="card ticket-card bg-light-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <p class="f-s-16 mb-0">En stock</p>
                        <div class="h-40 w-40 d-flex-center">
                            <i class="iconoir-fast-arrow-down-square f-s-45 text-primary"></i>
                        </div>
                    </div>
                    <h3 class="text-primary-dark">{{ $inStock }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card ticket-card bg-light-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <p class="f-s-16 mb-0">En cours de<br>livraison</p>
                        <div class="h-40 w-40 d-flex-center">
                            <i class="iconoir-fast-arrow-right-square f-s-45 text-warning"></i>
                        </div>
                    </div>
                    <h3 class="text-warning-dark">{{ $withDeliveryPerson }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card ticket-card bg-light-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <p class="f-s-16 mb-0">Vendus</p>
                        <div class="h-40 w-40 d-flex-center">
                            <i class="iconoir-shopping-bag-arrow-up f-s-45 text-success"></i>
                        </div>
                    </div>
                    <h3 class="text-success-dark">{{ $withClient }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card ticket-card bg-light-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <p class="f-s-16 mb-0">Perdus</p>
                        <div class="h-40 w-40 d-flex-center">
                            <i class="iconoir-file-not-found f-s-45 text-danger"></i>
                        </div>
                    </div>
                    <h3 class="text-danger-dark">{{ $lostStolen }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>