<div class="row g-3 mb-3">
    <div class="col-md-5">
        <label class="form-label small fw-medium">Shablon</label>
        <select id="sms-shablon" class="form-select form-select-sm">
            <option value="">— Bo'sh xabar (qo'lda yozish) —</option>
            @foreach($sms_shablonlar as $sh)
            <option value="{{ $sh->id }}">{{ $sh->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-medium">Telefon raqam</label>
        @php $smsRaqamlari = $kredit->mijoz->sms_raqamlari ?? []; @endphp
        @if(count($smsRaqamlari) > 1)
        <select id="sms-tel-select" class="form-select form-select-sm" onchange="document.getElementById('sms-tel').value=this.value">
            @foreach($smsRaqamlari as $r)
            <option value="{{ $r }}">{{ $r }}</option>
            @endforeach
        </select>
        <input type="hidden" id="sms-tel" value="{{ $smsRaqamlari[0] ?? '' }}">
        @else
        <input type="text" id="sms-tel" class="form-control form-control-sm"
               value="{{ $smsRaqamlari[0] ?? ($kredit->mijoz->telefon ?? '') }}" placeholder="+998901234567">
        @endif
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button type="button" class="btn btn-sm btn-primary w-100" id="sms-yubor-btn" onclick="smsTabYuborish()">
            <i class="bi bi-send me-1"></i>SMS yuborish
        </button>
    </div>
    <div class="col-12">
        <label class="form-label small fw-medium">Xabar matni</label>
        <textarea id="sms-matn" class="form-control form-control-sm" rows="3" maxlength="800"
                  placeholder="Shablon tanlang yoki qo'lda yozing"></textarea>
        <div class="form-text small"><span id="sms-belgi-son">0</span> belgi</div>
        <div id="sms-cooldown-info" class="form-text small text-warning d-none">
            <i class="bi bi-clock-history me-1"></i><span id="sms-cooldown-matn"></span>
        </div>
    </div>
    <div class="col-12">
        <div id="sms-natija" class="alert d-none py-2 small mb-0"></div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0" id="sms-log-table">
        <thead class="table-light">
            <tr>
                <th style="width:120px">Sana</th>
                <th>Shablon</th>
                <th>Telefon</th>
                <th style="width:110px">Holat</th>
                <th style="width:100px">Provider</th>
                <th>Izoh / Xato</th>
            </tr>
        </thead>
        <tbody id="sms-log-tbody">
            @forelse($sms_loglar as $log)
            @php
                $statusMatn = match($log->status) {
                    'sent' => 'Yuborildi', 'test' => 'Test rejim', 'skipped' => 'Bekor qilindi',
                    'failed' => 'Xato', default => $log->status,
                };
            @endphp
            <tr>
                <td class="small text-muted">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                <td class="small">{{ $log->template?->name ?? '—' }}</td>
                <td class="small">{{ $log->phone }}</td>
                <td><span class="badge bg-{{ $log->status_rangi }}">{{ $statusMatn }}</span></td>
                <td class="small text-muted">{{ $log->provider ?? '—' }}</td>
                <td class="small text-muted">{{ $log->error_message ?? '—' }}</td>
            </tr>
            @empty
            <tr id="sms-log-empty">
                <td colspan="6" class="text-center text-muted py-3 small">
                    <i class="bi bi-chat-dots me-1"></i>Bu shartnoma uchun hali SMS yuborilmagan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
(function() {
    const smsShablonMatnlari = @json($sms_shablon_matnlari);
    let smsOxirgi24Soat      = @json($sms_oxirgi_24soat); // {template_id: ISO vaqt}

    const shablonSelect  = document.getElementById('sms-shablon');
    const matnInput       = document.getElementById('sms-matn');
    const belgiSon        = document.getElementById('sms-belgi-son');
    const cooldownInfo     = document.getElementById('sms-cooldown-info');
    const cooldownMatn     = document.getElementById('sms-cooldown-matn');
    const btn              = document.getElementById('sms-yubor-btn');

    function qolganVaqtMatni(isoVaqt) {
        const yuborilgan = new Date(isoVaqt).getTime();
        const qolgan = 24 * 60 * 60 * 1000 - (Date.now() - yuborilgan);
        if (qolgan <= 0) return null;
        const soat = Math.floor(qolgan / 3600000);
        const daqiqa = Math.floor((qolgan % 3600000) / 60000);
        return soat + ' soat ' + daqiqa + ' daqiqa';
    }

    function cooldownTekshir() {
        const tplId = shablonSelect.value;
        const oxirgi = tplId ? smsOxirgi24Soat[tplId] : null;
        const qolganMatn = oxirgi ? qolganVaqtMatni(oxirgi) : null;

        if (qolganMatn) {
            btn.disabled = true;
            btn.classList.add('opacity-50');
            cooldownMatn.textContent = "Bu shablon ushbu shartnoma uchun so'nggi 24 soat ichida yuborilgan. Yana " + qolganMatn + " dan keyin yuborish mumkin.";
            cooldownInfo.classList.remove('d-none');
        } else {
            btn.disabled = false;
            btn.classList.remove('opacity-50');
            cooldownInfo.classList.add('d-none');
        }
    }

    shablonSelect?.addEventListener('change', function() {
        const matn = smsShablonMatnlari[this.value] || '';
        matnInput.value = matn;
        belgiSon.textContent = matn.length;
        cooldownTekshir();
    });
    matnInput?.addEventListener('input', function() {
        belgiSon.textContent = this.value.length;
    });

    cooldownTekshir();

    window.smsTabYuborish = function() {
        const tel    = document.getElementById('sms-tel').value.trim();
        const matn   = matnInput.value.trim();
        const natija = document.getElementById('sms-natija');

        if (!tel || !matn) {
            natija.className = 'alert alert-danger py-2 small mb-0';
            natija.textContent = "Telefon raqam va xabar matni to'ldirilishi shart.";
            natija.classList.remove('d-none');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Yuborilmoqda...';

        fetch('{{ route("kreditlar.sms.yubor", $kredit) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                phone: tel,
                message: matn,
                template_id: shablonSelect.value || null
            })
        })
        .then(r => r.json())
        .then(d => {
            natija.className = 'alert py-2 small mb-0 ' + (d.ok ? 'alert-success' : 'alert-danger');
            natija.textContent = d.ok
                ? ('SMS ' + d.status_text + '! (' + (d.provider || '—') + ')')
                : (d.status_text + (d.error ? ': ' + d.error : ''));
            natija.classList.remove('d-none');

            document.getElementById('sms-log-empty')?.remove();
            const tbody = document.getElementById('sms-log-tbody');
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td class="small text-muted">' + d.sana + '</td>' +
                '<td class="small">' + d.shablon + '</td>' +
                '<td class="small">' + d.telefon + '</td>' +
                '<td><span class="badge bg-' + d.status_rang + '">' + d.status_text + '</span></td>' +
                '<td class="small text-muted">' + (d.provider || '—') + '</td>' +
                '<td class="small text-muted">' + (d.error || '—') + '</td>';
            tbody.prepend(tr);

            // Muvaffaqiyatli yuborilgan shablon uchun darhol 24 soatlik cooldown boshlash
            if (d.ok && shablonSelect.value) {
                smsOxirgi24Soat[shablonSelect.value] = new Date().toISOString();
            }
        })
        .catch(() => {
            natija.className = 'alert alert-danger py-2 small mb-0';
            natija.textContent = "So'rov yuborishda xato yuz berdi.";
            natija.classList.remove('d-none');
        })
        .finally(() => {
            btn.innerHTML = '<i class="bi bi-send me-1"></i>SMS yuborish';
            cooldownTekshir();
        });
    };
})();
</script>
