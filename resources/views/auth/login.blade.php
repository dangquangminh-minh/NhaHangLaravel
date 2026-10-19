@extends('layouts.app')
@section('title','Đăng nhập')
@section('content')
<div class="card card-body p-4 p-lg-5 mx-auto app-auth-panel"><div class="eyebrow">Rất vui được gặp lại</div><h1 class="h3 my-3">Đăng nhập</h1><form method="post" action="{{ route('login.store') }}">@csrf<x-field name="email" label="Email hoặc tên Gmail" type="text" maxlength="100" autocomplete="username" required /><x-field name="password" label="Mật khẩu" type="password" autocomplete="current-password" required /><button class="btn btn-primary w-100">Đăng nhập</button></form><div class="d-flex justify-content-between mt-4"><a href="{{ route('password.request') }}">Quên mật khẩu?</a><a href="{{ route('register') }}">Tạo tài khoản</a></div></div>
@endsection
