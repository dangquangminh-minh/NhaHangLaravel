@extends('layouts.app')
@section('title','Đăng ký')
@section('content')
<form method="post" action="{{ route('register.store') }}" class="card card-body p-4 mx-auto app-auth-panel app-auth-panel-wide">@csrf<h1 class="h3 mb-4">Tạo tài khoản khách hàng</h1><x-field name="name" label="Họ và tên" maxlength="100" required /><x-gmail-email /><x-field name="phone" label="Số điện thoại" type="tel" maxlength="16" required /><x-field name="password" label="Mật khẩu (ít nhất 8 ký tự)" type="password" autocomplete="new-password" required /><x-field name="password_confirmation" label="Xác nhận mật khẩu" type="password" autocomplete="new-password" required /><button class="btn btn-primary">Đăng ký</button><a class="mt-3" href="{{ route('login') }}">Đã có tài khoản? Đăng nhập</a></form>
@endsection
