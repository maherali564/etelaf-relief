@extends('layouts.app')
@section('content')
<section class="section">
    <div class="container prose">
        <h1>{{ trans_field($page, 'title') }}</h1>
        <div>{!! trans_field($page, 'content') !!}</div>
    </div>
</section>
@endsection
