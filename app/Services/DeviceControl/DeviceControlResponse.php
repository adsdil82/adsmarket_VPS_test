<?php

namespace App\Services\DeviceControl;

class DeviceControlResponse
{
    public function __construct(
        public readonly bool   $muvaffaqiyat,
        public readonly string $xabar       = '',
        public readonly array  $ma_lumotlar  = [],
        public readonly string $xato_kodi   = '',
        public readonly bool   $qayta_urinish = false,
    ) {}

    public static function ok(string $xabar = 'Muvaffaqiyatli', array $ma_lumotlar = []): self
    {
        return new self(true, $xabar, $ma_lumotlar);
    }

    public static function xato(string $xabar, string $xato_kodi = '', bool $qayta_urinish = false): self
    {
        return new self(false, $xabar, [], $xato_kodi, $qayta_urinish);
    }

    public function toArray(): array
    {
        return [
            'muvaffaqiyat'  => $this->muvaffaqiyat,
            'xabar'         => $this->xabar,
            'ma_lumotlar'   => $this->ma_lumotlar,
            'xato_kodi'     => $this->xato_kodi,
            'qayta_urinish' => $this->qayta_urinish,
        ];
    }
}
