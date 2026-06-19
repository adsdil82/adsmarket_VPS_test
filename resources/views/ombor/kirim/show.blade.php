@extends('layouts.app')
@section('title','Kirim #'.$kirim->id)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('kirim.index') }}">Kirim</a></li>
<li class="breadcrumb-item active">#{{ $kirim->id }}</li>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6 class="mb-0">Hujjat ma'lumotlari</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">ID</td><td>#{{ $kirim->id }}</td></tr>
                    <tr><td class="text-muted">Sana</td><td>{{ $kirim->sana->format('d.m.Y') }}</td></tr>
                    <tr><td class="text-muted">Yetkazuvchi</td><td>{{ $kirim->yetkazuvchi ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Hujjat #</td><td>{{ $kirim->hujjat_raqam ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Xodim</td><td>{{ $kirim->xodim?->ism_familiya }}</td></tr>
                    <tr><td class="text-muted">Filial</td><td>{{ $kirim->filial?->nomi }}</td></tr>
                    <tr><td class="text-muted">Holat</td>
                        <td><span class="badge bg-{{ $kirim->holat==='tasdiqlangan'?'success':'danger' }}">{{ $kirim->holat }}</span></td>
                    </tr>
                    <tr><td class="text-muted">Jami</td>
                        <td class="fw-bold text-success fs-5">{{ number_format($kirim->umumiy_summa,0,'.',' ') }} so'm</td>
                    </tr>
                </table>
                @if($kirim->izoh)
                <div class="alert alert-light mt-2 mb-0 py-2 small">{{ $kirim->izoh }}</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6 class="mb-0">Tovarlar ({{ $kirim->tafsilot->count() }} ta)</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tovar</th>
                            <th>Guruh</th>
                            <th class="text-end">Miqdor</th>
                            <th class="text-end">Tan narx</th>
                            <th class="text-end">Jami</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kirim->tafsilot as $i => $t)
                        <tr>
                            <td class="text-muted">{{ $i+1 }}</td>
                            <td class="fw-medium">{{ $t->tovar?->nomi }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $t->tovar?->guruh?->nomi ?? '—' }}</span></td>
                            <td class="text-end">{{ $t->miqdor }} {{ $t->tovar?->birlik }}</td>
                            <td class="text-end text-muted">{{ number_format($t->tan_narx,0,'.',' ') }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($t->jami_summa,0,'.',' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="fw-bold text-end">Jami:</td>
                            <td class="fw-bold text-success">{{ number_format($kirim->umumiy_summa,0,'.',' ') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
