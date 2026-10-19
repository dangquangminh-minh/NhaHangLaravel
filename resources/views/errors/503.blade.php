@extends('layouts.app')
@section('title','Lỗi 503')
@section('content')
<div class="card card-body text-center p-5"><div class="display-2">503</div><h1 class="h4 my-4">Nhà hàng đang bảo trì hệ thống. Vui lòng quay lại sau.</h1><div><a class="btn btn-primary" href="{{ route('home') }}">Về trang chủ</a></div></div>
@endsection
