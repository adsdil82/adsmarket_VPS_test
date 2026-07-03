<?php
namespace App\Services;

use App\Models\TovarKatalog;

/**
 * EAN-13 shtrix-kod generatsiyasi. "20" prefiksi — GS1 standartida
 * ichki (do'kon ichida) foydalanish uchun ajratilgan diapazon (200-299),
 * demak tashqi tovar kodlari bilan hech qachon to'qnashmaydi.
 */
class BarcodeService
{
    private const PREFIX = '20';

    /** Yangi, katalogda hali mavjud bo'lmagan noyob EAN-13 shtrix-kod yaratadi. */
    public function generate(): string
    {
        do {
            $tana = self::PREFIX . str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
            $kod  = $tana . $this->nazoratRaqami($tana);
        } while (TovarKatalog::where('barkod', $kod)->exists());

        return $kod;
    }

    /** EAN-13 nazorat (check) raqamini hisoblaydi — standart GS1 algoritmi. */
    private function nazoratRaqami(string $bazaRaqam): string
    {
        $jami = 0;
        foreach (str_split($bazaRaqam) as $i => $raqam) {
            $jami += (int) $raqam * ($i % 2 === 0 ? 1 : 3);
        }
        $qoldiq = $jami % 10;
        return (string) ($qoldiq === 0 ? 0 : 10 - $qoldiq);
    }

    /** Berilgan EAN-13 kodning nazorat raqami to'g'riligini tekshiradi. */
    public function tekshir(string $kod): bool
    {
        if (strlen($kod) !== 13 || !ctype_digit($kod)) return false;
        return $this->nazoratRaqami(substr($kod, 0, 12)) === substr($kod, 12, 1);
    }
}
