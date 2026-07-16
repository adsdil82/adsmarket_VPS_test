<?php

namespace App\View\Composers;

use App\Services\OperatsionKunService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Topbar'dagi operatsion kun ko'rsatkichi (badge) uchun — har sahifada
 * yuklanadi, shuning uchun 60 soniyalik keshga tayanadi.
 */
class OperatsionKunComposer
{
    public function __construct(private OperatsionKunService $svc) {}

    public function compose(View $view): void
    {
        $user = Auth::user();

        if (!$user || !$user->filial_id) {
            $view->with('operatsionKunBadge', null);
            return;
        }

        $kun = cache()->remember("operatsion_kun_{$user->filial_id}", 60, function () use ($user) {
            return $this->svc->joriyKun($user->filial_id);
        });

        $view->with('operatsionKunBadge', $kun);
    }
}
