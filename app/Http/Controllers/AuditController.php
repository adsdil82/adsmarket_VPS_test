<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    // Maydon nomlarini o'zbek tilida ko'rsatish
    private static array $maydonlar = [
        // Kredit
        'shartnoma_raqam'    => 'Shartnoma raqami',
        'kredit_summa'       => 'Kredit summa',
        'jami_summa'         => 'Jami summa',
        'boshlangich_tolov'  => "Boshlangich to'lov",
        'tolov_qilingan'     => "To'lov qilingan",
        'qoldiq_qarz'        => 'Qoldiq qarz',
        'boshlanish_sana'    => 'Boshlanish sanasi',
        'tugash_sana'        => 'Tugash sanasi',
        'oylik_tolov_miqdori'=> "Oylik to'lov",
        'muddati_oy'         => 'Muddat (oy)',
        'foiz_stavka'        => 'Foiz stavka',
        'holat'              => 'Holat',
        'izoh'               => 'Izoh',
        'mijoz_id'           => 'Mijoz',
        'filial_id'          => 'Filial',
        'xodim_id'           => 'Xodim',
        // To'lov
        'summa'              => 'Summa',
        'tolov_sana'         => "To'lov sanasi",
        'tulov_turi_id'      => "To'lov turi",
        'kvitansiya_raqam'   => 'Kvitansiya raqami',
        'reg_kredit_id'      => 'Shartnoma',
        // Mijoz
        'familiya'           => 'Familiya',
        'ism'                => 'Ism',
        'otasining_ismi'     => "Otasining ismi",
        'telefon'            => 'Telefon',
        'manzil'             => 'Manzil',
        'passport_raqam'     => 'Pasport raqami',
        'pinfl'              => 'PINFL',
        'tug_sana'           => "Tug'ilgan sana",
        // Umumiy
        'password'           => 'Parol (o\'zgartirildi)',
        'login'              => 'Login',
        'email'              => 'Email',
        'rol'                => 'Rol',
        'til'                => 'Til',
        'created_at'         => 'Yaratilgan',
        'updated_at'         => 'Yangilangan',
        'eski_id'            => 'Eski ID',
    ];

    // Model nomlarini o'zbek tilida
    private static array $modellar_nomi = [
        'RegKredit'       => 'Shartnoma',
        'Tulov'           => "To'lov",
        'OldinTulov'      => "Oldindan to'lov",
        'Mijoz'           => 'Mijoz',
        'Foydalanuvchi'   => 'Foydalanuvchi',
        'Filial'          => 'Filial',
        'Sozlama'         => 'Sozlama',
        'TovarKatalog'    => 'Tovar katalog',
        'Kirim'           => 'Kirim',
        'Chiqim'          => 'Chiqim',
    ];

    public function index(Request $request)
    {
        $query = DB::table('audits as a')
            ->leftJoin('foydalanuvchilar as u', 'u.id', '=', 'a.user_id')
            ->select('a.*', 'u.ism_familiya', 'u.login', 'u.rol')
            ->when($request->user_id, fn($q) => $q->where('a.user_id', $request->user_id))
            ->when($request->event,   fn($q) => $q->where('a.event', $request->event))
            ->when($request->model,   fn($q) => $q->where('a.auditable_type', 'like', "%{$request->model}%"))
            ->when($request->dan_sana,  fn($q) => $q->whereDate('a.created_at', '>=', $request->dan_sana))
            ->when($request->gacha_sana,fn($q) => $q->whereDate('a.created_at', '<=', $request->gacha_sana))
            ->orderByDesc('a.created_at');

        $auditlar         = $query->paginate(25)->withQueryString();
        $foydalanuvchilar = DB::table('foydalanuvchilar')->select('id','ism_familiya','login')->get();
        $eventlar         = ['created','updated','deleted'];
        $modellarList     = array_keys(self::$modellar_nomi);

        // Related entity nomlarini olish (shartnoma raqami, mijoz ismi va h.k.)
        $this->entityMalumotlariQosh($auditlar);

        return view('admin.audit', compact(
            'auditlar','foydalanuvchilar','eventlar','modellarList'
        ))->with('maydonlar', self::$maydonlar)
          ->with('modellar_nomi', self::$modellar_nomi);
    }

    /** Audit yozuvlariga entity ma'lumotlarini qo'shamiz */
    private function entityMalumotlariQosh($auditlar): void
    {
        // Barcha ID larni yig'amiz
        $kreditIds = $mijozIds = $filialIds = [];

        foreach ($auditlar as $a) {
            $model = class_basename($a->auditable_type ?? '');
            $newV  = json_decode($a->new_values ?? '{}', true) ?? [];
            $oldV  = json_decode($a->old_values ?? '{}', true) ?? [];
            $all   = array_merge($newV, $oldV);

            if (in_array($model, ['Tulov','OldinTulov'])) {
                $id = $all['reg_kredit_id'] ?? null;
                if ($id) $kreditIds[] = $id;
            }
            if (isset($all['mijoz_id'])) $mijozIds[] = $all['mijoz_id'];
            if (isset($all['filial_id'])) $filialIds[] = $all['filial_id'];
            if ($model === 'RegKredit') $kreditIds[] = $a->auditable_id;
            if ($model === 'Mijoz')     $mijozIds[]  = $a->auditable_id;
        }

        // Bir so'rovda olish
        $kreditlar = DB::table('reg_kredit')
            ->whereIn('id', array_unique($kreditIds))
            ->pluck('shartnoma_raqam', 'id');

        $mijozlar = DB::table('mijozlar')
            ->whereIn('id', array_unique($mijozIds))
            ->selectRaw("id, CONCAT(familiya,' ',ism) as ism_familiya")
            ->pluck('ism_familiya', 'id');

        $filiallar = DB::table('filiallar')
            ->whereIn('id', array_unique($filialIds))
            ->pluck('nomi', 'id');

        // Har bir audit ga context yozamiz
        foreach ($auditlar as $a) {
            $model = class_basename($a->auditable_type ?? '');
            $newV  = json_decode($a->new_values ?? '{}', true) ?? [];
            $oldV  = json_decode($a->old_values ?? '{}', true) ?? [];

            $ctx = '';
            if ($model === 'RegKredit') {
                $ctx = $kreditlar[$a->auditable_id] ?? "Shartnoma #$a->auditable_id";
            } elseif ($model === 'Tulov' || $model === 'OldinTulov') {
                $kId = ($newV['reg_kredit_id'] ?? $oldV['reg_kredit_id'] ?? null);
                $ctx = $kreditlar[$kId] ?? "To'lov #$a->auditable_id";
            } elseif ($model === 'Mijoz') {
                $ctx = $mijozlar[$a->auditable_id] ?? "Mijoz #$a->auditable_id";
            } elseif ($model === 'Foydalanuvchi') {
                $ctx = "Foydalanuvchi #$a->auditable_id";
            }

            // ID larni ismga almashtirish
            foreach (['mijoz_id', 'filial_id'] as $fld) {
                if (isset($newV[$fld])) $newV["_{$fld}_nom"] = $fld === 'mijoz_id'
                    ? ($mijozlar[$newV[$fld]] ?? null)
                    : ($filiallar[$newV[$fld]] ?? null);
                if (isset($oldV[$fld])) $oldV["_{$fld}_nom"] = $fld === 'mijoz_id'
                    ? ($mijozlar[$oldV[$fld]] ?? null)
                    : ($filiallar[$oldV[$fld]] ?? null);
            }

            $a->_context  = $ctx;
            $a->_new_vals = $newV;
            $a->_old_vals = $oldV;
        }
    }
}
