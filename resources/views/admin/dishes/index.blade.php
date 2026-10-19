@extends('layouts.app')
@section('title','Quản lý món ăn')
@section('content')
<div class="d-flex flex-wrap justify-content-between gap-2 mb-4"><h1 class="h2">Quản lý món ăn</h1><a class="btn btn-primary" href="{{ route('admin.dishes.create') }}">+ Thêm món ăn</a></div>
@include('shared.search',['liveResults'=>'dish-results'])
<div id="dish-results" data-live-results aria-busy="false">
<form id="bulk-dishes" method="post" action="{{ route('admin.dishes.bulk-destroy') }}" data-confirm="Xóa các món đã chọn? Nếu có món đang được tham chiếu, toàn bộ lần xóa sẽ bị hủy.">@csrf<button class="btn btn-sm btn-outline-danger mb-3">Xóa món đã chọn</button></form>
<div class="card table-responsive mb-4"><table class="table mb-0"><thead><tr><th scope="col"><input type="checkbox" data-check-all="bulk-dishes" aria-label="Chọn tất cả món trên trang"></th><th scope="col">Món ăn</th><th scope="col">Loại món / Nhà cung cấp</th><th scope="col" class="ui-numeric">Đơn giá</th><th scope="col">Phục vụ</th><th scope="col">Thao tác</th></tr></thead><tbody class="ui-table-body">
@forelse($records as $record)<tr><td><input form="bulk-dishes" type="checkbox" name="ids[]" value="{{ $record->id }}" aria-label="Chọn {{ $record->name }}"></td><td><div class="action-row"><img loading="lazy" decoding="async" class="mini-image" src="{{ $record->imageUrl() }}" alt=""><span>{{ $record->name }}<small class="d-block text-muted">#{{ $record->id }}</small></span></div></td><td>{{ $record->category->name }}<small class="d-block text-muted">{{ $record->supplier->name }}</small></td><td class="ui-numeric price">{{ \App\Support\Money::format($record->price) }}đ</td><td><span class="badge badge-status">{{ $record->is_available ? 'Đang phục vụ' : 'Tạm ngừng' }}</span></td><td><div class="action-row"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.dishes.show',$record) }}">Chi tiết</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.dishes.edit',$record) }}">Sửa</a><x-delete :item="$record->name" :action="route('admin.dishes.destroy',$record)" /></div></td></tr>@empty<tr><td colspan="6" class="empty"><x-empty-state message="Không có món phù hợp." /></td></tr>@endforelse
</tbody></table></div>{{ $records->links() }}
</div>
@endsection
