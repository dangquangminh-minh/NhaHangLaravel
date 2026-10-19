@can('update',$order)
@if(!$order->isTerminal())
@if($order->payment_method==='transfer' && !$order->is_paid)
@if($order->payment_reported_at)<form method="post" action="{{ route('admin.orders.payment.verify',$order) }}" data-confirm="Bạn đã kiểm tra tài khoản MB và nhận đủ {{ \App\Support\Money::format($order->total) }}đ cho đơn #{{ $order->id }}?">@csrf<button class="btn btn-sm btn-success mb-2">Xác minh đã nhận tiền</button></form>@else<p class="small text-muted mb-2">Chờ khách báo chuyển khoản.</p>@endif
<p class="small text-muted">Chỉ chuyển Đang làm sau khi xác minh tiền và gọi xác nhận khách.</p>
@endif
<div class="action-row">@foreach($order->nextStatuses() as $next)<form method="post" action="{{ route('admin.orders.update',$order) }}" data-confirm="{{ $next===5 ? 'Hủy đơn này? Không thể khôi phục trạng thái. Khoản đã nhận cần hoàn thủ công.' : 'Chuyển đơn sang '.$statuses->firstWhere('id',$next)->name.'? Không thể quay về bước trước.' }}">@csrf @method('PATCH')<input type="hidden" name="order_status_id" value="{{ $next }}"><button class="btn btn-sm {{ $next===5?'btn-outline-danger':'btn-primary' }}">{{ $statuses->firstWhere('id',$next)->name }}</button></form>@endforeach</div>
@else<span class="text-muted small">Đơn đã kết thúc, không thể đổi trạng thái.</span>@endif
@endcan
