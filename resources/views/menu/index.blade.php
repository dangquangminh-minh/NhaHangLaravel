@extends('layouts.app')
@section('title','Thực đơn')
@section('content')
<div class="eyebrow">Tươi ngon mỗi ngày</div><h1 class="mb-4">Thực đơn nhà hàng</h1>
<section class="mb-5" id="mon-ban-chay"><div class="eyebrow">Được khách hàng yêu thích</div><h2 class="h3 mt-2">10 món ăn bán chạy nhất quán</h2><p class="text-muted">Xếp theo tổng số phần trong đơn đã hoàn tất và thanh toán.</p><div class="row g-4">@forelse($top as $dish)@include('shared.dish-card')@empty<p class="text-muted">Chưa có dữ liệu món đã bán.</p>@endforelse</div></section>
<h2 class="h3 mb-3">Tất cả món ăn</h2>
@include('shared.search',['placeholder'=>'Tên món hoặc mô tả','liveResults'=>'menu-results'])
@include('menu.results')
@endsection
