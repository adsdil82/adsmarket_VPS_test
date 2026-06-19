{{--
  kredit/_create_modal.blade.php
  "Yangi shartnoma" uchun Bootstrap fullscreen modal
  O'zgaruvchilar: $filiallar (formFiliallar dan), $tovarGuruhlar
--}}
<div class="modal fade" id="kreditYangiModal" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false"
     aria-labelledby="kreditYangiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable"
       style="max-width:1140px;margin:1rem auto">
    <div class="modal-content border-0 shadow-lg">

      {{-- Header --}}
      <div class="modal-header py-2 px-3"
           style="background:linear-gradient(135deg,#1e3a5f,#2563eb)">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-file-earmark-plus text-white fs-5"></i>
          <h6 class="modal-title text-white fw-bold mb-0" id="kreditYangiModalLabel">
            Yangi nasiya shartnomasi
          </h6>
        </div>
        <button type="button" class="btn-close btn-close-white"
                data-bs-dismiss="modal" aria-label="Yopish"></button>
      </div>

      {{-- Body --}}
      <div class="modal-body p-0">

        @if($errors->any())
        <div class="alert alert-danger mx-3 mt-3 mb-0 py-2">
          <ul class="mb-0 ps-3 small">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('kreditlar.store') }}"
              id="kredit-form" autocomplete="off">
          @csrf
          @php
            $isEdit = false;
            $kredit = null;
          @endphp
          @include('kredit._form_tabs')
        </form>

      </div>
    </div>
  </div>
</div>

{{-- Validatsiya xatosi bo'lsa modal avtomatik ochilsin --}}
@if($errors->any() && old('_token'))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('kreditYangiModal');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
});
</script>
@endpush
@endif
