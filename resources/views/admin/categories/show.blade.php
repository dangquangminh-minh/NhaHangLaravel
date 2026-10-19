@extends('layouts.app')
@section('title','Chi tiết Loại món')
@section('content')
<div class="card card-body p-4 form-panel"><h1 class="h3 mb-4">Chi tiết Loại món #{{ $record->id }}</h1><dl class="row"><dt class="col-sm-4">Tên loại món</dt><dd class="col-sm-8 preserve-lines">{{ $record->name }}</dd><dt class="col-sm-4">Mô tả</dt><dd class="col-sm-8 preserve-lines">{{ $record->description }}</dd></dl><div class="action-row"><a class="btn btn-primary" href="{{ route('admin.categories.edit',$record) }}">Chỉnh sửa</a><a class="btn btn-outline-secondary" href="{{ route('admin.categories.index') }}">Quay lại</a><x-delete :action="route('admin.categories.destroy',$record)" /></div></div>
@endsection
