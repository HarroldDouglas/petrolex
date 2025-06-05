<!-- Order Status start -->
<div class="col-xxl-4 mt-3">
    <div class="card equal-card">
        <div class="card-header">
            <h5>Statut de la Commande</h5>
        </div>
        <div class="card-body">
            <ul class="app-timeline-box">

                <li class="timeline-section">
                    <div class="timeline-icon">
                        <span class="text-light-primary h-35 w-35 d-flex-center b-r-50">
                            <i class="ti ti-shopping-cart f-s-20"></i>
                        </span>
                    </div>
                    <div class="timeline-content bg-light-primary b-1-primary">
                        <div class="d-flex justify-content-between align-items-center timeline-flex">
                            <h6 class="mt-2 text-primary">Commande Passée</h6>
                            <span class="badge text-bg-primary ms-2">{{ $order->order_date->diffForHumans() }}</span>
                        </div>
                        <p class="mt-2 text-dark">Une commande a été passée.</p>
                        <p class="text-primary">{{ $order->order_date->format('D, d M Y - H:i') }}</p>
                    </div>
                </li>
                <li class="timeline-section">
                    <div class="timeline-icon">
                        <span class="text-light-secondary h-35 w-35 d-flex-center b-r-50">
                            <i class="ti ti-checks f-s-20"></i>
                        </span>
                    </div>
                    <div class="timeline-content bg-light-secondary b-1-secondary">
                        <div class="d-flex justify-content-between align-items-center timeline-flex">
                            <h6 class="mt-2 text-secondary">En cours de livraison</h6>
                            <span class="badge text-bg-secondary">Il y a 50 min</span>
                        </div>
                        <p class="mt-2 text-dark">
                            Le livreur est déjà en route pour livrer votre commande.
                        </p>
                        <p class="text-dark-secondary">Jeu, 20 Déc 2024 - 6:48AM</p>
                    </div>
                </li>
                <li class="timeline-section">
                    <div class="timeline-icon">
                        <span class="text-light-success h-35 w-35 d-flex-center b-r-50">
                            <i class="ti ti-truck-delivery f-s-20"></i>
                        </span>
                    </div>
                    <div class="timeline-content bg-light-success b-1-success">
                        <div class="d-flex justify-content-between align-items-center timeline-flex">
                            <h6 class="mt-2 text-success">Livrée</h6>
                            <span class="badge text-bg-success ms-2">Il y a 1 heure</span>
                        </div>
                        <p class="mt-2 text-dark">
                            Votre article a bien été livré sur votre confirmation.
                        </p>
                        <p class="text-success">Jeu, 20 Déc 2024 - 5:48AM</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    @if ($order->canBeRated() && ($order->comments || $order->rating))
        <div class="card mt-3">
            <div class="card-header">
                <h5>Évaluation du client</h5>
            </div>
            <div class="card-body">
                @if ($order->rating)
                    <div class="d-flex justify-content-between">
                        <h6 class="f-w-600 text-dark"><i class="ti ti-star f-s-18 me-2 text-warning"></i>Note</h6>
                        <div class="text-end">
                            <p>{{ $order->rating }}/5</p>
                        </div>
                    </div>
                @endif

                @if ($order->comments)
                    <div class="mt-3">
                        <h6 class="f-w-600 text-dark"><i class="ti ti-message f-s-18 me-2 text-info"></i>Commentaire
                            client</h6>
                        <p class="mt-2">{{ $order->comments }}</p>
                    </div>
                @endif

                @if ($order->center_comments)
                    <div class="mt-3">
                        <h6 class="f-w-600 text-dark"><i
                                class="ti ti-message-circle f-s-18 me-2 text-primary"></i>Commentaire centre</h6>
                        <p class="mt-2">{{ $order->center_comments }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
<!-- Order Status end -->
