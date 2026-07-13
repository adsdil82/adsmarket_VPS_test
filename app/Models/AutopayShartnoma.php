<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AutopayShartnoma extends Model
{
    protected $table    = 'autopay_shartnomalar';
    protected $fillable = ['reg_kredit_id', 'mijoz_id', 'pinfl', 'manba', 'loan_id', 'holat', 'auto_yoqilgan', 'oxirgi_debt', 'xato_matni', 'yuborgan_id', 'yuborilgan_vaqt'];
    protected $casts    = ['auto_yoqilgan' => 'boolean', 'yuborilgan_vaqt' => 'datetime'];

    public function kredit()      { return $this->belongsTo(RegKredit::class, 'reg_kredit_id'); }
    public function mijoz()       { return $this->belongsTo(Mijoz::class, 'mijoz_id'); }
    public function yuborgan()    { return $this->belongsTo(Foydalanuvchi::class, 'yuborgan_id'); }
    public function tranzaksiyalar() { return $this->hasMany(AutopayTranzaksiya::class, 'autopay_shartnoma_id'); }

    public function scopeFaol($q) { return $q->where('holat', 'faol'); }

    /** Qo'lda (bizning tizim yaratmagan) va hali bizning kredit tizimimizga biriktirilmagan kontrakt. */
    public function biriktirilmaganmi(): bool
    {
        return $this->manba === 'qolda' && !$this->reg_kredit_id;
    }
}
