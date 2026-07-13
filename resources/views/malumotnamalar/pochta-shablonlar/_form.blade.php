<div class="row g-2">
    <div class="col-12">
        <label class="form-label small mb-1">Shablon nomi <span class="text-danger">*</span></label>
        <input type="text" name="nomi" id="{{ $prefix }}nomi"
            class="form-control form-control-sm"
            value="{{ old('nomi', $sh?->nomi) }}"
            placeholder="Masalan: Birinchi ogohlantirish" required>
    </div>
    <div class="col-12">
        <label class="form-label small mb-1">Xat matni <span class="text-danger">*</span></label>
        <textarea name="matn" id="{{ $prefix }}matn"
            class="form-control form-control-sm" rows="8"
            placeholder="Xat matnini kiriting. O'zgaruvchilar: @{{mijoz_fio}}, @{{shartnoma_raqam}}, @{{kechikish_kun}}, @{{jami_qarz}}, @{{yuborish_sana}}, @{{tashkilot_nomi}}"
            required>{{ old('matn', $sh?->matn) }}</textarea>
        <div class="form-text">
            O'zgaruvchilar ikki jingalak qavs ichida yoziladi:
            <code>@{{mijoz_fio}}</code>, <code>@{{shartnoma_raqam}}</code> va hokazo.
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label small mb-1">Qayta yuborish (kun)</label>
        <input type="number" name="qayta_yuborish_kun" id="{{ $prefix }}qayta_yuborish_kun"
            class="form-control form-control-sm"
            value="{{ old('qayta_yuborish_kun', $sh?->qayta_yuborish_kun ?? 30) }}"
            min="0" max="365">
        <div class="form-text">0 = cheklovsiz</div>
    </div>
    <div class="col-md-4">
        <label class="form-label small mb-1">Holat</label>
        <select name="holat" id="{{ $prefix }}holat" class="form-select form-select-sm">
            <option value="faol"   {{ old('holat', $sh?->holat) === 'faol'   ? 'selected' : '' }}>Faol</option>
            <option value="nofaol" {{ old('holat', $sh?->holat) === 'nofaol' ? 'selected' : '' }}>Nofaol</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label small mb-1">Tartib</label>
        <input type="number" name="sort_order" id="{{ $prefix }}sort_order"
            class="form-control form-control-sm"
            value="{{ old('sort_order', $sh?->sort_order ?? 0) }}"
            min="0">
    </div>
</div>
