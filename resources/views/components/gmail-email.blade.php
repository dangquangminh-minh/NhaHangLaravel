@props(['value'=>'','required'=>true])
@php($local=old('email_local',\Illuminate\Support\Str::before(old('email',$value??''),'@')))
@php($invalid=$errors->has('email')||$errors->has('email_local'))
<div class="mb-3"><label for="email_local" class="form-label">Email @if($required)<span class="text-danger" aria-hidden="true">*</span>@endif</label>
<div class="input-group"><input type="text" @class(['form-control','is-invalid'=>$invalid]) id="email_local" name="email_local" value="{{ $local }}" maxlength="64" autocomplete="username" aria-describedby="gmail-suffix email-hint{{ $invalid?' email-error':'' }}" @if($invalid) aria-invalid="true" @endif @required($required)><span class="input-group-text" id="gmail-suffix">@gmail.com</span></div>
<small class="text-muted" id="email-hint">Chỉ nhập phần trước @gmail.com.</small>
@if($invalid)<div id="email-error" class="invalid-feedback d-block">@error('email_local')<div>{{ $message }}</div>@enderror @error('email')<div>{{ $message }}</div>@enderror</div>@endif
</div>