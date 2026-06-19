@if($kechikkanlar->isEmpty())
    <p class="text-muted text-center py-4 mb-0">
        <i class="bi bi-check-circle text-success fs-4 d-block mb-2"></i>
        Muddati o'tgan to'lovlar yo'q
    </p>
@else
<div class="table-responsive">
    <table class="table table-hover mb-0 table-sm dash-table">
        <thead class="table-light">
            <tr>
                <th>Shartnoma</th>
                <th>Mijoz</th>
                <th class="text-end">Qoldiq</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kechikkanlar as $k)
            <tr>
                <td>
                    <a href="{{ route('kreditlar.show', $k) }}"
                       class="text-decoration-none fw-medium">
                        {{ $k->shartnoma_raqam }}
                    </a>
                    @if($k->filial)
                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1"
                          style="font-size:.62rem">{{ $k->filial->kod }}</span>
                    @endif
                </td>
                <td class="text-truncate" style="max-width:110px">
                    {{ $k->mijoz->familiya ?? '' }} {{ $k->mijoz->ism ?? '' }}
                </td>
                <td class="text-end text-danger fw-medium small">
                    {{ round($k->qoldiq_qarz / 1_000_000, 1) }} mln
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
