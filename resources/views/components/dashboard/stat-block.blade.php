@props(['bgColor', 'textColor', 'icon', 'title', 'subtitle', 'value', 'url' => null])

<div class="col-lg-3">
    @if($url)
        <a href="{!! $url !!}" target="_blank" class="text-decoration-none">
    @endif
    <div class="card ticket-card bg-{{ $bgColor }} {{ $url ? 'cursor-pointer' : '' }}">
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
    @if($url)
        </a>
    @endif
</div>

<style>
    .cursor-pointer {
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .cursor-pointer:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
</style>
