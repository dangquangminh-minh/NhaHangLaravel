@extends('layouts.app')
@section('title','Lưu Nhà cung cấp')
@section('content')
<form class="card card-body p-4 form-panel" method="post" action="{{ $record->exists ? route('admin.suppliers.update',$record) : route('admin.suppliers.store') }}">@csrf @if($record->exists)@method('PUT')@endif<h1 class="h3 mb-4">{{ $record->exists ? 'Cập nhật' : 'Thêm mới' }} Nhà cung cấp</h1><x-field name="name" label="Tên nhà cung cấp" type="text" :value="$record->name" required /><x-field name="address" label="Địa chỉ" type="text" :value="$record->address" required /><x-field name="phone" label="Số điện thoại" type="tel" :value="$record->phone" required /><div class="action-row"><button class="btn btn-primary">Lưu thông tin</button><a class="btn btn-outline-secondary" href="{{ route('admin.suppliers.index') }}">Quay lại</a></div></form>
@endsection
