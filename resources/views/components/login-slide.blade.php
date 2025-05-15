@props(['background', 'image', 'text'])

<div class="slide" style="background-color: {{ $background }}; width: 100%;">
    <div class="slide-content"
        style="width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center;">
        <img src="{{ asset($image) }}" class="slide-image" alt="slide">
        <div class="slide-text">
            {!! $text !!}
        </div>
    </div>
</div>
