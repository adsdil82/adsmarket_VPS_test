@php $child = $child ?? false; @endphp
<tr>
    <td class="{{ $child ? 'ps-4' : '' }}">
        @if($child)<i class="bi bi-arrow-return-right me-1 text-muted" style="font-size:.7rem"></i>@endif
        <code style="font-size:.75rem">{{ $kat->kod }}</code>
    </td>
    <td class="{{ $child ? 'text-muted' : 'fw-semibold' }}">
        @php $rangCss = ['gray'=>'secondary','green'=>'success','blue'=>'primary','red'=>'danger','yellow'=>'warning','purple'=>'secondary','orange'=>'warning','teal'=>'info','pink'=>'danger']; @endphp
        <span class="badge bg-{{ $rangCss[$kat->rang] ?? 'secondary' }}" style="font-size:.6rem">&nbsp;</span>
        {{ $kat->nomi }}
        @if($kat->avtomatik)<span class="badge bg-light text-muted ms-1" style="font-size:.6rem">auto</span>@endif
    </td>
    <td>
        <span class="badge bg-{{ $kat->holat==='faol' ? 'success' : 'secondary' }}" style="font-size:.7rem">
            {{ $kat->holat==='faol' ? 'Faol' : 'Nofaol' }}
        </span>
    </td>
    <td class="text-end pe-2">
        @if(!$kat->avtomatik)
        <button class="btn btn-outline-primary btn-sm py-0 px-1"
                data-bs-toggle="modal" data-bs-target="#pkEdit{{ $kat->id }}">
            <i class="bi bi-pencil" style="font-size:.7rem"></i>
        </button>
        <form method="POST" action="{{ route('malumotnamalar.pul-kategoriyalar.destroy', $kat) }}" class="d-inline"
              onsubmit="return confirm('O\'chirish?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm py-0 px-1"><i class="bi bi-trash" style="font-size:.7rem"></i></button>
        </form>
        @endif
    </td>
</tr>

<div class="modal fade" id="pkEdit{{ $kat->id }}" tabindex="-1">
    <div class="modal-dialog modal-md">
        <form method="POST" action="{{ route('malumotnamalar.pul-kategoriyalar.update', $kat) }}" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Kategoriyani tahrirlash</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-8">
                        <label class="form-label small fw-bold">Nomi</label>
                        <input type="text" name="nomi" class="form-control form-control-sm" value="{{ $kat->nomi }}" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-bold">Kod</label>
                        <input type="text" name="kod" class="form-control form-control-sm font-monospace" value="{{ $kat->kod }}" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Ota kategoriya</label>
                        <select name="ota_id" class="form-select form-select-sm">
                            <option value="">— asosiy —</option>
                            @foreach($barcha as $a)
                            @if($a->id !== $kat->id)
                            <option value="{{ $a->id }}" {{ $kat->ota_id == $a->id ? 'selected' : '' }}>{{ $a->nomi }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Rang</label>
                        <select name="rang" class="form-select form-select-sm">
                            @foreach($ranglar as $r)
                            <option value="{{ $r }}" {{ $kat->rang === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Holat</label>
                        <select name="holat" class="form-select form-select-sm">
                            <option value="faol" {{ $kat->holat==='faol' ? 'selected' : '' }}>Faol</option>
                            <option value="nofaol" {{ $kat->holat==='nofaol' ? 'selected' : '' }}>Nofaol</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bekor</button>
                <button type="submit" class="btn btn-primary btn-sm">Saqlash</button>
            </div>
        </form>
    </div>
</div>
