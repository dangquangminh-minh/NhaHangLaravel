@extends('layouts.app')
@section('title','Lỗi 403')
@section('content')
<div class="card card-body text-center p-5"><div class="display-2">403</div><h1 class="h4 my-4">Bạn không có quyền truy cập trang này.</h1><div><a class="btn btn-primary" href="{{ route('home') }}">Về trang chủ</a></div></div>
@endsection
