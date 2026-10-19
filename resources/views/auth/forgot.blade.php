@extends('layouts.app')
@section('title','Quên mật khẩu')
@section('content')
<form method="post" action="{{ route('password.email') }}" class="card card-body p-4 mx-auto app-auth-panel">@csrf<h1 class="h3">Quên mật khẩu</h1><p class="text-muted">Nhập email đã đăng ký để nhận liên kết đặt lại mật khẩu.</p><x-field name="email" label="Email" type="email" required /><button class="btn btn-primary">Gửi liên kết đặt lại</button></form>
@endsection
