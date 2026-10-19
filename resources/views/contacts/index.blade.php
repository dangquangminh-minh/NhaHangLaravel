@extends('layouts.app')
@section('title','Tin liên hệ')
@section('content')
<h1 class="h2 mb-4">Tin liên hệ từ khách hàng</h1>@include('shared.search',['placeholder'=>'Tên người gửi hoặc chủ đề'])
<div class="row g-3 mb-4">@forelse($records as $record)<div class="col-lg-6"><article class="card card-body h-100"><h2 class="h5">{{ $record->subject }}</h2><p><strong>{{ $record->name }}</strong> · {{ $record->email }} · {{ $record->phone }}</p><small>{{ $record->created_at->format('d/m/Y H:i') }} · {{ $record->is_read ? 'Đã đọc' : 'Chưa đọc' }}</small><p class="preserve-lines mt-3">{{ $record->message }}</p>@if(!$record->is_read)<form method="post" action="{{ route('admin.contacts.update',$record) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-primary">Đánh dấu đã đọc</button></form>@endif</article></div>@empty<p class="empty">Không có tin liên hệ phù hợp.</p>@endforelse</div>{{ $records->links() }}
@endsection
