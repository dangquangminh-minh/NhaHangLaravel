@extends('layouts.app')
@section('title','Chi tiết Nhà cung cấp')
@section('content')
<div class="card card-body p-4 form-panel"><h1 class="h3 mb-4">Chi tiết Nhà cung cấp #{{ $record->id }}</h1><dl class="row"><dt class="col-sm-4">Tên nhà cung cấp</dt><dd class="col-sm-8 preserve-lines">{{ $record->name }}</dd><dt class="col-sm-4">Địa chỉ</dt><dd class="col-sm-8 preserve-lines">{{ $record->address }}</dd><dt class="col-sm-4">Số điện thoại</dt><dd class="col-sm-8 preserve-lines">{{ $record->phone }}</dd></dl><div class="action-row"><a class="btn btn-primary" href="{{ route('admin.suppliers.edit',$record) }}">Chỉnh sửa</a><a class="btn btn-outline-secondary" href="{{ route('admin.suppliers.index') }}">Quay lại</a><x-delete :action="route('admin.suppliers.destroy',$record)" /></div></div>
@endsection
