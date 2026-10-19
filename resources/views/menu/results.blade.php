<div id="menu-results" data-live-results aria-busy="false">
<p class="text-muted" data-result-count>Tìm thấy {{ $dishes->total() }} món ăn.</p><div class="row g-4 mb-4">@forelse($dishes as $dish)@include('shared.dish-card')@empty<div class="col-12"><div class="card"><x-empty-state message="Không có món phù hợp. Hãy đổi từ khóa hoặc loại món." /></div></div>@endforelse</div>{{ $dishes->links() }}
</div>
