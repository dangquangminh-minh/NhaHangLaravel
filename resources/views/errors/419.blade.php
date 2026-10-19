@extends('layouts.app')
@section('title','Lỗi 419')
@section('content')
<div class="card card-body text-center p-5"><div class="display-2">419</div><h1 class="h4 my-4">Phiên làm việc đã hết hạn. Hãy tải lại trang và gửi lại biểu mẫu.</h1><div><a class="btn btn-primary" href="{{ route('home') }}">Về trang chủ</a></div></div>
@endsection
