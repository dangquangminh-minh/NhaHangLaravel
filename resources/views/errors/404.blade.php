@extends('layouts.app')
@section('title','Lỗi 404')
@section('content')
<div class="card card-body text-center p-5"><div class="display-2">404</div><h1 class="h4 my-4">Trang hoặc dữ liệu bạn tìm không tồn tại.</h1><div><a class="btn btn-primary" href="{{ route('home') }}">Về trang chủ</a></div></div>
@endsection
