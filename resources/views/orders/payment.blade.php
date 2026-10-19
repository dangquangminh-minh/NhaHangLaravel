@extends('layouts.app')
@section('title','Thanh toán QR')
@section('content')
<div class="mx-auto payment-first">
<div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><h1 class="h2">Thanh toán bằng QR</h1><a href="{{ route('orders.index') }}">← Danh sách đơn</a></div>
<p>Quét mã và chuyển khoản đúng số tiền, nội dung bên dưới. Sau đó bấm <strong>Tôi đã chuyển khoản</strong> để xem thông tin đơn hàng.</p>
<p class="small text-muted">Trạng thái: <strong>Chờ xác nhận</strong>. Nhân viên sẽ kiểm tra tiền sau khi bạn báo chuyển khoản; thao tác này không tự xác nhận ngân hàng đã nhận tiền.</p>
@include('shared.payment-qr')
@can('cancel',$order)<form class="mt-3" method="post" action="{{ route('orders.cancel',$order) }}" data-confirm="Bạn muốn hủy đơn đang chờ xác nhận?">@csrf<button class="btn btn-outline-danger">Hủy đơn hàng</button></form>@endcan
</div>
@endsection
