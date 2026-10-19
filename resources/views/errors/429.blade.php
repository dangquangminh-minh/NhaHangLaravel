@extends('layouts.app')
@section('title','Lỗi 429')
@section('content')
<div class="card card-body text-center p-5"><div class="display-2">429</div><h1 class="h4 my-4">Bạn thao tác quá nhiều lần. Vui lòng thử lại sau ít phút.</h1><div><a class="btn btn-primary" href="{{ route('home') }}">Về trang chủ</a></div></div>
@endsection
