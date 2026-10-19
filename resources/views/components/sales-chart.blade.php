@props(['series','metric','title','money'=>false])
@php
$maximum=max(1,max(array_column($series,$metric)?:[0]));
$width=720;
$labelEvery=max(1,(int)ceil(count($series)/8));
$step=($width-90)/max(1,count($series));
@endphp
<section class="card card-body h-100"><h2 class="h5">{{ $title }}</h2><p class="text-muted small">Chỉ tính đơn đã hoàn tất và thanh toán. Trỏ vào từng cột để xem số liệu; có thể cuộn ngang trên màn hình nhỏ.</p><div class="sales-chart-scroll"><svg class="sales-chart" width="{{ $width }}" height="300" viewBox="0 0 {{ $width }} 300" role="img" aria-label="{{ $title }}">
<line x1="65" y1="235" x2="{{ $width-12 }}" y2="235" stroke="var(--ui-border-input)"/><line x1="65" y1="35" x2="65" y2="235" stroke="var(--ui-border-input)"/>
<text x="60" y="25" class="chart-label">{{ $money?\App\Support\Money::format(\App\Support\Money::decimal($maximum)).'đ':$maximum.' đơn' }}</text><text x="42" y="239" class="chart-label">0</text>
@foreach($series as $i=>$row)
@php($height=($row[$metric]/$maximum)*185)
<rect x="{{ 65+$i*$step+$step*.15 }}" y="{{ 235-$height }}" width="{{ $step*.7 }}" height="{{ $height }}" rx="3" fill="{{ $money?'var(--ui-accent)':'var(--ui-primary)' }}"><title>{{ $row['date'] }}: {{ $money?\App\Support\Money::format(\App\Support\Money::decimal($row[$metric])).'đ':$row[$metric].' đơn' }}</title></rect>
@if($i % $labelEvery === 0 || $loop->last)<text x="{{ 65+$i*$step+$step*.5 }}" y="252" text-anchor="end" transform="rotate(-45 {{ 65+$i*$step+$step*.5 }} 252)" class="chart-label">{{ $row['date'] }}</text>@endif
@endforeach
</svg></div></section>
