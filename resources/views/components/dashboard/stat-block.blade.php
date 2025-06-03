<div class="col-lg-3">
    <div class="card ticket-card bg-{{ $bgColor }}">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <p class="f-s-16 mb-0">{{ $title }}@if($subtitle)<br>{{ $subtitle }}@endif</p>
                <div class="h-40 w-40 d-flex-center">
                    <i class="ph-bold ph-{{ $icon }} f-s-45 text-{{ $textColor }}"></i>
                </div>
            </div>
            <h3 class="text-{{ $textColor }}-dark">{{ $value }}</h3>
        </div>
    </div>
</div>
