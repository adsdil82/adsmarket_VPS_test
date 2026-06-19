<div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width:120px">Sana</th>
                <th>Shablon</th>
                <th>Manzil</th>
                <th style="width:90px">Holat</th>
                <th style="width:60px">Amal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pochta_loglar as $log)
            <tr>
                <td class="small text-muted">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                <td class="small">{{ $log->shablon?->nomi ?? '—' }}</td>
                <td class="small text-muted" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"
                    title="{{ $log->address }}">{{ $log->address }}</td>
                <td>{!! $log->holatBadge() !!}</td>
                <td>
                    <a href="{{ route('admin.gibrid-pochta.pochta-loglar.index', ['kredit_id' => $kredit->id]) }}"
                       class="btn btn-xs btn-outline-secondary" title="Jurnalda ko'rish">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-3 small">
                    <i class="bi bi-envelope me-1"></i>
                    Bu kredit uchun hali pochta xat yuborilmagan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($pochta_loglar->count() > 0)
<div class="p-2 text-end">
    <a href="{{ route('admin.gibrid-pochta.pochta-loglar.index', ['kredit_id' => $kredit->id]) }}"
       class="btn btn-xs btn-outline-primary small">
        <i class="bi bi-list-ul me-1"></i>Barcha loglar
    </a>
</div>
@endif
