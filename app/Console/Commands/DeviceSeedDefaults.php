<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeviceSeedDefaults extends Command
{
    protected $signature = 'devices:seed-defaults';
    protected $description = 'Device Control uchun default provayderlar va qoidalarni seed qilish';

    public function handle(): int
    {
        $this->call('db:seed', ['--class' => 'DeviceControlSeeder', '--force' => true]);
        $this->info('Device Control default ma\'lumotlar seed qilindi.');
        return 0;
    }
}
