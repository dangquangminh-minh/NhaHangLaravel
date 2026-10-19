@extends('layouts.app')
@section('title','Trang chủ')
@section('content')
<section class="hero p-4 p-lg-5 mb-5"><div class="row align-items-center g-4"><div class="col-lg-7"><div class="eyebrow mb-3">Chào mừng đến với Nhà Hàng Ngon</div><h1>Bữa ngon sum vầy,<br>đậm đà hương Việt.</h1><p class="lead mt-4 mb-4">Chọn món yêu thích, đặt bàn cho buổi gặp mặt và nhận ưu đãi từ mỗi bữa ăn.</p><div class="action-row"><a href="{{ route('menu') }}" class="btn btn-primary btn-lg">Khám phá thực đơn</a><a href="{{ route('reservations.create') }}" class="btn btn-outline-primary btn-lg">Đặt bàn ngay</a></div></div><div class="col-lg-5"><img loading="lazy" decoding="async" class="img-fluid" src="{{ asset('assets/images/hero.png') }}" alt="Món ăn tại nhà hàng"></div></div></section>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4"><div><div class="eyebrow">Gợi ý cho bạn</div><h2 class="mt-2 mb-0">Món được yêu thích</h2><p class="text-muted mb-0">Ưu tiên số phần đã bán từ hóa đơn đã thanh toán.</p></div><a href="{{ route('menu') }}">Xem toàn bộ thực đơn →</a></div>
<div class="row g-4">@forelse($top as $dish)@include('shared.dish-card')@empty<p class="empty">Nhà hàng đang cập nhật thực đơn.</p>@endforelse</div>
@endsection
