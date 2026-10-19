@extends('layouts.app')
@section('title','Liên hệ nhà hàng')
@section('content')
<form method="post" action="{{ route('contacts.store') }}" class="card card-body p-4 form-panel">@csrf<div class="eyebrow">Chúng tôi luôn lắng nghe</div><h1 class="h3 my-3">Liên hệ nhà hàng</h1><x-field name="name" label="Họ và tên" :value="auth()->user()?->name" maxlength="100" required /><x-field name="email" label="Email" type="email" :value="auth()->user()?->email" required /><x-field name="phone" label="Số điện thoại" type="tel" :value="auth()->user()?->phone" /><x-field name="subject" label="Chủ đề" maxlength="200" required /><x-field name="message" label="Nội dung" type="textarea" maxlength="5000" required /><button class="btn btn-primary">Gửi lời nhắn</button></form>
@endsection
