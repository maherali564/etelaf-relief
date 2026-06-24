@extends('layouts.app')
@section('content')
<section class="hero-detailed" @if($page->image) style="background-image:url('{{ asset('storage/'.$page->image) }}')" @endif>
    <div class="hero-detailed__overlay"></div>
    <div class="hero-detailed__inner">
        <h1 class="hero-detailed__title">{{ trans_field($page, 'title') }}</h1>
    </div>
</section>
<section class="section">
    <div class="container page-content">
        <div>{!! safeHtml(trans_field($page, 'content')) !!}</div>
    </div>
</section>
<style>
.page-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem 1rem;
    line-height: 1.8;
    color: #334155;
    font-size: 1rem;
}
[dir="rtl"] .page-content { line-height: 2; }
.page-content h2 { font-size: 1.5rem; font-weight: 700; color: #059669; margin: 2rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e2e8f0; }
.page-content h3 { font-size: 1.15rem; font-weight: 600; color: #1e293b; margin: 1.5rem 0 0.75rem; }
.page-content p { margin: 0 0 1rem; }
.page-content ul { margin: 0 0 1rem 1.5rem; padding: 0; }
[dir="rtl"] .page-content ul { margin: 0 1.5rem 1rem 0; }
.page-content ul li { margin-bottom: 0.5rem; list-style: disc; }
.page-content a { color: #059669; text-decoration: underline; }
.page-content a:hover { color: #047857; }
.page-content hr { border: none; border-top: 1px solid #e2e8f0; margin: 2rem 0; }
</style>
@endsection
