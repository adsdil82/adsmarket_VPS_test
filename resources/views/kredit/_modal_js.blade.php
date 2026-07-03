@push('scripts')
<script>
// ─── MIJOZ MODAL ────────────────────────────────────────────────────

(function() {
    var el = document.getElementById('mijozIzlashModal');
    if (!el) return;
    el.addEventListener('shown.bs.modal', function() {
        var dialog = document.getElementById('mijozModalDialog');
        if (!dialog) return;
        dialog.style.position = 'fixed';
        dialog.style.left = Math.max(0, (window.innerWidth - dialog.offsetWidth) / 2) + 'px';
        dialog.style.top  = Math.max(0, window.innerHeight * 0.05) + 'px';
        dialog.style.margin = '0';
    });
    el.addEventListener('hidden.bs.modal', function() {
        var d = document.getElementById('mijozModalDialog');
        if (d) { d.style.position=''; d.style.left=''; d.style.top=''; d.style.margin=''; }
    });
    document.addEventListener('mousedown', function(e) {
        var header = document.getElementById('mijozModalHeader');
        if (!header || !header.contains(e.target)) return;
        if (e.target.closest('button, a')) return;
        var dialog = document.getElementById('mijozModalDialog');
        if (!dialog || dialog.style.position !== 'fixed') return;
        e.preventDefault();
        var sx = e.clientX - dialog.offsetLeft, sy = e.clientY - dialog.offsetTop;
        function mv(ev) {
            dialog.style.left = Math.max(0, Math.min(window.innerWidth  - dialog.offsetWidth,  ev.clientX - sx)) + 'px';
            dialog.style.top  = Math.max(0, Math.min(window.innerHeight - dialog.offsetHeight, ev.clientY - sy)) + 'px';
        }
        function up() { document.removeEventListener('mousemove', mv); document.removeEventListener('mouseup', up); }
        document.addEventListener('mousemove', mv);
        document.addEventListener('mouseup', up);
    });
})();

var _mijozModal  = null;
var _mijozTimer  = null;
var _mijozPage   = 1;
var _mijozQ      = '';
var _mijozPages  = 1;
var _mijozModalMaqsad = 'mijoz';

function mijozModalOch(maqsad) {
    var elM = document.getElementById('mijozIzlashModal');
    if (!elM) { console.error('mijozIzlashModal topilmadi'); return; }
    if (!_mijozModal) _mijozModal = new bootstrap.Modal(elM, { backdrop: false, keyboard: true });
    _mijozModalMaqsad = maqsad || 'mijoz';
    var titleEl = document.getElementById('mijozModalTitle');
    if (titleEl) titleEl.textContent = _mijozModalMaqsad === 'kafil' ? 'Kafil tanlash' : 'Mijoz tanlash';
    _mijozPage  = 1;
    _mijozQ     = '';
    _mijozPages = 1;
    document.getElementById('mijoz-modal-qidiruv').value = '';
    document.getElementById('mijoz-modal-jadval').classList.add('d-none');
    document.getElementById('mijoz-modal-empty').classList.add('d-none');
    document.getElementById('mijoz-modal-hint').classList.remove('d-none');
    document.getElementById('mijoz-modal-tbody').innerHTML = '';
    document.getElementById('mijoz-modal-spinner').classList.remove('d-none');
    _mijozHidePagination();
    _mijozModal.show();
    setTimeout(function() {
        mijozQidirAjax('', 1);
        document.getElementById('mijoz-modal-qidiruv').focus();
    }, 300);
}

function mijozSahifaOtish(delta) {
    var newPage = _mijozPage + delta;
    if (newPage < 1 || newPage > _mijozPages) return;
    _mijozPage = newPage;
    document.getElementById('mijoz-modal-spinner').classList.remove('d-none');
    document.getElementById('mijoz-modal-jadval').classList.add('d-none');
    mijozQidirAjax(_mijozQ, _mijozPage);
}

function _mijozHidePagination() {
    var pg = document.getElementById('mijoz-pagination');
    if (pg) pg.classList.add('d-none');
}

function _mijozUpdatePagination(page, pages, total) {
    _mijozPage  = page;
    _mijozPages = pages;
    var pg = document.getElementById('mijoz-pagination');
    if (!pg) return;
    if (pages <= 1) { pg.classList.add('d-none'); return; }
    pg.classList.remove('d-none');
    document.getElementById('mijoz-page-info').textContent = page + ' / ' + pages + '  (' + total + ' ta)';
    var prev = document.getElementById('mijoz-prev-btn');
    var next = document.getElementById('mijoz-next-btn');
    if (prev) prev.disabled = (page <= 1);
    if (next) next.disabled = (page >= pages);
}

function mijozModalTanlash(row) {
    if (row.dataset.taqiqlangan === '1' && _mijozModalMaqsad === 'mijoz') {
        alert("Bu mijoz holati «" + row.dataset.holatNomi + "» — yangi shartnoma tuzish taqiqlangan.");
        return;
    }

    if (_mijozModalMaqsad === 'kafil') {
        document.getElementById('kafil_mijoz_id').value  = row.dataset.id;
        document.getElementById('kafil-tanlangan').value = row.dataset.fio;
        document.getElementById('kafil-info').innerHTML  = _mijozInfoHtml(row);
        // Mijoz tanlanganda qo'lda kiritilgan kafil maydonlari endi kerak emas
        ['kafil_ism', 'kafil_telefon', 'kafil_manzil'].forEach(function(nomi) {
            var el = document.querySelector('[name="' + nomi + '"]');
            if (el) el.value = '';
        });
        var qolda = document.getElementById('kafil-qolda');
        if (qolda) qolda.classList.add('d-none');
        if (_mijozModal) _mijozModal.hide();
        return;
    }

    document.getElementById('mijoz_id').value        = row.dataset.id;
    document.getElementById('mijoz-tanlangan').value = row.dataset.fio;
    document.getElementById('mijoz-info').innerHTML  = _mijozInfoHtml(row);
    var xEl = document.getElementById('mijoz-info-xato');
    if (xEl) xEl.style.display = 'none';
    if (_mijozModal) _mijozModal.hide();
}

/** Tanlangan mijoz/kafil uchun to'liq ma'lumot blokini quradi (telefon, manzil, PINFL, karta, izoh) */
function _mijozInfoHtml(row) {
    var d = row.dataset;
    var html = '<div class="border rounded p-2 bg-light mt-1">' +
        '<div><i class="bi bi-check-circle text-success me-1"></i><strong>' + d.fio + '</strong></div>' +
        '<div class="text-muted mt-1">' +
        '<i class="bi bi-telephone me-1"></i>' + (d.telefon || '—');
    if (d.passport) html += ' &nbsp;&middot;&nbsp; <i class="bi bi-card-text me-1"></i>' + d.passport;
    if (d.pinfl)     html += ' &nbsp;&middot;&nbsp; PINFL: ' + d.pinfl;
    if (d.karta)     html += ' &nbsp;&middot;&nbsp; <i class="bi bi-credit-card me-1"></i>' + d.karta;
    html += '</div>';
    if (d.manzil) html += '<div class="text-muted"><i class="bi bi-geo-alt me-1"></i>' + d.manzil + '</div>';
    if (d.izoh)   html += '<div class="text-muted"><i class="bi bi-chat-left-text me-1"></i>' + d.izoh + '</div>';
    html += '</div>';
    return html;
}

function kafilTanlovniTozala() {
    document.getElementById('kafil_mijoz_id').value  = '';
    document.getElementById('kafil-tanlangan').value = '';
    document.getElementById('kafil-info').innerHTML  =
        '<span class="text-muted">Kafil tanlanmagan (ixtiyoriy)</span>';
}

document.addEventListener('DOMContentLoaded', function() {
    var mqEl = document.getElementById('mijoz-modal-qidiruv');
    if (mqEl) {
        mqEl.addEventListener('input', function() {
            clearTimeout(_mijozTimer);
            _mijozQ = this.value.trim();
            _mijozPage = 1;
            document.getElementById('mijoz-modal-spinner').classList.remove('d-none');
            document.getElementById('mijoz-modal-hint').classList.add('d-none');
            _mijozTimer = setTimeout(function() { mijozQidirAjax(_mijozQ, 1); }, 280);
        });
        mqEl.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            var rows = document.querySelectorAll('#mijoz-modal-tbody tr');
            if (rows.length === 1) { mijozModalTanlash(rows[0]); return; }
            clearTimeout(_mijozTimer);
            document.getElementById('mijoz-modal-spinner').classList.remove('d-none');
            mijozQidirAjax(mqEl.value.trim(), 1);
        });
    }
    var tvQ = document.getElementById('tovar-modal-qidiruv');
    if (tvQ) tvQ.addEventListener('input', function() { tovarModalFiltr(); });
    var tvModal = document.getElementById('tovarIzlashModal');
    if (tvModal) {
        tvModal.addEventListener('shown.bs.modal', function() {
            tovarGuruhFilter(_aktifGuruh, document.querySelector('#tovar-guruh-tablar button.active'));
        });
    }
});

function mijozQidirAjax(q, page) {
    var filialEl = document.querySelector('[name=filial_id]');
    $.getJSON('{{ route("mijozlar.ajax.qidiruv") }}', {
        q: q || '',
        filial_id: filialEl ? filialEl.value : '',
        page: page || 1
    })
    .done(function(res) {
        document.getElementById('mijoz-modal-spinner').classList.add('d-none');
        var data   = res.data !== undefined ? res.data : res;
        var total  = res.total !== undefined ? res.total : data.length;
        var pg     = res.page  !== undefined ? res.page  : 1;
        var pages  = res.pages !== undefined ? res.pages : 1;

        var tbody  = document.getElementById('mijoz-modal-tbody');
        var jadval = document.getElementById('mijoz-modal-jadval');
        var empty  = document.getElementById('mijoz-modal-empty');
        document.getElementById('mijoz-modal-hint').classList.add('d-none');

        if (!data.length) {
            jadval.classList.add('d-none');
            empty.classList.remove('d-none');
            tbody.innerHTML = '';
            _mijozHidePagination();
            return;
        }
        empty.classList.add('d-none');
        jadval.classList.remove('d-none');

        var hdr = document.getElementById('mijoz-modal-soni-hdr');
        if (hdr) {
            if (q) hdr.textContent = data.length + ' ta topildi';
            else   hdr.textContent = 'Jami ' + total + ' ta';
        }

        var holatBadgeStil = {
            faol:   { bg:'#d1fae5', fg:'#065f46', matn:'Faol' },
            nofaol: { bg:'#f1f5f9', fg:'#475569', matn:'Nofaol' },
            sudda:  { bg:'#fef3c7', fg:'#92400e', matn:'SUDDA' },
            yomon:  { bg:'#fee2e2', fg:'#991b1b', matn:'YOMON' }
        };
        var _attrEsc = function(s) { return (s||'').toString().replace(/"/g,'&quot;'); };
        tbody.innerHTML = data.map(function(m) {
            var st = holatBadgeStil[m.holat] || holatBadgeStil.nofaol;
            var badge = '<span style="background:' + st.bg + ';color:' + st.fg +
                ';font-weight:700;padding:2px 10px;border-radius:6px;font-size:.78rem">' + st.matn + '</span>';
            var taqiqlangan = (m.holat === 'sudda' || m.holat === 'yomon');
            return '<tr class="mijoz-modal-qator" style="cursor:pointer' + (taqiqlangan ? ';opacity:.55' : '') + '"' +
                ' data-id="' + m.id + '"' +
                ' data-fio="' + (m.fio||'').replace(/"/g,"'") + '"' +
                ' data-telefon="' + (m.telefon||'') + '"' +
                ' data-passport="' + (m.passport||'') + '"' +
                ' data-pinfl="' + _attrEsc(m.pinfl) + '"' +
                ' data-manzil="' + _attrEsc(m.manzil) + '"' +
                ' data-izoh="' + _attrEsc(m.izoh) + '"' +
                ' data-karta="' + _attrEsc(m.karta_raqami) + '"' +
                ' data-taqiqlangan="' + (taqiqlangan ? '1' : '0') + '"' +
                ' data-holat-nomi="' + st.matn + '"' +
                ' ondblclick="mijozModalTanlash(this)" title="' + (taqiqlangan ? 'Taqiqlangan: shartnoma tuzib bo\'lmaydi' : '2 marta bosing') + '">' +
                '<td><div class="fw-medium small">' + m.fio + '</div></td>' +
                '<td class="small">' + m.telefon + '</td>' +
                '<td class="small text-muted">' + m.passport + '</td>' +
                '<td class="small text-muted">' + (m.filial||'') + '</td>' +
                '<td class="text-center">' + badge + '</td></tr>';
        }).join('');

        _mijozUpdatePagination(pg, pages, total);
    })
    .fail(function() {
        document.getElementById('mijoz-modal-spinner').classList.add('d-none');
        var empty = document.getElementById('mijoz-modal-empty');
        empty.classList.remove('d-none');
        if (empty.querySelector('div')) empty.querySelector('div').textContent = 'Xatolik. Sahifani yangilang.';
    });
}

// ─── TOVAR MODAL ────────────────────────────────────────────────────
var _activeTovarRow = null;
var _tovarModal     = null;
var _aktifGuruh     = 0;

function tovarModalOch(btn) {
    _activeTovarRow = btn ? btn.closest('.tovar-qator') : null;
    var elT = document.getElementById('tovarIzlashModal');
    if (!elT) { console.error('tovarIzlashModal topilmadi'); return; }
    if (!_tovarModal) _tovarModal = new bootstrap.Modal(elT, { backdrop: false, keyboard: true });
    document.getElementById('tovar-modal-qidiruv').value = '';
    document.querySelectorAll('.tovar-modal-qator').forEach(function(r){ r.classList.remove('table-success'); });
    tovarGuruhFilter(0, document.querySelector('#tovar-guruh-tablar button[data-guruh="0"]'));
    _tovarModal.show();
    setTimeout(function() { document.getElementById('tovar-modal-qidiruv').focus(); }, 300);
}

function tovarModalTanlash(tr) {
    if (!_activeTovarRow) return;
    _activeTovarRow.querySelector('.tovar-nomi-inp').value = tr.dataset.nomi;
    var narxInp = _activeTovarRow.querySelector('.tovar-narx');
    var soniInp = _activeTovarRow.querySelector('.tovar-soni');
    narxInp.value = tr.dataset.narx;
    soniInp.value = 1;
    _activeTovarRow.querySelector('.tovar-katalog-id').value = tr.dataset.id;
    var jamiEl = _activeTovarRow.querySelector('.tovar-jami');
    if (jamiEl) jamiEl.value = formatSon(parseFloat(narxInp.value)||0);
    if (typeof tovarJamiYig === 'function') tovarJamiYig();
    if (typeof hisoblash === 'function') hisoblash();
    document.querySelectorAll('.tovar-modal-qator').forEach(function(r){ r.classList.remove('table-success'); });
    tr.classList.add('table-success');
    if (_tovarModal) _tovarModal.hide();
}

function tovarGuruhFilter(guruhId, btn) {
    _aktifGuruh = parseInt(guruhId);
    document.querySelectorAll('#tovar-guruh-tablar button').forEach(function(b){ b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    var q = (document.getElementById('tovar-modal-qidiruv').value||'').toLowerCase().trim();
    var rows = document.querySelectorAll('#tovar-modal-tbody .tovar-modal-qator');
    var visible = 0;
    rows.forEach(function(row) {
        var ok = (_aktifGuruh===0 || parseInt(row.dataset.guruh)===_aktifGuruh) &&
                 (!q || (row.dataset.nomi||'').toLowerCase().includes(q) || (row.dataset.barkod||'').includes(q));
        row.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });
    var soni = document.getElementById('tovar-modal-soni');
    if (soni) soni.textContent = visible;
    var empty  = document.getElementById('tovar-modal-empty');
    var jadval = document.getElementById('tovar-modal-jadval');
    if (visible === 0) {
        if (empty)  empty.classList.remove('d-none');
        if (jadval) jadval.classList.add('d-none');
    } else {
        if (empty)  empty.classList.add('d-none');
        if (jadval) jadval.classList.remove('d-none');
    }
}

function tovarModalFiltr(q) {
    if (q !== undefined) {
        var inp = document.getElementById('tovar-modal-qidiruv');
        if (inp) inp.value = q;
    }
    tovarGuruhFilter(_aktifGuruh, document.querySelector('#tovar-guruh-tablar button.active'));
}

/**
 * Shtrix-kod skaneri: qidiruv maydoniga tez matn kiritib Enter bosilganda,
 * aniq barkodga mos qator topilsa avtomat tanlanadi (modal darhol yopiladi).
 */
function tovarModalBarkodSkan() {
    var q = (document.getElementById('tovar-modal-qidiruv').value || '').trim();
    if (!q) return;
    var mos = null;
    document.querySelectorAll('.tovar-modal-qator').forEach(function(row) {
        if (row.dataset.barkod === q) mos = row;
    });
    if (mos) {
        tovarModalTanlash(mos);
        return;
    }

    // Oldindan yuklangan 30 tadan topilmadi — serverdan ANIQ barkod bo'yicha qidiramiz
    // (masalan eski yoki kam sotiladigan tovar bo'lishi mumkin).
    fetch('{{ route("kreditlar.ajax.tovar_barkod") }}?barkod=' + encodeURIComponent(q), {
        headers: { 'Accept': 'application/json' }
    }).then(function(res) { return res.json().then(function(data) { return {ok: res.ok, data: data}; }); })
      .then(function(r) {
        if (!r.ok) {
            tovarModalFiltr();
            return;
        }
        var t = r.data;
        if (!_activeTovarRow) return;
        _activeTovarRow.querySelector('.tovar-nomi-inp').value = t.nomi;
        var narxInp = _activeTovarRow.querySelector('.tovar-narx');
        var soniInp = _activeTovarRow.querySelector('.tovar-soni');
        narxInp.value = t.nasiya_narx;
        soniInp.value = 1;
        _activeTovarRow.querySelector('.tovar-katalog-id').value = t.id;
        var jamiEl = _activeTovarRow.querySelector('.tovar-jami');
        if (jamiEl) jamiEl.value = formatSon(parseFloat(narxInp.value)||0);
        if (typeof tovarJamiYig === 'function') tovarJamiYig();
        if (typeof hisoblash === 'function') hisoblash();
        if (_tovarModal) _tovarModal.hide();
    });
}
</script>
@endpush