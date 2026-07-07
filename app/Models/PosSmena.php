<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSmena extends Model
{
    protected $table = 'pos_smenalar';

    protected $fillable = [
        'smena_raqami', 'filial_id', 'xodim_id', 'ochilgan_vaqt', 'yopilgan_vaqt',
        'dastlabki_qoldiq', 'hisoblangan_qoldiq', 'yakuniy_qoldiq', 'farq',
        'topshirilgan_summa', 'topshirish_holati', 'qabul_qilgan_id', 'qabul_vaqti',
        'rad_sababi', 'holat', 'izoh',
    ];

    protected $casts = [
        'ochilgan_vaqt'      => 'datetime',
        'yopilgan_vaqt'      => 'datetime',
        'qabul_vaqti'        => 'datetime',
        'dastlabki_qoldiq'   => 'decimal:2',
        'hisoblangan_qoldiq' => 'decimal:2',
        'yakuniy_qoldiq'     => 'decimal:2',
        'farq'               => 'decimal:2',
        'topshirilgan_summa' => 'decimal:2',
    ];

    public function filial(): BelongsTo { return $this->belongsTo(Filial::class); }
    public function xodim(): BelongsTo { return $this->belongsTo(Foydalanuvchi::class, 'xodim_id'); }
    public function qabulQilgan(): BelongsTo { return $this->belongsTo(Foydalanuvchi::class, 'qabul_qilgan_id'); }
    public function sotuvlar(): HasMany { return $this->hasMany(PosSotuv::class, 'smena_id'); }
    public function qaytimlar(): HasMany { return $this->hasMany(PosQaytim::class, 'smena_id'); }

    public function scopeOchiq($q) { return $q->where('holat', 'ochiq'); }

    public static function yangiSmenaRaqami(int $filialId): string
    {
        $bugun = now()->format('Ymd');
        $soni = static::whereDate('created_at', today())->where('filial_id', $filialId)->count();
        return "SM-{$filialId}-{$bugun}-" . str_pad($soni + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Joriy (real vaqtda) naqd qoldiqni hisoblaydi: dastlabki qoldiq + shu
     * smena davomida qilingan naqd savdolar (mijozga qaytarilgan pul ayrilib)
     * − shu smena davomida NAQD qilingan qaytimlar — asosiy kassaga hali
     * topshirilmagan summa ayirilmaydi (bu faqat smena yopilganda
     * "hisoblangan_qoldiq" sifatida qotiriladi).
     */
    public function joriyNaqdQoldiq(): float
    {
        $naqdSofSumma = (float) $this->sotuvlar()
            ->where('holat', 'tugallangan')
            ->selectRaw('COALESCE(SUM(naqd_summa - qayta_pul), 0) as jami')
            ->value('jami');

        $naqdQaytimlar = (float) $this->qaytimlar()
            ->where('holat', 'tugallangan')->where('tolov_turi', 'naqd')
            ->sum('jami_summa');

        return (float) $this->dastlabki_qoldiq + $naqdSofSumma - $naqdQaytimlar;
    }
}
