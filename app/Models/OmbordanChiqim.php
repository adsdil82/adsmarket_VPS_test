<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OmbordanChiqim extends Model
{
    protected $table    = 'ombordan_chiqim';
    protected $fillable = ['filial_id','xodim_id','sana','sabab','umumiy_summa','izoh','holat'];
    protected $casts    = ['sana' => 'date'];

    public function filial()   { return $this->belongsTo(Filial::class, 'filial_id'); }
    public function xodim()    { return $this->belongsTo(Foydalanuvchi::class, 'xodim_id'); }
    public function tafsilot() { return $this->hasMany(ChiqimTafsilot::class, 'chiqim_id'); }

    /**
     * Barcha sabablar — tarixiy va joriy. 'nasiya_sotish' va 'naqd_sotish' shartnoma/POS
     * modullaridan AVTOMATIK yoziladi (RegKreditController/PosController) — qo'lda
     * chiqim yaratishda tanlanmaydi, faqat ro'yxat/filtr va eski yozuvlarni ko'rsatish uchun.
     */
    public static $sabablar = [
        'nasiya_sotish' => 'Nasiya sotish (avtomatik)',
        'naqd_sotish'   => 'Naqd sotish (avtomatik)',
        'bonus'         => "Bonus (aksiya/sovg'a)",
        'yoqolgan'      => "Yo'qolgan",
        'brak'          => 'Brak (nosoz)',
        'boshqa'        => 'Boshqa',
    ];

    /** Qo'lda (manual) chiqim yaratishda tanlash mumkin bo'lgan sabablar. */
    public static $qoldaSabablar = [
        'bonus'    => "Bonus (aksiya/sovg'a)",
        'yoqolgan' => "Yo'qolgan",
        'brak'     => 'Brak (nosoz)',
        'boshqa'   => 'Boshqa',
    ];
}
