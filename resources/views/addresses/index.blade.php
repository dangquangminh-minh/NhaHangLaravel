@extends('layouts.app')
@section('title','Địa chỉ giao hàng')
@section('content')
<div class="d-flex flex-wrap justify-content-between gap-2 mb-4"><h1 class="h2">Địa chỉ giao hàng</h1><a href="{{ route('addresses.create') }}" class="btn btn-primary">+ Thêm địa chỉ</a></div><div class="row g-4 mb-4">@forelse($records as $record)<div class="col-md-6"><div class="card card-body h-100"><h2 class="h5">{{ $record->recipient_name }} @if($record->is_default)<span class="badge badge-status">Mặc định</span>@endif</h2><p>{{ $record->phone }}</p><p class="preserve-lines">{{ $record->address }}</p><div class="action-row"><a class="btn btn-sm btn-outline-primary" href="{{ route('addresses.show',$record) }}">Chi tiết</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('addresses.edit',$record) }}">Sửa</a><x-delete :action="route('addresses.destroy',$record)" /></div></div></div>@empty<p class="empty">Bạn chưa có địa chỉ giao hàng.</p>@endforelse</div>{{ $records->links() }}
@endsection
