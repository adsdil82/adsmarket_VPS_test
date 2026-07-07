<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosTerminalLog extends Model
{
    protected $table = 'pos_terminal_loglar';
    public $timestamps = true;

    protected $fillable = ['xodim_id', 'filial_id', 'hodisa', 'ip', 'izoh'];

    public static function yoz(string $hodisa, ?int $xodimId, ?int $filialId, ?string $izoh = null): void
    {
        static::create([
            'xodim_id'  => $xodimId,
            'filial_id' => $filialId,
            'hodisa'    => $hodisa,
            'ip'        => request()->ip(),
            'izoh'      => $izoh,
        ]);
    }
}
