@extends('layouts.app')
@section('title','Lưu món ăn')
@section('content')
<div class="form-panel"><form class="card card-body p-4 mb-4" method="post" enctype="multipart/form-data" action="{{ $record->exists ? route('admin.dishes.update',$record) : route('admin.dishes.store') }}">@csrf @if($record->exists)@method('PUT')@endif
<h1 class="h3 mb-4">{{ $record->exists ? 'Chỉnh sửa món ăn' : 'Thêm món ăn' }}</h1><x-field name="name" label="Tên món ăn" :value="$record->name" maxlength="200" required />
<div class="row"><div class="col-md-6"><x-field name="category_id" label="Loại món" type="select" :value="$record->category_id" :options="[''=>'Chọn loại món']+$categories->pluck('name','id')->all()" required /></div><div class="col-md-6"><x-field name="supplier_id" label="Nhà cung cấp" type="select" :value="$record->supplier_id" :options="[''=>'Chọn nhà cung cấp']+$suppliers->pluck('name','id')->all()" required /></div></div>
<div class="row"><div class="col-md-6"><x-field name="price" label="Đơn giá (đ)" type="number" :value="\App\Support\Money::plain($record->price)" min="1000" max="100000000" step="0.01" required /></div><div class="col-md-6"><x-field name="is_available" label="Trạng thái phục vụ" type="select" :value="(int)$record->is_available" :options="[1=>'Đang phục vụ',0=>'Tạm ngừng']" required /></div></div>
<x-field name="description" label="Mô tả món ăn" type="textarea" :value="$record->description" maxlength="5000" />
@if($record->image)<img loading="lazy" decoding="async" src="{{ $record->imageUrl() }}" class="album-image mb-3" alt="Ảnh hiện tại">@endif
<x-field name="image" label="Ảnh đại diện (JPG, PNG, WebP; tối đa 2MB)" type="file" accept="image/jpeg,image/png,image/webp" />
<x-field name="images" label="Thêm ảnh album (tối đa 5 ảnh/lần; 2MB/ảnh)" type="file" multiple accept="image/jpeg,image/png,image/webp" />
<div class="action-row"><button class="btn btn-primary">Lưu món ăn</button><a class="btn btn-outline-secondary" href="{{ route('admin.dishes.index') }}">Quay lại</a></div></form>
@if($record->exists && $record->images->isNotEmpty())<section class="card card-body"><h2 class="h5">Album ảnh hiện tại</h2><div class="action-row">@foreach($record->images as $image)<div class="text-center"><img loading="lazy" decoding="async" class="album-image d-block mb-2" src="{{ asset($image->path) }}" alt="Ảnh album"><x-delete :action="route('admin.dishes.images.destroy',[$record,$image])" label="Xóa ảnh" /></div>@endforeach</div></section>@endif</div>
@endsection
