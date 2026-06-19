@if(empty($topQarzdorlar))
    <p class="text-muted text-center py-4 mb-0">
        <i class="bi bi-check-circle text-success fs-4 d-block mb-2"></i>
        Katta qarzdorlar yo'q
    </p>
@else
    @php $maxQ = collect($topQarzdorlar)->max('qoldiq_qarz') ?: 1; @endphp
    @foreach($topQarzdorlar as $r)
    @php $pct = round($r->qoldiq_qarz / $maxQ * 100); @endphp
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1 me-2" style="min-width:0">
                <a href="{{ route('kreditlar.show', $r->id) }}"
                   class="text-decoration-none fw-medium small d-block text-truncate">
                    {{ $r->shartnoma_raqam }}
                </a>
                <div class="text-muted text-truncate" style="font-size:.75rem">{{ $r->mijoz_ism }}</div>
            </div>
            <div class="text-end flex-shrink-0">
                <div class="fw-bold text-danger small">
                    {{ round($r->qoldiq_qarz / 1_000_000, 1) }} mln
                </div>
                @if($r->holat === 'muddati_otgan')
                <span class="badge bg-danger" style="font-size:.62rem">!</span>
                @endif
            </div>
        </div>
        <div class="qarz-bar mt-1">
            <div class="qarz-bar-fill" style="width:{{ $pct }}%"></div>
        </div>
    </div>
    @endforeach
@endif
