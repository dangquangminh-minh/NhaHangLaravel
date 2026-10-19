@props(['action','item'=>'','label'=>'Xóa','message'=>'Bạn có chắc muốn xóa bản ghi này?'])
<form method="post" action="{{ $action }}" data-confirm="{{ $message }}" data-confirm-name="{{ $item }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><svg class="ui-icon" aria-hidden="true"><use href="#icon-trash"/></svg>{{ $label }}</button></form>
