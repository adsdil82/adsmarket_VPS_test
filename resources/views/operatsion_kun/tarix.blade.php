@extends('layouts.app')
@section('title', 'Ish kuni — Yopish tarixi')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('operatsion_kun.index') }}">Ish kuni</a></li>
<li class="breadcrumb-item active">Yopish tarixi</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Yopish / ochish tarixi</h5>
    <a href="{{ route('operatsion_kun.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Orqaga
    </a>
</div>

<form method="GET" class="row g-2 mb-3">
    @if($filiallar->isNotEmpty())
    <div class="col-auto">
        <select name="filial_id" class="form-select form-select-sm">
            <option value="">Barcha filiallar</option>
            @foreach($filiallar as $f)
            <option value="{{ $f->id }}" @selected($filialId == $f->id)>{{ $f->nomi }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="col-auto">
        <input type="date" name="sana_dan" class="form-control form-control-sm" value="{{ $sanaDan }}" placeholder="Sanadan">
    </div>
    <div class="col-auto">
        <input type="date" name="sana_gacha" class="form-control form-control-sm" value="{{ $sanaGacha }}" placeholder="Sanagacha">
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Qidirish</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light">
            <tr>
                <th>Vaqt</th>
                <th>Filial</th>
                <th>Sana</th>
                <th>Amal</th>
                <th>Foydalanuvchi</th>
                <th>Natija / izoh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loglar as $log)
            <tr>
                <td>{{ $log->vaqt->format('d.m.Y H:i') }}</td>
                <td>{{ $log->operatsionKun->filial->nomi ?? '—' }}</td>
                <td>{{ $log->operatsionKun->sana->format('d.m.Y') ?? '—' }}</td>
                <td>
                    @if($log->amal === 'yopish')
                        <span class="badge bg-danger">Yopish</span>
                    @else
                        <span class="badge bg-warning text-dark">Ochish</span>
                    @endif
                </td>
                <td>{{ $log->user->ism_familiya ?? '—' }}</td>
                <td class="small text-muted">
                    @if($log->amal === 'yopish' && $log->natija_json)
                        {{ $log->natija_json['jami_shartnomalar'] ?? 0 }} ta shartnoma,
                        {{ $log->natija_json['kechikkan_shartnomalar'] ?? 0 }} ta kechikkan
                    @elseif($log->natija_json['izoh'] ?? null)
                        {{ $log->natija_json['izoh'] }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Yozuv topilmadi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $loglar->links() }}
@endsection
