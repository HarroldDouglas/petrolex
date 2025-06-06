@push('styles')
    <style>
        .timeline-section.active .timeline-icon span {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(var(--bs-primary-rgb), 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(var(--bs-primary-rgb), 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(var(--bs-primary-rgb), 0);
            }
        }

        .star-rating i {
            margin-right: 1px;
        }
    </style>
@endpush

<!-- Order Status start -->
<div class="col-xxl-4 mt-3">
    <div class="card equal-card">
        <div class="card-header">
            <h5>Statut de la Commande</h5>
        </div>
        <div class="card-body">
            <ul class="app-timeline-box">
                @foreach ($steps as $index => $step)
                    <li class="timeline-section {{ $index === $currentStep ? 'active' : '' }}">
                        <div class="timeline-icon">
                            <span class="text-light-{{ $step['color'] }} h-35 w-35 d-flex-center b-r-50">
                                <i class="ti {{ $step['icon'] }} f-s-20"></i>
                            </span>
                        </div>
                        <div class="timeline-content bg-light-{{ $step['color'] }} b-1-{{ $step['color'] }}">
                            <div class="d-flex justify-content-between align-items-center timeline-flex">
                                <h6 class="mt-2 text-{{ $step['color'] }}">{{ $step['title'] }}</h6>
                                @if ($step['date'])
                                    <span class="badge text-bg-{{ $step['color'] }} ms-2">
                                        {{ $step['date']->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                            <p class="mt-2 text-dark">{{ $step['description'] }}</p>
                            @if ($step['date'])
                                <p class="text-{{ $step['color'] }}">
                                    {{ $step['date']->format('D, d M Y - H:i') }}
                                </p>
                            @endif

                            {{-- Special content for processing step --}}
                            @if ($step['key'] === 'processing' && isset($step['delivery_person']) && $step['delivery_person'])
                                <div class="mt-2 p-2 bg-light rounded">
                                    <small class="text-muted">
                                        <i class="ti ti-user me-1"></i>
                                        Livreur: {{ $step['delivery_person']->user->name }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Customer Feedback Section --}}
    @if ($canShowFeedback)
        <div class="card mt-3">
            <div class="card-header">
                <h5>{{ $feedbackTitle }}</h5>
            </div>
            <div class="card-body">
                @if ($order->rating)
                    <div class="d-flex justify-content-between">
                        <h6 class="f-w-600 text-dark">
                            <i class="ti ti-star f-s-18 me-2 text-warning"></i>Note
                        </h6>
                        <div class="text-end">
                            <div class="d-flex align-items-center">
                                <span class="me-2">{{ $order->rating }}/5</span>
                                <div class="star-rating">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="ti ti-star{{ $i <= $order->rating ? '-filled' : '' }} text-warning f-s-12"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($order->comments)
                    <div class="mt-3">
                        <h6 class="f-w-600 text-dark">
                            <i class="ti ti-message f-s-18 me-2 text-info"></i>Commentaire client
                        </h6>
                        <p class="mt-2 bg-light p-2 rounded">{{ $order->comments }}</p>
                    </div>
                @endif

                @if ($order->center_comments)
                    <div class="mt-3">
                        <h6 class="f-w-600 text-dark">
                            <i class="ti ti-message-circle f-s-18 me-2 text-primary"></i>
                            {{ $centerCommentsLabel }}
                        </h6>
                        <p class="mt-2 bg-light p-2 rounded">{{ $order->center_comments }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
<!-- Order Status end -->
