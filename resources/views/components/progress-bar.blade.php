@props(['raised' => 0, 'goal' => 0, 'percent' => null, 'label' => true, 'class' => ''])
@php $pct = $percent ?? ($goal > 0 ? min(100, round(($raised / $goal) * 100)) : 0); @endphp
@if($goal > 0 || $raised > 0)
<div class="progress-c {{ $class }}">
    <div class="progress-c__track">
        <div class="progress-c__fill" style="width:{{ $pct }}%"></div>
    </div>
    @if($label)
    <div class="progress-c__stats">
        <span>${{ number_format($raised) }} / ${{ number_format($goal) }}</span>
        <span>{{ $pct }}%</span>
    </div>
    @endif
</div>
@endif
