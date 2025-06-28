<li class="header-notification">
    <a aria-controls="notificationcanvasRight" class="d-block head-icon position-relative"
        data-bs-target="#notificationcanvasRight" data-bs-toggle="offcanvas" href="#"
        role="button">
        <i class="iconoir-bell"></i>
        <span
            class="position-absolute translate-middle p-1 bg-success border border-light rounded-circle animate__animated animate__fadeIn animate__infinite animate__slower"></span>
    </a>
    <div aria-labelledby="notificationcanvasRightLabel"
        class="offcanvas offcanvas-end header-notification-canvas" id="notificationcanvasRight"
        tabindex="-1">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="notificationcanvasRightLabel">
                Notification</h5>
            <button aria-label="Close" class="btn-close" data-bs-dismiss="offcanvas"
                type="button"></button>
        </div>
        <div class="offcanvas-body notification-offcanvas-body app-scroll p-0">
            <div class="head-container notification-head-container">
                <!-- Nouvelle commande créée -->
                <div class="notification-message head-box">
                    <div class="message-images">
                        <span
                            class="bg-light-primary h-35 w-35 d-flex-center b-r-10 position-relative">
                            <i class="ph-duotone ph-shopping-cart-simple f-s-18"></i>
                        </span>
                    </div>
                    <div class="message-content-box flex-grow-1 ps-2">
                        <a class="f-s-15  mb-0" href="{{ route('orders.list') }}"
                            target="_blank">
                            <span class="f-w-500 ">Nouvelle commande</span> 
                            créée avec succès - Réf. <span class="f-w-500 ">CMD-12345</span>
                        </a>
                        <div>
                            <span class="d-inline-block f-w-500 me-1">
                                Client: <span class="text-primary">Entreprise ABC</span>
                            </span> |
                            <span class="d-inline-block f-w-500 ms-1">
                                Total: <span class="text-primary">125.000 FCFA</span>
                            </span>
                        </div>
                        <span class="badge text-light-primary mt-2">Il y a 10 min</span>
                    </div>
                    <div class="align-self-start text-end">
                        <i class="iconoir-xmark close-btn"></i>
                    </div>
                </div>
                
                <!-- Commande en cours de livraison -->
                <div class="notification-message head-box">
                    <div class="message-images">
                        <span
                            class="bg-light-info h-35 w-35 d-flex-center b-r-10 position-relative">
                            <i class="ph-duotone ph-truck f-s-18"></i>
                        </span>
                    </div>
                    <div class="message-content-box flex-grow-1 ps-2">
                        <a class="f-s-15  mb-0" href="{{ route('orders.list') }}"
                            target="_blank">
                            La commande <span class="f-w-500 ">CMD-10982</span>
                            est en cours de livraison
                        </a>
                        <div>
                            <span class="d-inline-block f-w-500">
                                Client: <span class="text-info">Station Mobil</span> |
                                ETA: <span class="text-info">Aujourd'hui, 14:30</span>
                            </span>
                        </div>
                        <span class="badge text-light-info mt-2">Il y a 45 min</span>
                    </div>
                    <div class="align-self-start text-end">
                        <i class="iconoir-xmark close-btn"></i>
                    </div>
                </div>
                
                <!-- Commande livrée -->
                <div class="notification-message head-box">
                    <div class="message-images">
                        <span
                            class="bg-light-success h-35 w-35 d-flex-center b-r-10 position-relative">
                            <i class="ph-duotone ph-check-circle f-s-18"></i>
                        </span>
                    </div>
                    <div class="message-content-box flex-grow-1 ps-2">
                        <a class="f-s-15  mb-0" href="{{ route('orders.list') }}"
                            target="_blank">
                            La commande <span class="f-w-500 ">CMD-10876</span>
                            a été livrée avec succès
                        </a>
                        <div>
                            <span class="d-inline-block f-w-500">
                                Client: <span class="text-success">Total Energies</span> |
                                Quantité: <span class="text-success">250 bouteilles</span>
                            </span>
                        </div>
                        <span class="badge text-light-success mt-2">Il y a 2h</span>
                    </div>
                    <div class="align-self-start text-end">
                        <i class="iconoir-xmark close-btn"></i>
                    </div>
                </div>
                
                <!-- Commande annulée -->
                <div class="notification-message head-box">
                    <div class="message-images">
                        <span
                            class="bg-light-danger h-35 w-35 d-flex-center b-r-10 position-relative">
                            <i class="ph-duotone ph-x-circle f-s-18"></i>
                        </span>
                    </div>
                    <div class="message-content-box flex-grow-1 ps-2">
                        <a class="f-s-15  mb-0" href="{{ route('orders.list') }}"
                            target="_blank">
                            La commande <span class="f-w-500 ">CMD-11023</span>
                            a été annulée
                        </a>
                        <div>
                            <span class="d-inline-block f-w-500">
                                Client: <span class="text-danger">Station Shell</span> |
                                Raison: <span class="text-danger">Erreur de commande</span>
                            </span>
                        </div>
                        <span class="badge text-light-danger mt-2">Il y a 3h</span>
                    </div>
                    <div class="align-self-start text-end">
                        <i class="iconoir-xmark close-btn"></i>
                    </div>
                </div>
                
                <!-- Modification de commande -->
                <div class="notification-message head-box">
                    <div class="message-images">
                        <span
                            class="bg-light-warning h-35 w-35 d-flex-center b-r-10 position-relative">
                            <i class="ph-duotone ph-note-pencil f-s-18"></i>
                        </span>
                    </div>
                    <div class="message-content-box flex-grow-1 ps-2">
                        <a class="f-s-15  mb-0" href="{{ route('orders.list') }}"
                            target="_blank">
                            La commande <span class="f-w-500 ">CMD-11042</span>
                            a été modifiée
                        </a>
                        <div>
                            <span class="d-inline-block f-w-500">
                                Client: <span class="text-warning">Petrolex SA</span> |
                                Modification: <span class="text-warning">Quantité +50</span>
                            </span>
                        </div>
                        <span class="badge text-light-warning mt-2">Il y a 5h</span>
                    </div>
                    <div class="align-self-start text-end">
                        <i class="iconoir-xmark close-btn"></i>
                    </div>
                </div>

                <div class="hidden-massage py-4 px-3">
                    <img alt="" class="w-50 h-50 mb-3 mt-2"
                        src="{{ asset('assets/images/icons/bell.png') }}">
                    <div>
                        <h6 class="mb-0">Aucune notification</h6>
                        <p class="">Vous n'avez pas de nouvelles notifications concernant les commandes.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</li>