<?php
namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Taminotchi;
use App\Models\TaminotKirim;
use App\Models\TaminotKirimQator;
use App\Models\TaminotchiTulov;
use App\Models\TovarKatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaminotchiController extends Controller
{
    // ── Yordamchi ───────────────────────────────────────────────
    private function filialId(): ?int
    {
        $u = Auth::user();
        return $u->isAdmin() ? null : $u->filial_id;
    }

    // ══════════════════════════════════════════════════════════════
    // TA'MINOTCHILAR CRUD
    // ══════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $filialId = $this->filialId();
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();

        $taminotchilar = Taminotchi::query()
            ->when($filialId, fn($q) => $q->filialda($filialId))
            ->when($request->holat, fn($q) => $q->where('holat', $request->holat))
            ->when($request->qidiruv, fn($q) => $q->where(fn($s) =>
                $s->where('nomi','like',"%{$request->qidiruv}%")
                  ->orWhere('telefon','like',"%{$request->qidiruv}%")
            ))
            ->withCount('kirimlar')
            ->withSum('kirimlar','jami_summa')
            ->withSum('tulovlar','summa')
            ->orderBy('nomi')
            ->paginate(30)->withQueryString();

        return view('taminotchi.index', compact('taminotchilar','filiallar','filialId'));
    }

    public function create()
    {
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        return view('taminotchi.create', compact('filiallar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomi'          => 'required|string|max:200',
            'kontakt_shaxs' => 'nullable|string|max:150',
            'telefon'       => 'nullable|string|max:100',
            'telefon2'      => 'nullable|string|max:100',
            'manzil'        => 'nullable|string|max:300',
            'inn'           => 'nullable|string|max:30',
            'bank_hisob'    => 'nullable|string|max:50',
            'bank_nomi'     => 'nullable|string|max:200',
            'mfo'           => 'nullable|string|max:20',
            'izoh'          => 'nullable|string',
            'holat'         => 'in:faol,nofaol',
            'filial_id'     => 'nullable|exists:filiallar,id',
        ]);

        if (!Auth::user()->isAdmin()) {
            $data['filial_id'] = Auth::user()->filial_id;
        }

        $t = Taminotchi::create($data);
        return redirect()->route('taminotchi.show', $t)
            ->with('muvaffaqiyat', "Ta'minotchi «{$t->nomi}» qo'shildi.");
    }

    public function show(Taminotchi $taminotchi, Request $request)
    {
        $taminotchi->load(['filial']);

        $danSana   = $request->dan_sana   ?? now()->subMonths(3)->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $kirimlar = $taminotchi->kirimlar()
            ->with(['xodim:id,ism_familiya','filial:id,kod'])
            ->whereBetween('kirim_sana', [$danSana, $gachaSana])
            ->orderByDesc('kirim_sana')
            ->get();

        $tulovlar = $taminotchi->tulovlar()
            ->with(['xodim:id,ism_familiya'])
            ->whereBetween('tolov_sana', [$danSana, $gachaSana])
            ->orderByDesc('tolov_sana')
            ->get();

        // Balans hisob
        $balans = [
            'jami_kirim' => $taminotchi->kirimlar()->sum('jami_summa'),
            'jami_tolov' => $taminotchi->tulovlar()->sum('summa'),
        ];
        $balans['qoldiq'] = $balans['jami_kirim'] - $balans['jami_tolov'];

        return view('taminotchi.show', compact(
            'taminotchi','kirimlar','tulovlar','balans','danSana','gachaSana'
        ));
    }

    public function edit(Taminotchi $taminotchi)
    {
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        return view('taminotchi.edit', compact('taminotchi','filiallar'));
    }

    public function update(Request $request, Taminotchi $taminotchi)
    {
        $data = $request->validate([
            'nomi'          => 'required|string|max:200',
            'kontakt_shaxs' => 'nullable|string|max:150',
            'telefon'       => 'nullable|string|max:100',
            'telefon2'      => 'nullable|string|max:100',
            'manzil'        => 'nullable|string|max:300',
            'inn'           => 'nullable|string|max:30',
            'bank_hisob'    => 'nullable|string|max:50',
            'bank_nomi'     => 'nullable|string|max:200',
            'mfo'           => 'nullable|string|max:20',
            'izoh'          => 'nullable|string',
            'holat'         => 'in:faol,nofaol',
            'filial_id'     => 'nullable|exists:filiallar,id',
        ]);
        $taminotchi->update($data);
        return redirect()->route('taminotchi.show', $taminotchi)
            ->with('muvaffaqiyat', "Ma'lumotlar yangilandi.");
    }

    // ══════════════════════════════════════════════════════════════
    // KIRIM (YETKAZIB BERISH FAKTURASI)
    // ══════════════════════════════════════════════════════════════

    public function kirimCreate(Taminotchi $taminotchi)
    {
        $tovarlar = TovarKatalog::where('holat','faol')->orderBy('nomi')->get(['id','nomi','sotish_narx','birlik']);
        return view('taminotchi.kirim_create', compact('taminotchi','tovarlar'));
    }

    public function kirimStore(Request $request, Taminotchi $taminotchi)
    {
        $request->validate([
            'hujjat_raqam'   => 'nullable|string|max:50',
            'kirim_sana'     => 'required|date',
            'izoh'           => 'nullable|string',
            'qatorlar'       => 'required|array|min:1',
            'qatorlar.*.nomi'  => 'required|string',
            'qatorlar.*.miqdor'=> 'required|numeric|min:0.001',
            'qatorlar.*.narx'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $taminotchi) {
            // Qatorlardan jami hisoblash
            $jami = 0;
            $qatorlar = collect($request->qatorlar)->map(function ($q) use (&$jami) {
                $q['jami'] = round($q['miqdor'] * $q['narx'], 2);
                $jami += $q['jami'];
                return $q;
            });

            $kirim = TaminotKirim::create([
                'taminotchi_id' => $taminotchi->id,
                'filial_id'     => $taminotchi->filial_id ?? Auth::user()->filial_id,
                'xodim_id'      => Auth::id(),
                'hujjat_raqam'  => $request->hujjat_raqam,
                'kirim_sana'    => $request->kirim_sana,
                'jami_summa'    => $jami,
                'tolangan'      => 0,
                'qoldiq'        => $jami,
                'holat'         => 'kutilmoqda',
                'izoh'          => $request->izoh,
            ]);

            foreach ($qatorlar as $q) {
                TaminotKirimQator::create([
                    'kirim_id'  => $kirim->id,
                    'tovar_id'  => $q['tovar_id'] ?? null,
                    'nomi'      => $q['nomi'],
                    'miqdor'    => $q['miqdor'],
                    'birlik'    => $q['birlik'] ?? 'dona',
                    'narx'      => $q['narx'],
                    'jami'      => $q['jami'],
                ]);
            }
        });

        return redirect()->route('taminotchi.show', $taminotchi)
            ->with('muvaffaqiyat', "Kirim muvaffaqiyatli qayd etildi.");
    }

    // ══════════════════════════════════════════════════════════════
    // TO'LOV KIRITISH
    // ══════════════════════════════════════════════════════════════

    public function tulovStore(Request $request, Taminotchi $taminotchi)
    {
        $request->validate([
            'summa'       => 'required|numeric|min:0.01',
            'tolov_sana'  => 'required|date',
            'tolov_turi'  => 'in:naqd,plastik,bank,offset',
            'valyuta'     => 'in:UZS,USD,EUR,RUB,CNY',
            'kurs'        => 'nullable|numeric|min:1',
            'kirim_id'    => 'nullable|exists:taminot_kirimlar,id',
            'hujjat_raqam'=> 'nullable|string|max:50',
            'izoh'        => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $taminotchi) {
            $valyuta  = $request->valyuta ?? 'UZS';
            $kurs     = (float)($request->kurs ?? 1);
            $summa    = (float)$request->summa;
            $summaUzs = $valyuta === 'UZS' ? $summa : round($summa * $kurs, 2);

            $tulov = TaminotchiTulov::create([
                'taminotchi_id' => $taminotchi->id,
                'kirim_id'      => $request->kirim_id,
                'xodim_id'      => Auth::id(),
                'filial_id'     => $taminotchi->filial_id ?? Auth::user()->filial_id,
                'summa'         => $summa,
                'valyuta'       => $valyuta,
                'kurs'          => $kurs,
                'summa_uzs'     => $summaUzs,
                'tolov_sana'    => $request->tolov_sana,
                'tolov_turi'    => $request->tolov_turi ?? 'naqd',
                'hujjat_raqam'  => $request->hujjat_raqam,
                'izoh'          => $request->izoh,
            ]);

            // Kirimga to'lov ulashsa — uning qoldiqini yangilash
            if ($request->kirim_id) {
                $kirim = TaminotKirim::find($request->kirim_id);
                if ($kirim) {
                    $yangiTolangan = $kirim->tolangan + $request->summa;
                    $yangiQoldiq   = max(0, $kirim->jami_summa - $yangiTolangan);
                    $kirim->update([
                        'tolangan' => $yangiTolangan,
                        'qoldiq'   => $yangiQoldiq,
                        'holat'    => $yangiQoldiq == 0 ? 'toliq' : 'qisman',
                    ]);
                }
            }
        });

        return back()->with('muvaffaqiyat',
            number_format($request->summa, 0, '.', ' ') . " so'm to'lov kiritildi.");
    }

    // ══════════════════════════════════════════════════════════════
    // AKT SVERKA
    // ══════════════════════════════════════════════════════════════

    public function aktSverka(Taminotchi $taminotchi, Request $request)
    {
        $danSana   = $request->dan_sana   ?? now()->startOfYear()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        // Boshlanish qoldig'i (dan_sana gacha)
        $boshlKirim = $taminotchi->kirimlar()->where('kirim_sana','<',$danSana)->sum('jami_summa');
        $boshlTolov = $taminotchi->tulovlar()->where('tolov_sana','<',$danSana)->sum('summa');
        $boshlQoldiq = $boshlKirim - $boshlTolov;

        // Davr harakatlari
        $kirimlar = $taminotchi->kirimlar()
            ->whereBetween('kirim_sana', [$danSana, $gachaSana])
            ->orderBy('kirim_sana')->get();

        $tulovlar = $taminotchi->tulovlar()
            ->whereBetween('tolov_sana', [$danSana, $gachaSana])
            ->with('xodim:id,ism_familiya')
            ->orderBy('tolov_sana')->get();

        // Umumlashtirilgan harakat ro'yxati (xronologik)
        $harakatlar = collect();
        foreach ($kirimlar as $k) {
            $harakatlar->push([
                'sana'    => $k->kirim_sana,
                'tur'     => 'kirim',
                'tavsif'  => "Kirim ({$k->hujjat_raqam})",
                'debet'   => $k->jami_summa,   // biz qarz oldik
                'kredit'  => 0,
                'model'   => $k,
            ]);
        }
        foreach ($tulovlar as $t) {
            $harakatlar->push([
                'sana'    => $t->tolov_sana,
                'tur'     => 'tolov',
                'tavsif'  => "To'lov ({$t->tolov_turi})" . ($t->izoh ? " — {$t->izoh}" : ''),
                'debet'   => 0,
                'kredit'  => $t->summa,         // biz to'ladik
                'model'   => $t,
            ]);
        }
        $harakatlar = $harakatlar->sortBy('sana')->values();

        // Qoldiqni qayta hisoblash
        $joriy = $boshlQoldiq;
        $harakatlar = $harakatlar->map(function ($h) use (&$joriy) {
            $joriy += $h['debet'] - $h['kredit'];
            $h['qoldiq'] = $joriy;
            return $h;
        });

        $yakunQoldiq = $boshlQoldiq + $kirimlar->sum('jami_summa') - $tulovlar->sum('summa');

        return view('taminotchi.akt_sverka', compact(
            'taminotchi','harakatlar','boshlQoldiq','yakunQoldiq',
            'danSana','gachaSana','kirimlar','tulovlar'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // HISOBOT — barcha ta'minotchilar bo'yicha
    // ══════════════════════════════════════════════════════════════

    public function hisobot(Request $request)
    {
        $filialId  = $this->filialId();
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $statistika = DB::table('taminotchilar as t')
            ->when($filialId, fn($q) => $q->where(fn($s) =>
                $s->where('t.filial_id',$filialId)->orWhereNull('t.filial_id')
            ))
            ->when($request->filial_id, fn($q) => $q->where('t.filial_id',$request->filial_id))
            ->selectRaw("
                t.id, t.nomi, t.telefon, t.holat,
                COALESCE(SUM(DISTINCT k.jami_summa),0) as jami_kirim,
                COALESCE(SUM(DISTINCT tv.summa),0)    as jami_tolov,
                COALESCE(SUM(DISTINCT k.jami_summa),0) - COALESCE(SUM(DISTINCT tv.summa),0) as qoldiq,
                COUNT(DISTINCT k.id) as kirim_soni,
                COUNT(DISTINCT tv.id) as tulov_soni
            ")
            ->leftJoin('taminot_kirimlar as k', fn($j) =>
                $j->on('k.taminotchi_id','=','t.id')
                  ->whereBetween('k.kirim_sana', [$danSana, $gachaSana])
            )
            ->leftJoin('taminotchi_tulovlar as tv', fn($j) =>
                $j->on('tv.taminotchi_id','=','t.id')
                  ->whereBetween('tv.tolov_sana', [$danSana, $gachaSana])
            )
            ->groupBy('t.id','t.nomi','t.telefon','t.holat')
            ->orderByDesc('qoldiq')
            ->get();

        $jami = [
            'kirim'  => $statistika->sum('jami_kirim'),
            'tolov'  => $statistika->sum('jami_tolov'),
            'qoldiq' => $statistika->sum('qoldiq'),
            'qarazdor' => $statistika->where('qoldiq','>',0)->count(), // biz qarazdormiz
            'hakdor'   => $statistika->where('qoldiq','<',0)->count(), // ular bizga qarazdor
        ];

        if ($request->format === 'excel') {
            return $this->excelHisobot($statistika, $danSana, $gachaSana);
        }

        return view('taminotchi.hisobot', compact(
            'statistika','jami','filiallar','filialId','danSana','gachaSana'
        ));
    }

    private function excelHisobot($statistika, $dan, $gacha)
    {
        $html  = '<html xmlns:o="urn:schemas-microsoft-com:office:office">';
        $html .= '<head><meta charset="UTF-8"></head><body>';
        $html .= "<h3>Ta'minotchilar hisoboti: $dan — $gacha</h3>";
        $html .= '<table border="1"><thead><tr>';
        foreach (['#','Nomi','Telefon','Kirim','To\'lov','Qoldiq','Holat'] as $h)
            $html .= "<th>$h</th>";
        $html .= '</tr></thead><tbody>';
        foreach ($statistika as $i => $r) {
            $html .= "<tr><td>" . ($i+1) . "</td><td>{$r->nomi}</td><td>{$r->telefon}</td>";
            $html .= "<td style='text-align:right'>" . number_format($r->jami_kirim,0,'.',' ') . "</td>";
            $html .= "<td style='text-align:right'>" . number_format($r->jami_tolov,0,'.',' ') . "</td>";
            $html .= "<td style='text-align:right'>" . number_format($r->qoldiq,0,'.',' ') . "</td>";
            $html .= "<td>" . ($r->qoldiq > 0 ? 'Qarazdor' : ($r->qoldiq < 0 ? 'Hakdor' : 'Teng')) . "</td>";
            $html .= "</tr>";
        }
        $html .= '</tbody></table></body></html>';
        $fn = 'taminotchi_hisobot_' . now()->format('Ymd') . '.xls';
        return response($html,200,['Content-Type'=>'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition'=>"attachment; filename=\"$fn\""]);
    }

    // ── To'lovlar reestri ────────────────────────────────────────
    public function tulovReestr(Request $request)
    {
        $filialId  = $this->filialId();
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $tulovlar = TaminotchiTulov::with(['taminotchi','xodim:id,ism_familiya','filial:id,kod'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->taminotchi_id, fn($q) => $q->where('taminotchi_id', $request->taminotchi_id))
            ->whereBetween('tolov_sana', [$danSana, $gachaSana])
            ->orderByDesc('tolov_sana')
            ->paginate(50)->withQueryString();

        $taminotchilar = Taminotchi::faol()
            ->when($filialId, fn($q) => $q->filialda($filialId))
            ->orderBy('nomi')->get();

        return view('taminotchi.tulov_reestr', compact(
            'tulovlar','taminotchilar','filiallar','filialId','danSana','gachaSana'
        ));
    }
}
