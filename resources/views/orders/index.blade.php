@extends('layouts.app')
@section('title','Đơn hàng')
@section('content')
<h1 class="h2 mb-4">{{ auth()->user()->isAdmin() ? 'Quản lý đơn hàng' : 'Lịch sử đơn hàng của bạn' }}</h1>@include('shared.search',['placeholder'=>'Mã đơn hoặc tên khách hàng'])
<div class="card table-responsive mb-4"><table class="table mb-0"><thead><tr><th scope="col">Mã đơn</th><th scope="col">Khách hàng</th><th scope="col">Ngày đặt</th><th scope="col" class="ui-numeric">Thành tiền</th><th scope="col">Trạng thái</th><th scope="col"></th></tr></thead><tbody class="ui-table-body">@forelse($records as $record)<tr><td>#{{ $record->id }}</td><td>{{ $record->user->name }}</td><td>{{ $record->created_at->format('d/m/Y H:i') }}</td><td class="ui-numeric price">{{ \App\Support\Money::format($record->total) }}đ</td><td><span class="badge badge-status">{{ $record->status->name }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('orders.show',$record) }}">Xem chi tiết</a></td></tr>@empty<tr><td colspan="6" class="empty"><x-empty-state message="Chưa có đơn hàng phù hợp." /></td></tr>@endforelse</tbody></table></div>{{ $records->links() }}
@endsection
