<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\RegKredit;
use App\Models\Tulov;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin()
            ? ($request->filial_id ? (int)$request->filial_id : null)
            : $user->filial_id;

        $stats           = $this->statistikaOl($filialId);
        $kechikkanlar    = $this->kechikkanlarOl($filialId);
        $bugungiTulovlar = $this->bugungiTulovlarOl($filialId);
        $filiallar       = $user->isAdmin() ? Filial::faol()->get() : collect();

        // Grafiklar uchun
        $oylikChart     = $this->oylikChartOl($filialId);
        $filialMuqoyasa = $user->isAdmin() ? $this->filialMuqoyasaOl() : [];
        $holatlariChart = $this->holatlariOl($filialId);

        // TOP qarzdorlar
        $topQarzdorlar  = $this->topQarzdorlarOl($filialId);

        return view('dashboard.index', compact(
            'stats', 'kechikkanlar', 'bugungiTulovlar',
            'filiallar', 'filialId',
            'oylikChart', 'filialMuqoyasa', 'holatlariChart',
            'topQarzdorlar'
        ));
    }

    /** AJAX: to'liq dashboard JSON (filial tugmasi bosilganda) */
    public function ajaxStatistika(Request $request)
    {
        $user     = Auth::user();
        $filialId = $request->filial_id ? (int)$request->filial_id : null;

        if (!$user->isAdmin() && $filialId !== (int)$user->filial_id) {
            return response()->json(['xato' => 'Ruxsat yoq'], 403);
        }

        return response()->json([
            'stats'           => $this->statistikaOl($filialId),
            'oylikChart'      => $this->oylikChartOl($filialId),
            'holatlariChart'  => $this->holatlariOl($filialId),
            'topQarzdorlar'   => $this->topQarzdorlarOl($filialId),
            'kechikkanlar'    => $this->kechikkanlarOlArray($filialId),
            'bugungiTulovlar' => $this->bugungiTulovlarOlJson($filialId),
        ]);
    }

    // ─── Private: Statistika ────────────────────────────────────

    private function statistikaOl(?int $filialId): array
    {
        return Cache::remember("dash_stats_{$filialId}", 60, function () use ($filialId) {
            $bugun    = today();
            $ertasi   = $bugun->copy()->addDay();
            $oyBoshi  = now()->startOfMonth();
            $hafBoshi = now()->startOfWeek();

            $k = DB::table('reg_kredit')
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->selectRaw("
                    COUNT(*) as jami,
                    SUM(holat='faol') as faol,
                    SUM(holat='yopilgan') as yopilgan,
                    SUM(holat='muddati_otgan') as muddati_otgan,
                    COALESCE(SUM(kredit_summa),0) as jami_kredit,
                    COALESCE(SUM(CASE WHEN holat IN ('faol','muddati_otgan') THEN qoldiq_qarz ELSE 0 END),0) as jami_qoldiq,
                    COALESCE(SUM(tolov_qilingan),0) as jami_tolov,
                    SUM(CASE WHEN DATE(created_at)=CURDATE() THEN 1 ELSE 0 END) as bugun_yangi,
                    SUM(CASE WHEN created_at>=? THEN 1 ELSE 0 END) as oy_yangi
                ", [$oyBoshi])
                ->first();

            $tBase = DB::table('tulovlar as t')->whereNotNull('t.summa');
            if ($filialId) {
                $tBase->join('reg_kredit as rk', 'rk.id', '=', 't.reg_kredit_id')
                      ->where('rk.filial_id', $filialId);
            }
            $t = (clone $tBase)->selectRaw("
                SUM(CASE WHEN t.tolov_sana>=? AND t.tolov_sana<? THEN 1 ELSE 0 END) as b_soni,
                COALESCE(SUM(CASE WHEN t.tolov_sana>=? AND t.tolov_sana<? THEN t.summa ELSE 0 END),0) as b_summa,
                COALESCE(SUM(CASE WHEN t.tolov_sana>=? THEN t.summa ELSE 0 END),0) as oy_summa,
                COALESCE(SUM(CASE WHEN t.tolov_sana>=? THEN t.summa ELSE 0 END),0) as haf_summa
            ", [$bugun, $ertasi, $bugun, $ertasi, $oyBoshi, $hafBoshi])->first();

            $m = DB::table('mijozlar')
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->selectRaw("COUNT(*) as jami, SUM(holat='faol') as faol")
                ->first();

            $samaradorlik = ($k->jami_kredit > 0)
                ? round(($k->jami_tolov / $k->jami_kredit) * 100, 1)
                : 0;

            return [
                'jami_shartnomalar'     => (int)($k->jami ?? 0),
                'faol_shartnomalar'     => (int)($k->faol ?? 0),
                'yopilgan_shartnomalar' => (int)($k->yopilgan ?? 0),
                'muddati_otgan'         => (int)($k->muddati_otgan ?? 0),
                'jami_kredit_summa'     => (float)($k->jami_kredit ?? 0),
                'jami_qoldiq'           => (float)($k->jami_qoldiq ?? 0),
                'jami_tolov_qilingan'   => (float)($k->jami_tolov ?? 0),
                'bugun_tolov_soni'      => (int)($t->b_soni ?? 0),
                'bugun_tolov_summa'     => (float)($t->b_summa ?? 0),
                'oy_tolov_summa'        => (float)($t->oy_summa ?? 0),
                'haf_tolov_summa'       => (float)($t->haf_summa ?? 0),
                'jami_mijozlar'         => (int)($m->jami ?? 0),
                'faol_mijozlar'         => (int)($m->faol ?? 0),
                'bugun_yangi_shartnoma' => (int)($k->bugun_yangi ?? 0),
                'oy_yangi_shartnoma'    => (int)($k->oy_yangi ?? 0),
                'samaradorlik'          => (float)$samaradorlik,
            ];
        });
    }

    /** Oxirgi 6 oy to'lovlar (line chart) */
    private function oylikChartOl(?int $filialId): array
    {
        return Cache::remember("dash_oylik_{$filialId}", 120, function () use ($filialId) {
            // Ma'lumotlar bor oxirgi oyni topamiz
            $lastOy = DB::table('tulovlar as t')
                ->when($filialId, function ($q) use ($filialId) {
                    $q->join('reg_kredit as rk', 'rk.id', '=', 't.reg_kredit_id')
                      ->where('rk.filial_id', $filialId);
                })
                ->max('t.tolov_sana');

            $endDate   = $lastOy ? \Carbon\Carbon::parse($lastOy)->endOfMonth() : now();
            $startDate = $endDate->copy()->subMonths(5)->startOfMonth();

            $rows = DB::table('tulovlar as t')
                ->when($filialId, function ($q) use ($filialId) {
                    $q->join('reg_kredit as rk', 'rk.id', '=', 't.reg_kredit_id')
                      ->where('rk.filial_id', $filialId);
                })
                ->whereBetween('t.tolov_sana', [$startDate, $endDate])
                ->selectRaw("DATE_FORMAT(t.tolov_sana,'%Y-%m') as oy,
                             COALESCE(SUM(t.summa),0) as summa, COUNT(*) as soni")
                ->groupBy('oy')
                ->orderBy('oy')
                ->get();

            $oylar    = [];
            $summalar = [];
            $sonlar   = [];
            foreach ($rows as $r) {
                $ts        = strtotime($r->oy . '-01');
                $oylar[]   = date('M Y', $ts);
                $summalar[] = (float)$r->summa;
                $sonlar[]  = (int)$r->soni;
            }

            return ['oylar' => $oylar, 'summalar' => $summalar, 'sonlar' => $sonlar];
        });
    }

    /** Shartnoma holatlari (donut chart) */
    private function holatlariOl(?int $filialId): array
    {
        return Cache::remember("dash_holat_{$filialId}", 120, function () use ($filialId) {
            $k = DB::table('reg_kredit')
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->selectRaw("
                    SUM(holat='faol') as faol,
                    SUM(holat='yopilgan') as yopilgan,
                    SUM(holat='muddati_otgan') as muddati_otgan
                ")->first();

            return [
                'faol'          => (int)($k->faol ?? 0),
                'yopilgan'      => (int)($k->yopilgan ?? 0),
                'muddati_otgan' => (int)($k->muddati_otgan ?? 0),
            ];
        });
    }

    /** Filiallar muqoyasasi — bar chart (faqat admin) */
    private function filialMuqoyasaOl(): array
    {
        return Cache::remember("dash_filial_muqoyasa", 120, function () {
            $rows = DB::table('reg_kredit as rk')
                ->join('filiallar as f', 'f.id', '=', 'rk.filial_id')
                ->selectRaw("
                    f.id, f.nomi, f.kod,
                    SUM(rk.holat='faol') as faol,
                    SUM(rk.holat='muddati_otgan') as muddati_otgan,
                    COALESCE(SUM(CASE WHEN rk.holat IN ('faol','muddati_otgan') THEN rk.qoldiq_qarz ELSE 0 END),0) as qoldiq,
                    COALESCE(SUM(rk.tolov_qilingan),0) as tolov,
                    COUNT(*) as jami_shartnoma
                ")
                ->where('f.holat', 'faol')
                ->groupBy('f.id', 'f.nomi', 'f.kod')
                ->orderByDesc('faol')
                ->get();

            $nomlar    = [];
            $faollar   = [];
            $muddatlar = [];
            $tolovlar  = [];

            foreach ($rows as $r) {
                $nomlar[]    = $r->kod ?? $r->nomi;
                $faollar[]   = (int)$r->faol;
                $muddatlar[] = (int)$r->muddati_otgan;
                $tolovlar[]  = round($r->tolov / 1_000_000, 1);
            }

            return [
                'nomlar'    => $nomlar,
                'faollar'   => $faollar,
                'muddatlar' => $muddatlar,
                'tolovlar'  => $tolovlar,
            ];
        });
    }

    /** TOP 5 eng katta qarzdorlar */
    private function topQarzdorlarOl(?int $filialId): array
    {
        return Cache::remember("dash_top_qarz_{$filialId}", 120, function () use ($filialId) {
            return DB::table('reg_kredit as rk')
                ->join('mijozlar as m', 'm.id', '=', 'rk.mijoz_id')
                ->when($filialId, fn($q) => $q->where('rk.filial_id', $filialId))
                ->whereIn('rk.holat', ['faol','muddati_otgan'])
                ->where('rk.qoldiq_qarz', '>', 0)
                ->selectRaw("rk.id, rk.shartnoma_raqam, rk.qoldiq_qarz, rk.holat,
                             CONCAT(m.familiya,' ',m.ism) as mijoz_ism")
                ->orderByDesc('rk.qoldiq_qarz')
                ->limit(5)
                ->get()
                ->toArray();
        });
    }

    private function kechikkanlarOl(?int $filialId)
    {
        return Cache::remember("dash_kech_{$filialId}", 300, function () use ($filialId) {
            return RegKredit::with(['mijoz:id,familiya,ism', 'filial:id,kod,nomi'])
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->where('holat', 'muddati_otgan')
                ->orderByDesc('qoldiq_qarz')
                ->limit(10)
                ->get(['id','shartnoma_raqam','mijoz_id','filial_id','qoldiq_qarz','tugash_sana']);
        });
    }

    private function kechikkanlarOlArray(?int $filialId): array
    {
        return $this->kechikkanlarOl($filialId)->map(fn($k) => [
            'id'              => $k->id,
            'shartnoma_raqam' => $k->shartnoma_raqam,
            'mijoz_ism'       => ($k->mijoz->familiya ?? '') . ' ' . ($k->mijoz->ism ?? ''),
            'qoldiq_qarz'     => $k->qoldiq_qarz,
            'tugash_sana'     => $k->tugash_sana,
            'filial_kod'      => $k->filial->kod ?? '',
        ])->toArray();
    }

    private function bugungiTulovlarOl(?int $filialId)
    {
        $bugun  = today();
        $ertasi = $bugun->copy()->addDay();

        $query = Tulov::with([
                'kredit:id,shartnoma_raqam,mijoz_id',
                'kredit.mijoz:id,familiya,ism',
                'tulovTuri:id,nomi',
                'xodim:id,ism_familiya'
            ])
            ->where('tolov_sana', '>=', $bugun)
            ->where('tolov_sana', '<',  $ertasi)
            ->orderByDesc('qabul_vaqt')
            ->limit(15);

        if ($filialId) {
            $query->join('reg_kredit as rk_d', 'rk_d.id', '=', 'tulovlar.reg_kredit_id')
                  ->where('rk_d.filial_id', $filialId)
                  ->select('tulovlar.*');
        }

        return $query->get();
    }

    private function bugungiTulovlarOlJson(?int $filialId): array
    {
        return $this->bugungiTulovlarOl($filialId)->map(fn($t) => [
            'shartnoma_raqam' => $t->kredit->shartnoma_raqam ?? '',
            'mijoz_ism'       => ($t->kredit->mijoz->familiya ?? '') . ' ' . ($t->kredit->mijoz->ism ?? ''),
            'tulov_turi'      => $t->tulovTuri->nomi ?? '',
            'summa'           => $t->summa,
            'kassir'          => $t->xodim->ism_familiya ?? '',
            'kredit_id'       => $t->reg_kredit_id,
        ])->toArray();
    }
}
