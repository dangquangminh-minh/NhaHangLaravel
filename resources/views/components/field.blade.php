@props(['name','label','type'=>'text','value'=>'','options'=>[],'required'=>false])
@php
$invalid=$errors->has($name)||($type==='file'&&$errors->has($name.'.*'));
$errorId=$name.'-error';
$describedBy=trim($attributes->get('aria-describedby','').' '.($invalid?$errorId:''));
$controlAttributes=$attributes->except(['aria-describedby','aria-invalid'])->class([$type==='select'?'form-select':'form-control','is-invalid'=>$invalid]);
@endphp
<div class="mb-3"><label for="{{ $name }}" class="form-label">{{ $label }} @if($required)<span class="text-danger" aria-hidden="true">*</span>@endif</label>
@if($type === 'textarea')<textarea id="{{ $name }}" name="{{ $name }}" rows="4" {{ $controlAttributes }} @if($invalid) aria-invalid="true" @endif @if($describedBy) aria-describedby="{{ $describedBy }}" @endif @required($required)>{{ old($name,$value) }}</textarea>
@elseif($type === 'select')<select id="{{ $name }}" name="{{ $name }}" {{ $controlAttributes }} @if($invalid) aria-invalid="true" @endif @if($describedBy) aria-describedby="{{ $describedBy }}" @endif @required($required)>@foreach($options as $key=>$text)<option value="{{ $key }}" @selected((string)old($name,$value) === (string)$key)>{{ $text }}</option>@endforeach</select>
@else<input id="{{ $name }}" name="{{ $name }}{{ $attributes->has('multiple') ? '[]' : '' }}" type="{{ $type }}" @if(!in_array($type,['password','file'])) value="{{ old($name,$value) }}" @endif {{ $controlAttributes }} @if($invalid) aria-invalid="true" @endif @if($describedBy) aria-describedby="{{ $describedBy }}" @endif @required($required)>
@endif
@if($invalid)<div id="{{ $errorId }}" class="invalid-feedback d-block">@error($name)<div>{{ $message }}</div>@enderror
@if($type==='file')@foreach($errors->get($name.'.*') as $messages)@foreach($messages as $message)<div>{{ $message }}</div>@endforeach @endforeach @endif</div>@endif
</div>