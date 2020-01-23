<section class="gallery">
@foreach($photos as $photo)
<div id="{{ $photo['id'] }}" class="lightbox">
    <div class="lightbox__box">
    <a class="lightbox__close" href="#" tabindex="0">X</a>
    <div class="lightbox__content">
        <img src="{{ $photo['link'] }}"/>
    </div>
    </div>
</div>
<a class="lightbox__thumbnail" href="#{{ $photo['id'] }}"><img src="{{ $photo['link'] }}" height="{{ $height }}"/></a>
@endforeach
