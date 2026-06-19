@extends('layouts.app')
@section('title','Xabar Shablonlari')
@section('breadcrumb')
    <li class="breadcrumb-item active">Xabar Shablonlari</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-file-text me-2 text-warning"></i>Xabar Shablonlari</h5>
    <a href="{{ route('xabarnoma.shablonlar.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus me-1"></i>Yangi shablon
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Nomi</th><th>Kanal</th><th>Kod</th><th>O'zgaruvchilar</th><th>Holat</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($shablonlar as $s)
                <tr>
                    <td class="fw-medium">{{ $s->name }}</td>
                    <td>
                        @php $rang = match($s->channel){ 'sms'=>'warning','telegram'=>'info','email'=>'primary','hybrid_mail'=>'secondary',default=>'secondary' }; @endphp
                        <span class="badge bg-{{ $rang }} text-uppercase" style="font-size:.65rem">{{ $s->channel }}</span>
                        @if($s->is_default)<span class="badge bg-success ms-1" style="font-size:.6rem">default</span>@endif
                    </td>
                    <td><code class="small">{{ $s->code }}</code></td>
                    <td class="small text-muted">
                        @foreach($s->variables as $v)
                        <span class="badge bg-light text-dark border me-1" style="font-size:.6rem">{{'{'.$v.'}'}}</span>
                        @endforeach
                    </td>
                    <td>
                        <span class="badge bg-{{ $s->is_active ? 'success' : 'secondary' }}" style="font-size:.65rem">
                            {{ $s->is_active ? 'Faol' : 'Nofaol' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('xabarnoma.shablonlar.edit', $s) }}" class="btn btn-sm btn-outline-primary py-0">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-secondary py-0 ms-1" onclick="previewShablon({{ $s->id }},'{{ addslashes($s->name) }}','{{ addslashes($s->body) }}')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Shablonlar yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($shablonlar->hasPages())
    <div class="card-footer py-2">{{ $shablonlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- O'zgaruvchilar spravka --}}
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header py-2 small fw-bold"><i class="bi bi-braces me-1"></i>Mavjud o'zgaruvchilar</div>
    <div class="card-body py-2">
        <div class="row g-1">
            @foreach($variables as $key => $desc)
            <div class="col-sm-3 col-6">
                <span class="badge bg-light text-dark border me-1" style="font-size:.7rem">{{'{'.$key.'}'}}</span>
                <small class="text-muted">{{ $desc }}</small>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header py-2 bg-warning">
        <h6 class="modal-title fw-bold" id="preview-title">Preview</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <pre id="preview-body" class="small bg-light p-3 rounded" style="white-space:pre-wrap"></pre>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
var _previewModal = null;
function previewShablon(id, name, body) {
    document.getElementById('preview-title').textContent = name;
    document.getElementById('preview-body').textContent = body;
    if (!_previewModal) _previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    _previewModal.show();
}
</script>
@endpush