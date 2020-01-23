@extends('default')

@section('content')
<section>
  <article class="pure-g">
    <div class="pure-u-1-6">
      <a href="{{ $path }}"><time>{{ @$date }}</time></a>
    </div>

    <div class="pure-u-5-6">
      @if($contents)
        <div class="e-content">
          {!! $contents !!}
        </div>
      @endif
      @if(@$photos)
        @include('partials.gallery', [
            'photos' => $photos,
            'height' => 300,
        ])
      @endif
    </div>
  </article>
</section>
@endsection
