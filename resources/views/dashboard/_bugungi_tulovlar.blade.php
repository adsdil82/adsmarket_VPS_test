@if($bugungiTulovlar->isEmpty())
    <p class="text-muted text-center py-4 mb-0">
        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
        Bugun to'lov qabul qilinmagan
    </p>
@else
<div class="table-responsive">
    <table class="table table-hover mb-0 table-sm dash-table">
        <thead class="table-light">
            <tr>
                <th>Shartnoma / Mijoz</th>
                <th class="text-end">Summa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bugungiTulovlar as $t)
            <tr>
                <td>
                    <a href="{{ route('kreditlar.show', $t->kredit) }}"
                       class="text-decoration-none fw-medium small">
                        {{ $t->kredit->shartnoma_raqam ?? '—' }}
                    </a>
                    <div class="text-muted" style="font-size:.72rem">
                        {{ $t->kredit->mijoz->familiya ?? '' }} {{ $t->kredit->mijoz->ism ?? '' }}
                    </div>
                </td>
                <td class="text-end fw-medium text-success small">
                    {{ number_format($t->summa, 0, '.', ' ') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
