@extends('layouts.app')
@section('title','Quản lý đánh giá')
@section('content')
<h1 class="h2 mb-4">Đánh giá món ăn</h1><div class="row g-3 mb-4">@forelse($records as $record)<div class="col-lg-6"><article class="card card-body h-100"><h2 class="h5">{{ $record->dish->name }} · {{ $record->rating }}/5 sao</h2><p>{{ $record->user->name }} · {{ $record->created_at->format('d/m/Y H:i') }}</p><p class="preserve-lines">{{ $record->message }}</p>@if($record->image)<img loading="lazy" decoding="async" class="album-image mb-3" src="{{ asset($record->image) }}" alt="Ảnh đánh giá">@endif<div><x-delete :action="route('reviews.destroy',$record)" label="Xóa đánh giá" /></div></article></div>@empty<p class="empty">Chưa có đánh giá.</p>@endforelse</div>{{ $records->links() }}
@endsection
