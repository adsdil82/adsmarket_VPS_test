<?php

use Illuminate\Support\Facades\Schedule;

// ─── NasiyaPro Scheduler ─────────────────────────────────────────
// Har kuni yarim tunda muddati o'tgan to'lovlarni yangilash
Schedule::command('nasiya:muddat-kuzatish')->dailyAt('00:05');

// ─── Qurilmalar Nazorati Scheduler ──────────────────────────────
// Har kuni tongda muddati o'tgan qurilmalarni tekshirish
Schedule::command('devices:check-overdue')->dailyAt('00:10');
// Har kuni muddatsiz qurilmalarni avtomatik ochish
Schedule::command('devices:auto-unlock')->dailyAt('00:20');
// Har soatda provayderlar bilan sinxronlash
Schedule::command('devices:sync-providers')->hourly();
