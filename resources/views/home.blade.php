@extends('default')

@section('content')
@foreach($posts as $post)
  <article class="pure-g">
    <div class="pure-u-1-6">
      <a href="{{ $post['path'] }}"><time>{{ @$post['data']['date'] }}</time></a>
    </div>

    <div class="pure-u-5-6">
      @if(@$post['summary'] || @$post['contents'])
        <div class="e-content">
          {!! @$post['summary'] ?: $post['contents'] !!}
          @if(@$post['summary'])<a href="{{ $post['path'] }}">more</a>@endif
        </div>
      @endif
      @if(@$post['data']['photos'])
        @include('partials.gallery', [
            'photos' => $post['data']['photos'],
            'height' => 120,
        ])
      @endif
    </div>
  </article>
@endforeach
@endsection
