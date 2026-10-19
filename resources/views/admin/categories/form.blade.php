@extends('layouts.app')
@section('title','Lưu Loại món')
@section('content')
<form class="card card-body p-4 form-panel" method="post" action="{{ $record->exists ? route('admin.categories.update',$record) : route('admin.categories.store') }}">@csrf @if($record->exists)@method('PUT')@endif<h1 class="h3 mb-4">{{ $record->exists ? 'Cập nhật' : 'Thêm mới' }} Loại món</h1><x-field name="name" label="Tên loại món" type="text" :value="$record->name" required /><x-field name="description" label="Mô tả" type="textarea" :value="$record->description"  /><div class="action-row"><button class="btn btn-primary">Lưu thông tin</button><a class="btn btn-outline-secondary" href="{{ route('admin.categories.index') }}">Quay lại</a></div></form>
@endsection
