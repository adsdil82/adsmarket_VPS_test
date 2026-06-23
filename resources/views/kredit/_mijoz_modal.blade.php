<div class="modal fade" id="mijozIzlashModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl" id="mijozModalDialog" style="max-width:1040px">
    <div class="modal-content border-0 shadow-lg">

      {{-- Header --}}
      <div class="modal-header py-2" id="mijozModalHeader" style="background:linear-gradient(135deg,#15803d,#16a34a);cursor:move">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-person-search text-white fs-5"></i>
          <h6 class="modal-title text-white fw-bold mb-0" id="mijozModalTitle">Mijoz tanlash</h6>
        </div>
        {{-- Qidiruv (markazda) --}}
        <div class="mx-3 flex-grow-1" style="max-width:320px">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-0">
              <i class="bi bi-search text-success"></i>
            </span>
            <input type="text" id="mijoz-modal-qidiruv" class="form-control border-0"
                   placeholder="Ism, telefon, passport..." autocomplete="off">
            <div id="mijoz-modal-spinner" class="input-group-text bg-white border-0 d-none">
              <div class="spinner-border spinner-border-sm text-success"></div>
            </div>
          </div>
        </div>
        {{-- Yangi mijoz tugmasi -- header o'ngida -- har doim ko'rinadi --}}
        @if(Auth::user()->isMenejerYoki())
        <a href="{{ route('mijozlar.create') }}" target="_blank"
           class="btn btn-light btn-sm me-2 fw-bold"
           title="Yangi oynada yangi mijoz yaratish formasini oching">
          <i class="bi bi-person-plus me-1 text-success"></i>Yangi mijoz
        </a>
        @endif
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      {{-- Body --}}
      <div class="modal-body p-0">
        <div id="mijoz-modal-body-inner" style="min-height:200px">
          <div id="mijoz-modal-hint" class="text-center text-muted py-5">
            <i class="bi bi-search fs-2 d-block mb-2 opacity-25"></i>
            Qidirish uchun ism yoki telefon kiriting...
          </div>
          <div id="mijoz-modal-empty" class="text-center text-muted py-4 d-none">
            <i class="bi bi-person-x fs-2 d-block mb-2 opacity-25"></i>
            <div>Mijoz topilmadi</div>
            <div class="small mt-2">Yuqoridagi <strong>+ Yangi mijoz</strong> tugmasi orqali qo'shing</div>
          </div>
          <table class="table table-hover table-sm mb-0 d-none" id="mijoz-modal-jadval">
            <thead class="table-light sticky-top">
              <tr>
                <th>F.I.O.</th>
                <th>Telefon</th>
                <th>Passport</th>
                <th>Filial</th>
                <th class="text-center">Holat</th>
              </tr>
            </thead>
            <tbody id="mijoz-modal-tbody"></tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer py-2 justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
          <small class="text-muted">
            <i class="bi bi-hand-index me-1"></i>2 marta bosing
            &nbsp;|&nbsp; <span id="mijoz-modal-soni-hdr" class="fw-bold"></span>
          </small>
          {{-- Pagination --}}
          <div id="mijoz-pagination" class="d-none align-items-center gap-1 d-flex">
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2"
                    id="mijoz-prev-btn" onclick="mijozSahifaOtish(-1)" disabled>
              <i class="bi bi-chevron-left"></i>
            </button>
            <span id="mijoz-page-info" class="small text-muted px-1">1 / 1</span>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2"
                    id="mijoz-next-btn" onclick="mijozSahifaOtish(1)" disabled>
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Yopish</button>
      </div>
    </div>
  </div>
</div>
