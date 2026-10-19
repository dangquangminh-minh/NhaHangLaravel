@extends('layouts.app')
@section('title','Lỗi 500')
@section('content')
<div class="card card-body text-center p-5"><div class="display-2">500</div><h1 class="h4 my-4">Có lỗi xử lý. Vui lòng thử lại hoặc liên hệ nhà hàng.</h1><div><a class="btn btn-primary" href="{{ route('home') }}">Về trang chủ</a></div></div>
@endsection
