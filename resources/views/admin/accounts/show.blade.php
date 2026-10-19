@extends('layouts.app')
@section('title','Thông tin tài khoản')
@section('content')
<div class="card card-body p-4 form-panel"><h1 class="h3 mb-4">{{ $record->name }}</h1><dl class="row">@foreach(['id'=>'Mã tài khoản','email'=>'Email','phone'=>'Số điện thoại','gender'=>'Giới tính','birth_year'=>'Năm sinh'] as $field=>$label)<dt class="col-sm-4">{{ $label }}</dt><dd class="col-sm-8">{{ $record->$field ?? 'Chưa cung cấp' }}</dd>@endforeach<dt class="col-sm-4">Vai trò</dt><dd class="col-sm-8">{{ $record->role->name }}</dd></dl><div class="action-row">@if($group==='staff')<a class="btn btn-primary" href="{{ route('admin.accounts.edit',[$group,$record]) }}">Chỉnh sửa</a>@endif<a class="btn btn-outline-secondary" href="{{ route('admin.accounts.index',$group) }}">Quay lại</a><x-delete :action="route('admin.accounts.destroy',[$group,$record])" /></div></div>
@endsection
