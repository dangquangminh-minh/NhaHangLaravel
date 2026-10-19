@extends('layouts.app')
@section('title','Chi tiết địa chỉ')
@section('content')
<div class="card card-body p-4 form-panel"><h1 class="h3">{{ $record->recipient_name }}</h1><p>{{ $record->phone }}</p><p class="preserve-lines">{{ $record->address }}</p><p>{{ $record->is_default ? 'Địa chỉ mặc định' : 'Địa chỉ khác' }}</p><div class="action-row"><a class="btn btn-primary" href="{{ route('addresses.edit',$record) }}">Chỉnh sửa</a><a class="btn btn-outline-secondary" href="{{ route('addresses.index') }}">Quay lại</a><x-delete :action="route('addresses.destroy',$record)" /></div></div>
@endsection
