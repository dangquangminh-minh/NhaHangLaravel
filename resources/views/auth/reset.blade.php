@extends('layouts.app')
@section('title','Đặt lại mật khẩu')
@section('content')
<form method="post" action="{{ route('password.update') }}" class="card card-body p-4 mx-auto app-auth-panel">@csrf<input type="hidden" name="token" value="{{ $token }}"><h1 class="h3 mb-4">Đặt lại mật khẩu</h1><x-field name="email" label="Email" type="email" :value="$email" required /><x-field name="password" label="Mật khẩu mới (ít nhất 8 ký tự)" type="password" required /><x-field name="password_confirmation" label="Xác nhận mật khẩu mới" type="password" required /><button class="btn btn-primary">Lưu mật khẩu mới</button></form>
@endsection
