{{-- ═══ TOVAR IZLASH MODAL ════════════════════════════════════════════ --}}
<div class="modal fade" id="tovarIzlashModal" tabindex="-1" aria-labelledby="tovarModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">

      {{-- Header --}}
      <div class="modal-header py-2" style="background:linear-gradient(135deg,#1e3a5f,#2563eb)">
        <div class="d-flex align-items-center gap-2 flex-grow-1">
          <i class="bi bi-tv text-white fs-5"></i>
          <h6 class="modal-title text-white fw-bold mb-0" id="tovarModalLabel">Ombordan tovar tanlash</h6>
        </div>
        <div class="ms-3 flex-grow-1" style="max-width:320px">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-0">
              <i class="bi bi-search text-primary"></i>
            </span>
            <input type="text" id="tovar-modal-qidiruv" class="form-control border-0"
                   placeholder="Tovar nomi yoki shtrix-kod (skaner)..." autocomplete="off"
                   onkeydown="if(event.key==='Enter'){event.preventDefault();tovarModalBarkodSkan();}">
            <button type="button" class="btn btn-outline-light btn-sm" id="tovar-modal-tozala"
                    onclick="document.getElementById('tovar-modal-qidiruv').value='';tovarModalFiltr('')"
                    title="Tozalash">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal"></button>
      </div>

      {{-- Guruh tablar --}}
      <div class="modal-body p-0">
        <div class="px-3 pt-2 pb-0 border-bottom">
          <ul class="nav nav-tabs nav-tabs-sm border-0 gap-1" id="tovar-guruh-tablar">
            <li class="nav-item">
              <button class="nav-link active py-1 px-2 small fw-bold" data-guruh="0" onclick="tovarGuruhFilter(0,this)">
                Hammasi
              </button>
            </li>
            @foreach($tovarGuruhlar as $g)
            <li class="nav-item">
              <button class="nav-link py-1 px-2 small" data-guruh="{{ $g->id }}" onclick="tovarGuruhFilter({{ $g->id }},this)">
                {{ $g->nomi }}
                <span class="badge bg-secondary ms-1" style="font-size:.65rem">{{ $g->tovarlar->count() }}</span>
              </button>
            </li>
            @endforeach
          </ul>
        </div>

        {{-- Tovar jadval --}}
        <div style="max-height:420px;overflow-y:auto">
          <table class="table table-hover table-sm mb-0" id="tovar-modal-jadval">
            <thead class="table-light sticky-top">
              <tr>
                <th style="width:50%">Tovar nomi</th>
                <th style="width:15%" class="text-center">Qoldiq</th>
                <th style="width:15%" class="text-end">Narx (so'm)</th>
                <th style="width:20%" class="text-center">Birlik</th>
              </tr>
            </thead>
            <tbody id="tovar-modal-tbody">
              @foreach($tovarGuruhlar as $g)
                @foreach($g->tovarlar as $t)
                <tr class="tovar-modal-qator"
                    data-id="{{ $t->id }}"
                    data-nomi="{{ $t->nomi }}"
                    data-barkod="{{ $t->barkod }}"
                    data-narx="{{ (int)$t->nasiya_narx }}"
                    data-qoldiq="{{ (float)$t->qoldiq }}"
                    data-birlik="{{ $t->birlik }}"
                    data-guruh="{{ $g->id }}"
                    style="cursor:pointer"
                    ondblclick="tovarModalTanlash(this)"
                    title="2 marta bosing — qatorga qo'shish">
                  <td>
                    <div class="fw-medium small">{{ $t->nomi }}</div>
                    <div class="text-muted" style="font-size:.7rem">{{ $g->nomi }}</div>
                  </td>
                  <td class="text-center small">
                    @if($t->qoldiq > 0)
                      <span style="background:#d1fae5;color:#065f46;font-weight:700;padding:2px 10px;border-radius:6px;font-size:.8rem">
                        {{ number_format($t->qoldiq, 0) }}
                      </span>
                    @else
                      <span style="background:#fee2e2;color:#991b1b;font-weight:700;padding:2px 8px;border-radius:6px;font-size:.8rem">0</span>
                    @endif
                  </td>
                  <td class="text-end small fw-medium">
                    {{ number_format($t->nasiya_narx, 0, '.', ' ') }}
                  </td>
                  <td class="text-center text-muted small">{{ $t->birlik }}</td>
                </tr>
                @endforeach
              @endforeach
            </tbody>
          </table>
          <div id="tovar-modal-empty" class="text-center text-muted py-5 d-none">
            <i class="bi bi-search fs-2 d-block mb-2 opacity-25"></i>
            Tovar topilmadi
          </div>
        </div>
      </div>

      <div class="modal-footer py-2 justify-content-between">
        <small class="text-muted">
          <i class="bi bi-hand-index me-1"></i>2 marta bosing — qatorga qo'shiladi
          &nbsp;|&nbsp; <span id="tovar-modal-soni">0</span> ta tovar
        </small>
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Yopish</button>
      </div>

    </div>
  </div>
</div>
