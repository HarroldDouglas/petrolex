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
                <div class="notification-message head-box">
                    <div class="message-images">
                        <span
                            class="bg-secondary h-35 w-35 d-flex-center b-r-10 position-relative">
                            <img alt="avtar" class="img-fluid b-r-10"
                                src="../assets/images/ai_avtar/6.jpg">
                            <span
                                class="position-absolute bottom-30 end-0 p-1 bg-secondary border border-light rounded-circle notification-avtar"></span>
                        </span>
                    </div>
                    <div class="message-content-box flex-grow-1 ps-2">

                        <a class="f-s-15 text-secondary mb-0" href="{{ route('orders.list') }}"
                            target="_blank"><span class="f-w-500 text-secondary">Gene Hart</span>
                            wants to
                            edit <span class="f-w-500 text-secondary">Report.doc</span></a>
                        <div>
                            <a class="d-inline-block f-w-500 text-success me-1"
                                href="#">Approve</a>
                            <a class="d-inline-block f-w-500 text-danger" href="#">Deny</a>
                        </div>
                        <span class="badge text-light-primary mt-2"> sep 23 </span>

                    </div>
                    <div class="align-self-start text-end">
                        <i class="iconoir-xmark close-btn"></i>
                    </div>
                </div>
                <div class="notification-message head-box">
                    <div class="message-images">
                        <span
                            class="bg-light-dark h-35 w-35 d-flex-center b-r-10 position-relative">
                            <i class="ph-duotone  ph-truck f-s-18"></i>
                        </span>
                    </div>
                    <div class="message-content-box flex-grow-1 ps-2">
                        <a class="f-s-15 text-secondary mb-0" href="{{ route('orders.list') }}"
                            target="_blank">Hey
                            <span class="f-w-500 text-secondary">Emery McKenzie</span>,
                            get ready: Your order from <span
                                class="f-w-500 text-secondary">@Shopper.com</span>
                            is out for delivery today!</a>
                        <span class="badge text-light-info mt-2"> sep 23 </span>

                    </div>
                    <div class="align-self-start text-end">
                        <i class="iconoir-xmark close-btn"></i>
                    </div>
                </div>
                <div class="notification-message head-box">
                    <div class="message-images">
                        <span
                            class="bg-secondary h-35 w-35 d-flex-center b-r-10 position-relative">
                            <img alt="" class="img-fluid b-r-10"
                                src="../assets/images/ai_avtar/2.jpg">
                            <span
                                class="position-absolute  end-0 p-1 bg-secondary border border-light rounded-circle notification-avtar"></span>
                        </span>
                    </div>
                    <div class="message-content-box flex-grow-1 ps-2">
                        <a class="f-s-15 text-secondary mb-0" href="{{ route('orders.list') }}"
                            target="_blank"><span class="f-w-500 text-secondary">Simon
                                Young</span> shared
                            a file called <span
                                class="f-w-500 text-secondary">Dropdown.pdf</span></a>
                        <span class="badge text-light-success mt-2"> 30 min</span>

                    </div>
                    <div class="align-self-start text-end">
                        <i class="iconoir-xmark close-btn"></i>
                    </div>
                </div>
                <div class="notification-message head-box">
                    <div class="message-images">
                        <span
                            class="bg-secondary h-35 w-35 d-flex-center b-r-10 position-relative">
                            <img alt="" class="img-fluid b-r-10"
                                src="../assets/images/ai_avtar/5.jpg">
                            <span
                                class="position-absolute end-0 p-1 bg-secondary border border-light rounded-circle notification-avtar"></span>
                        </span>
                    </div>
                    <div class="message-content-box flex-grow-1 ps-2">
                        <a class="f-s-15 text-secondary mb-0" href="{{ route('orders.list') }}"
                            target="_blank"><span class="f-w-500 text-secondary">Becky G.
                                Hayes</span> has
                            added a comment to <span
                                class="f-w-500 text-secondary">Final_Report.pdf</span></a>
                        <span class="badge text-light-warning mt-2"> 45 min</span>
                    </div>
                    <div class="align-self-start text-end">
                        <i class="iconoir-xmark close-btn"></i>
                    </div>
                </div>
                <div class="notification-message head-box">
                    <div class="message-images">
                        <span
                            class="bg-secondary h-35 w-35 d-flex-center b-r-10 position-relative">
                            <img alt="" class="img-fluid b-r-10"
                                src="../assets/images/ai_avtar/1.jpg">
                            <span
                                class="position-absolute  end-0 p-1 bg-secondary border border-light rounded-circle notification-avtar"></span>
                        </span>
                    </div>
                    <div class="message-content-box flex-grow-1 ps-2">
                        <a class="f-s-15 text-secondary mb-0" href="{{ route('orders.list') }}"
                            target="_blank"><span class="f-w-600 text-secondary">Romaine
                                Nadeau</span>
                            invited you to join a meeting
                        </a>
                        <div>
                            <a class="d-inline-block f-w-500 text-success me-1"
                                href="#">Join</a>
                            <a class="d-inline-block f-w-500 text-danger"
                                href="#">Decline</a>
                        </div>

                        <span class="badge text-light-secondary mt-2"> 1 hour ago </span>
                    </div>
                    <div class="align-self-start text-end">
                        <i class="iconoir-xmark close-btn"></i>
                    </div>
                </div>

                <div class="hidden-massage py-4 px-3">
                    <img alt="" class="w-50 h-50 mb-3 mt-2"
                        src="../assets/images/icons/bell.png">
                    <div>
                        <h6 class="mb-0">Notification Not Found</h6>
                        <p class="text-secondary">When you have any notifications added
                            here,will
                            appear here.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</li>