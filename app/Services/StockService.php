<?php
namespace App\Services;

use App\Models\Ombor;
use App\Models\OmborQoldiq;
use App\Models\StockLedger;
use App\Models\TovarKatalog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Ko'p-omborli tovar qoldig'i markazi. Har bir ombor o'z ALOHIDA
 * miqdorini saqlaydi (ombor_qoldiqlar), tovar_katalog.qoldiq esa
 * shu qoldiqlarning JAMI (barcha omborlar) keshlangan yig'indisi —
 * eski kodlar (tez ko'rsatish uchun) buzilmasligi uchun saqlanadi.
 *
 * Barcha stock harakatlari shu servis orqali o'tishi kerak — to'g'ridan
 * to'g'ri TovarKatalog::increment/decrement('qoldiq') chaqirmang, chunki
 * bu ombor_qoldiqlar bilan sinxronlikni buzadi.
 */
class StockService
{
    /** Filialning "asosiy" ombori — hozircha har filialda bitta asosiy ombor bor deb faraz qilinadi. */
    public function asosiyOmbor(int $filialId): ?Ombor
    {
        return Ombor::where('filial_id', $filialId)->where('tur', 'asosiy')->faol()->first()
            ?? Ombor::where('filial_id', $filialId)->faol()->first();
    }

    /** Tizimdagi birinchi (bosh kompaniya) asosiy ombor — hozircha bitta filial bo'lgani uchun markaziy qabul nuqtasi. */
    public function markaziyOmbor(): Ombor
    {
        return Ombor::where('tur', 'asosiy')->faol()->orderBy('id')->firstOrFail();
    }

    public function qoldiq(int $omborId, int $tovarId): float
    {
        return (float) (OmborQoldiq::where('ombor_id', $omborId)->where('tovar_id', $tovarId)->value('miqdor') ?? 0);
    }

    /**
     * Omborga tovar kirim qilish (taminotchidan, tuzatishdan va h.k.).
     */
    public function kirim(int $omborId, int $tovarId, float $miqdor, string $manbaTur, ?int $manbaId,
        string $izoh = '', string $harakat = 'kirim', ?float $tanNarx = null): void
    {
        if ($miqdor <= 0) return;

        DB::transaction(function () use ($omborId, $tovarId, $miqdor, $manbaTur, $manbaId, $izoh, $harakat, $tanNarx) {
            $row = OmborQoldiq::firstOrCreate(['ombor_id' => $omborId, 'tovar_id' => $tovarId], ['miqdor' => 0]);
            $oldin = (float) $row->miqdor;
            $row->increment('miqdor', $miqdor);

            $tovar = TovarKatalog::find($tovarId);
            $tovar?->increment('qoldiq', $miqdor);

            $this->ledgerYoz($omborId, $tovarId, $tovar->nomi ?? '', $harakat, $miqdor,
                $oldin, $oldin + $miqdor, $tanNarx ?? $tovar->tan_narx ?? 0, $manbaTur, $manbaId, $izoh);
        });
    }

    /**
     * Ombordan tovar chiqim qilish (sotuv, transfer, taminotchiga qaytarish).
     * Qoldiq yetarli bo'lmasa Exception otiladi.
     */
    public function chiqim(int $omborId, int $tovarId, float $miqdor, string $manbaTur, ?int $manbaId,
        string $izoh = '', string $harakat = 'chiqim'): void
    {
        if ($miqdor <= 0) return;

        DB::transaction(function () use ($omborId, $tovarId, $miqdor, $manbaTur, $manbaId, $izoh, $harakat) {
            $row = OmborQoldiq::where('ombor_id', $omborId)->where('tovar_id', $tovarId)->lockForUpdate()->first();
            $mavjud = $row ? (float) $row->miqdor : 0;

            if ($mavjud < $miqdor) {
                $tovar = TovarKatalog::find($tovarId);
                $ombor = Ombor::find($omborId);
                throw new \Exception(
                    "«{$tovar?->nomi}»: «{$ombor?->nomi}» omborida {$mavjud} {$tovar?->birlik} bor, {$miqdor} so'ralyapti."
                );
            }

            $row->decrement('miqdor', $miqdor);

            $tovar = TovarKatalog::find($tovarId);
            $tovar?->decrement('qoldiq', $miqdor);

            $this->ledgerYoz($omborId, $tovarId, $tovar->nomi ?? '', $harakat, $miqdor,
                $mavjud, $mavjud - $miqdor, $tovar->tan_narx ?? 0, $manbaTur, $manbaId, $izoh);
        });
    }

    /** Ombordagi mavjud qoldiq yetarli-yetarli emasligini oldindan bilish uchun (Exception otmasdan). */
    public function yetarlimi(int $omborId, int $tovarId, float $miqdor): bool
    {
        return $this->qoldiq($omborId, $tovarId) >= $miqdor;
    }

    private function ledgerYoz(int $omborId, int $tovarId, string $tovarNomi, string $harakat,
        float $miqdor, float $oldin, float $keyin, float $tanNarx,
        string $manbaTur, ?int $manbaId, string $izoh): void
    {
        StockLedger::create([
            'ombor_id'     => $omborId,
            'tovar_id'     => $tovarId,
            'tovar_nomi'   => $tovarNomi,
            'harakat'      => $harakat,
            'miqdor'       => $miqdor,
            'qoldiq_oldin' => $oldin,
            'qoldiq_keyin' => $keyin,
            'tan_narx'     => $tanNarx,
            'manba_tur'    => $manbaTur,
            'manba_id'     => $manbaId,
            'xodim_id'     => Auth::id(),
            'izoh'         => $izoh,
        ]);
    }
}
