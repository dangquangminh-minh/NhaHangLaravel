<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Nhà Hàng Ngon') · Nhà Hàng Ngon</title>
<link rel="stylesheet" href="{{ asset('assets/bootstrap.min.css') }}"><link rel="stylesheet" href="{{ asset('assets/ui-tokens.css') }}"><link rel="stylesheet" href="{{ asset('assets/ui-components.css') }}"><link rel="stylesheet" href="{{ asset('assets/app.css') }}">
</head>
<body class="ui-app">
@php($staff=auth()->user()?->isAdmin())
@include('shared.ui-icons')
<a href="#main-content" class="ui-skip">Đến nội dung chính</a>
<header class="ui-topbar"><nav class="navbar navbar-expand-xl ui-topbar-inner" aria-label="Điều hướng chính">
<a class="ui-brand" href="{{ $staff ? (auth()->user()->hasRole('owner') ? route('admin.dashboard') : route('admin.orders.index')) : route('home') }}"><span class="ui-brand-mark"><svg class="ui-icon" aria-hidden="true"><use href="#icon-grid"/></svg></span><span class="ui-brand-name">NHÀ HÀNG NGON<span class="ui-brand-note">{{ $staff ? 'Không gian quản trị' : 'Hương vị Việt' }}</span></span></a>
<div class="d-flex align-items-center gap-2 d-xl-none">@unless($staff)@include('shared.cart-link',['cartClass'=>'btn btn-sm btn-outline-primary'])@endunless
<button class="navbar-toggler ui-button ui-button--icon" type="button" data-bs-toggle="{{ $staff ? 'offcanvas' : 'collapse' }}" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Mở điều hướng"><svg class="ui-icon" aria-hidden="true"><use href="#icon-menu"/></svg></button></div>
@if($staff)
<div class="app-header-account d-none d-xl-flex action-row"><button type="button" class="ui-button ui-button--icon" data-ui-sidebar-toggle aria-controls="navigation" aria-expanded="true" aria-label="Ẩn thanh quản trị"><svg class="ui-icon" aria-hidden="true"><use href="#icon-menu"/></svg></button><a href="{{ route('profile.edit') }}" class="app-profile-link">{{ auth()->user()->name }} <small>{{ auth()->user()->role->name }}</small></a><form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-primary">Đăng xuất</button></form></div>
@else
<div class="collapse navbar-collapse" id="navigation"><ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link {{ request()->routeIs('menu','home','dishes.show') ? 'active' : '' }}" href="{{ route('menu') }}">Thực đơn</a></li><li class="nav-item d-none d-xl-block">@include('shared.cart-link',['cartClass'=>'nav-link'])</li><li class="nav-item"><a class="nav-link" href="{{ route('reservations.create') }}">Đặt bàn</a></li><li class="nav-item"><a class="nav-link" href="{{ route('contacts.create') }}">Liên hệ</a></li>
@auth
<li class="nav-item"><a class="nav-link" href="{{ route('orders.index') }}">Đơn hàng</a></li><li class="nav-item"><a class="nav-link" href="{{ route('reservations.index') }}">Lịch đặt bàn</a></li><li class="nav-item"><a class="nav-link" href="{{ route('vouchers.index') }}">Đổi voucher</a></li><li class="nav-item"><a class="nav-link" href="{{ route('addresses.index') }}">Địa chỉ</a></li>
@endauth
</ul><div class="action-row">@auth<a class="app-profile-link" href="{{ route('profile.edit') }}">{{ auth()->user()->name }} <small>({{ auth()->user()->role->name }})</small></a><form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-primary">Đăng xuất</button></form>@else<a class="btn btn-sm btn-outline-primary" href="{{ route('login') }}">Đăng nhập</a><a class="btn btn-sm btn-primary" href="{{ route('register') }}">Đăng ký</a>@endauth</div>
</div>
@endif
</nav></header>
<div @class(['ui-shell'=>$staff,'app-customer-shell'=>!$staff])>
@if($staff)<aside class="ui-sidebar offcanvas-xl offcanvas-start" tabindex="-1" id="navigation" aria-labelledby="management-title"><div class="offcanvas-header"><h2 class="offcanvas-title" id="management-title">Quản trị</h2><button type="button" class="ui-close" data-bs-dismiss="offcanvas" data-bs-target="#navigation" aria-label="Đóng điều hướng">×</button></div><div class="offcanvas-body"><p class="ui-sidebar-label d-none d-xl-block">Quản trị</p>@include('shared.management-nav')<div class="app-drawer-account d-xl-none"><a href="{{ route('profile.edit') }}" class="app-profile-link">{{ auth()->user()->name }}<small>{{ auth()->user()->role->name }}</small></a><form method="post" action="{{ route('logout') }}" class="mt-3">@csrf<button class="btn btn-outline-primary w-100">Đăng xuất</button></form></div><p class="ui-sidebar-note">Quản lý công việc nhà hàng trong một không gian.</p></div></aside>@endif
<main class="ui-main" id="main-content" tabindex="-1">
<nav aria-label="Đường dẫn"><ol class="ui-breadcrumb"><li><a href="{{ $staff ? route('admin.orders.index') : route('home') }}">{{ $staff ? 'Quản trị' : 'Trang chủ' }}</a></li><li aria-hidden="true">/</li><li aria-current="page">@yield('title','Nhà Hàng Ngon')</li></ol></nav>
@if(session('success'))<div class="alert alert-success alert-dismissible fade show app-success-alert" role="status" data-ui-success><span>{{ session('success') }}</span><button type="button" class="ui-close" data-bs-dismiss="alert" aria-label="Đóng thông báo">×</button></div>@endif
@if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert"><strong>Vui lòng kiểm tra lại thông tin.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main></div><footer class="py-4"><div class="container-fluid content-width d-flex flex-wrap justify-content-between gap-2"><span>Nhà Hàng Ngon · Hương vị Việt, trọn từng bữa ăn.</span><span>Đồ án quản lý nhà hàng · Laravel</span></div></footer>
@include('shared.confirm-modal')
<script src="{{ asset('assets/bootstrap.bundle.min.js') }}" defer></script><script src="{{ asset('assets/qrcode.min.js') }}" defer></script><script src="{{ asset('assets/app-ui.js') }}" defer></script><script src="{{ asset('assets/app.js') }}" defer></script>
</body></html>