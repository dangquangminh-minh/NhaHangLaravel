@extends('layouts.app')
@section('title','Lưu tài khoản')
@section('content')
<form method="post" action="{{ $record->exists ? route('admin.accounts.update',[$group,$record]) : route('admin.accounts.store',$group) }}" class="card card-body p-4 form-panel">@csrf @if($record->exists)@method('PUT')@endif<h1 class="h3 mb-4">{{ $record->exists ? 'Cập nhật' : 'Thêm' }} {{ $group==='staff'?'nhân viên':'khách hàng' }}</h1>
<x-field name="name" label="Họ tên" :value="$record->name" maxlength="100" required /><x-field name="email" label="Email" type="email" :value="$record->email" maxlength="100" required /><x-field name="phone" label="Số điện thoại" type="tel" :value="$record->phone" />
<div class="row"><div class="col-md-6"><x-field name="gender" label="Giới tính" type="select" :value="$record->gender" :options="[''=>'Chưa cung cấp','Nam'=>'Nam','Nữ'=>'Nữ','Khác'=>'Khác']" /></div><div class="col-md-6"><x-field name="birth_year" label="Năm sinh" type="number" :value="$record->birth_year" min="1900" :max="date('Y')" /></div></div>
@if($group==='staff')<x-field name="role_id" label="Vai trò" type="select" :value="$record->role_id" :options="$roles->pluck('name','id')->all()" required />@endif
<x-field name="password" :label="$record->exists ? 'Mật khẩu mới (để trống nếu giữ nguyên)' : 'Mật khẩu (ít nhất 8 ký tự)'" type="password" autocomplete="new-password" :required="!$record->exists" /><x-field name="password_confirmation" label="Xác nhận mật khẩu" type="password" autocomplete="new-password" />
<div class="action-row"><button class="btn btn-primary">Lưu tài khoản</button><a class="btn btn-outline-secondary" href="{{ route('admin.accounts.index',$group) }}">Quay lại</a></div></form>
@endsection
