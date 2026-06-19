@extends('layouts.app')
@section('title','Viloyatlar va Tumanlar')
@section('content')
<div class="container-fluid px-3 py-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
      <i class="bi bi-geo-alt me-1 text-primary"></i>Viloyatlar va Tumanlar
      <span class="badge bg-secondary ms-1">{{ $viloyatlar->sum('tumanlar_count') }} tuman</span>
    </h5>
  </div>

  <div class="row g-3">
    @foreach($viloyatlar as $v)
    <div class="col-md-6 col-xl-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header py-2 d-flex align-items-center gap-2"
             style="background:linear-gradient(135deg,#1e40af08,#3b82f610)">
          <i class="bi bi-map text-primary"></i>
          <strong class="small flex-grow-1">{{ $v->nomi }}</strong>
          <span class="badge bg-primary rounded-pill">{{ $v->tumanlar_count }}</span>
          <button class="btn btn-xs btn-outline-secondary"
            data-bs-toggle="modal" data-bs-target="#editViloyatModal"
            data-id="{{ $v->id }}" data-nomi="{{ $v->nomi }}"
            onclick="editViloyat(this)" title="Tahrirlash">
            <i class="bi bi-pencil"></i>
          </button>
        </div>
        <div class="card-body py-2 px-3">
          <div id="tuman-list-{{ $v->id }}">
            <button class="btn btn-link btn-sm p-0 text-decoration-none text-muted load-tumanlar"
                    data-viloyat-id="{{ $v->id }}">
              <i class="bi bi-chevron-down me-1"></i>Tumanlarni ko'rish
            </button>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

</div>

{{-- Viloyat tahrirlash modal --}}
<div class="modal fade" id="editViloyatModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title">Viloyat nomi tahrirlash</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editViloyatId">
        <label class="form-label small">Nomi</label>
        <input type="text" id="editViloyatNomi" class="form-control form-control-sm">
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Bekor</button>
        <button type="button" class="btn btn-sm btn-primary" id="saveViloyatBtn">Saqlash</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
const csrf = document.querySelector('meta[name=csrf-token]')?.content ?? '';

// Tumanlarni lazy load qilish
document.addEventListener('click', async function(e) {
  const btn = e.target.closest('.load-tumanlar');
  if (!btn) return;
  const vid = btn.dataset.viloyatId;
  const box = document.getElementById('tuman-list-' + vid);
  btn.remove();

  const r = await fetch('/malumotnamalar/viloyatlar/' + vid + '/tumanlar', {headers:{'Accept':'application/json'}});
  const data = await r.json();

  let html = '<div class="row g-1">';
  data.forEach(t => {
    html += '<div class="col-6"><span class="badge bg-light text-dark border w-100 text-start">'
          + '<small>' + t.nomi + '</small></span></div>';
  });
  html += '</div>';
  box.innerHTML = html;
});

// Viloyat tahrirlash
window.editViloyat = function(btn) {
  document.getElementById('editViloyatId').value   = btn.dataset.id;
  document.getElementById('editViloyatNomi').value = btn.dataset.nomi;
};

document.getElementById('saveViloyatBtn')?.addEventListener('click', async function() {
  const id   = document.getElementById('editViloyatId').value;
  const nomi = document.getElementById('editViloyatNomi').value.trim();
  if (!nomi) return;

  const r = await fetch('/malumotnamalar/viloyatlar/' + id, {
    method: 'PUT',
    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
    body: JSON.stringify({nomi})
  });
  const d = await r.json();
  if (d.ok) window.location.reload();
  else alert('Xato saqlashda');
});

})();
</script>
@endpush
@endsection
