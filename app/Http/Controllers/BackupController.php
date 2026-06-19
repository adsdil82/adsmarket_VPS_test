<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use ZipArchive;

class BackupController extends Controller
{
    /** Deploy sahifasi */
    public function deploy()
    {
        $dbHajm = DB::select("SELECT
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as hajm_mb,
            COUNT(*) as jadval_soni
            FROM information_schema.TABLES
            WHERE table_schema = ?", [config('database.connections.mysql.database')])[0] ?? null;

        $jadvallar = DB::select("SELECT table_name as nomi, TABLE_ROWS as qatorlar
            FROM information_schema.TABLES
            WHERE table_schema = ?
            ORDER BY TABLE_ROWS DESC", [config('database.connections.mysql.database')]);

        $loyihaHajm = $this->papkaHajm(base_path(), ['vendor', 'node_modules', '.git', 'storage/logs']);

        $sozlamalar = \App\Models\Sozlama::barchasi();

        return view('admin.deploy', compact('dbHajm', 'jadvallar', 'loyihaHajm', 'sozlamalar'));
    }

    /** Faqat DB → ZIP yuklab olish (PHP PDO orqali, mysqldump siz) */
    public function dbZip()
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $sana    = now()->format('Y-m-d_H-i');
        $sqlFile = storage_path("app/nasiyapro_db_{$sana}.sql");
        $zipFile = storage_path("app/nasiyapro_db_{$sana}.zip");
        $dbName  = config('database.connections.mysql.database');

        // SQL dump PHP orqali yaratish
        $sql = $this->phpDump($dbName);

        if (strlen($sql) < 100) {
            return response()->json(['xato' => 'Dump yaratishda xato.'], 500);
        }

        file_put_contents($sqlFile, $sql);

        // ZIP
        $zip = new ZipArchive();
        $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile($sqlFile, "nasiyapro_db_{$sana}.sql");
        $zip->addFromString('OQING.txt',
            "NasiyaPro ma'lumotlar bazasi\n" .
            "Sana: " . now()->format('d.m.Y H:i') . "\n" .
            "DB: {$dbName}\n\n" .
            "Xostga joylashtirish:\n" .
            "1. phpMyAdmin > Import > bu SQL faylni tanlang\n" .
            "2. Yoki: mysql -u USER -p DBNAME < nasiyapro_db.sql\n"
        );
        $zip->close();

        @unlink($sqlFile);

        return response()->download($zipFile, "nasiyapro_db_{$sana}.zip")
            ->deleteFileAfterSend();
    }

    /** PHP + PDO orqali SQL dump yaratish */
    private function phpDump(string $dbName): string
    {
        $pdo    = DB::connection()->getPdo();
        $output = [];

        $output[] = "-- NasiyaPro Database Dump";
        $output[] = "-- Sana: " . now()->format('Y-m-d H:i:s');
        $output[] = "-- DB: {$dbName}";
        $output[] = "";
        $output[] = "SET FOREIGN_KEY_CHECKS=0;";
        $output[] = "SET NAMES utf8mb4;";
        $output[] = "SET CHARACTER SET utf8mb4;";
        $output[] = "";

        // Barcha jadvallar ro'yxati
        $jadvallar = $pdo->query("SHOW FULL TABLES FROM `{$dbName}` WHERE Table_type = 'BASE TABLE'")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($jadvallar as $jadval) {
            // CREATE TABLE
            $create = $pdo->query("SHOW CREATE TABLE `{$jadval}`")->fetch(\PDO::FETCH_ASSOC);
            $output[] = "-- --------------------------------------------------------";
            $output[] = "-- Jadval: `{$jadval}`";
            $output[] = "-- --------------------------------------------------------";
            $output[] = "";
            $output[] = "DROP TABLE IF EXISTS `{$jadval}`;";
            $output[] = $create['Create Table'] . ";";
            $output[] = "";

            // Ma'lumotlar soni
            $count = $pdo->query("SELECT COUNT(*) FROM `{$jadval}`")->fetchColumn();
            if ($count === 0) {
                $output[] = "-- `{$jadval}` jadvali bo'sh";
                $output[] = "";
                continue;
            }

            // Ustunlar
            $cols = $pdo->query("SHOW COLUMNS FROM `{$jadval}`")->fetchAll(\PDO::FETCH_COLUMN);
            $colList = implode('`, `', $cols);

            // Ma'lumotlarni 500 qatordan yuklaymiz
            $offset = 0;
            $limit  = 500;

            while (true) {
                $rows = $pdo->query("SELECT * FROM `{$jadval}` LIMIT {$limit} OFFSET {$offset}")
                            ->fetchAll(\PDO::FETCH_ASSOC);
                if (empty($rows)) break;

                $values = [];
                foreach ($rows as $row) {
                    $rowVals = array_map(function ($v) use ($pdo) {
                        if ($v === null) return 'NULL';
                        return "'" . str_replace(
                            ["\\", "'", "\n", "\r", "\0"],
                            ["\\\\", "\\'", "\\n", "\\r", "\\0"],
                            $v
                        ) . "'";
                    }, array_values($row));
                    $values[] = '(' . implode(', ', $rowVals) . ')';
                }

                $output[] = "INSERT INTO `{$jadval}` (`{$colList}`) VALUES";
                $chunks = array_chunk($values, 100);
                foreach ($chunks as $i => $chunk) {
                    $isLast = ($i === count($chunks) - 1) && ($offset + $limit >= $count);
                    $output[] = implode(",\n", $chunk) . ($isLast ? ";" : ",");
                }
                $output[] = "";

                $offset += $limit;
                if ($offset >= $count) break;
            }
        }

        $output[] = "";
        $output[] = "SET FOREIGN_KEY_CHECKS=1;";
        $output[] = "-- Dump yakunlandi: " . now()->format('Y-m-d H:i:s');

        return implode("\n", $output);
    }

    /** Faqat APP → ZIP (vendor va storage/logs siz) */
    public function appZip()
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $sana    = now()->format('Y-m-d_H-i');
        $zipFile = storage_path("app/nasiyapro_app_{$sana}.zip");

        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['xato' => 'ZIP fayl yaratib bo\'lmadi.'], 500);
        }

        $base = base_path();

        // Kiritilmaydigan papkalar
        $chiqaril = ['vendor', 'node_modules', '.git', 'storage/logs',
                     'storage/framework/cache', 'storage/framework/sessions',
                     'storage/framework/views', 'bootstrap/cache'];

        // Kiritiluvchi papkalar
        $papkalar = ['app', 'config', 'database', 'resources', 'routes',
                     'public', 'storage/app', 'lang'];

        foreach ($papkalar as $papka) {
            $dir = $base . '/' . $papka;
            if (!is_dir($dir)) continue;

            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iter as $item) {
                $rel = ltrim(str_replace($base, '', $item->getRealPath()), '/\\');

                // Chiqariluvchi papka tekshiruvi
                $skip = false;
                foreach ($chiqaril as $c) {
                    if (str_starts_with($rel, $c)) { $skip = true; break; }
                }
                if ($skip) continue;

                if ($item->isDir()) {
                    $zip->addEmptyDir($rel);
                } else {
                    $zip->addFile($item->getRealPath(), $rel);
                }
            }
        }

        // Root fayllar
        foreach (['composer.json', 'composer.lock', 'artisan', '.env.example',
                  'package.json', 'vite.config.js'] as $f) {
            if (file_exists($base . '/' . $f)) {
                $zip->addFile($base . '/' . $f, $f);
            }
        }

        // .env.production namunasi
        $env = $this->envNamunasi();
        $zip->addFromString('.env.production_NAMUNA', $env);

        // Deploy qo'llanma
        $zip->addFromString("DEPLOY_QOLLANMA.txt", $this->deployQollanma());

        // Muhim bo'sh papkalar uchun .gitkeep
        foreach (['storage/logs', 'storage/framework/cache',
                  'storage/framework/sessions', 'storage/framework/views',
                  'bootstrap/cache'] as $d) {
            $zip->addEmptyDir($d);
            $zip->addFromString($d . '/.gitkeep', '');
        }

        $zip->close();

        return response()->download($zipFile, "nasiyapro_app_{$sana}.zip")
            ->deleteFileAfterSend();
    }

    // ─── Yordamchi ───────────────────────────────────────────────

    private function papkaHajm(string $path, array $ochir = []): string
    {
        $hajm = 0;
        try {
            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iter as $f) {
                if (!$f->isFile()) continue;
                $rel = str_replace($path . DIRECTORY_SEPARATOR, '', $f->getRealPath());
                foreach ($ochir as $o) {
                    if (str_starts_with(str_replace('\\', '/', $rel), $o)) continue 2;
                }
                $hajm += $f->getSize();
            }
        } catch (\Exception $e) {}

        if ($hajm > 1024 * 1024 * 1024) return round($hajm / 1024 / 1024 / 1024, 1) . ' GB';
        if ($hajm > 1024 * 1024) return round($hajm / 1024 / 1024, 1) . ' MB';
        return round($hajm / 1024, 0) . ' KB';
    }

    private function envNamunasi(): string
    {
        $soz = \App\Models\Sozlama::barchasi();
        $brand = $soz['brand_nomi'] ?? 'NasiyaPro';
        return "APP_NAME=\"{$brand}\"
APP_ENV=production
APP_KEY=    # php artisan key:generate bilan yangilang
APP_DEBUG=false
APP_URL=https://SIZNING_DOMEN.uz

LOG_CHANNEL=daily
LOG_LEVEL=error

# Ma'lumotlar bazasi (hosting panel'dan oling)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=HOSTING_DB_NOMI
DB_USERNAME=HOSTING_DB_USER
DB_PASSWORD=HOSTING_DB_PAROL

# Session va Cache (hosting'da Redis bo'lmasa file ishlatish)
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Vaqt zonasi
APP_TIMEZONE=Asia/Tashkent
APP_LOCALE=uz
";
    }

    private function deployQollanma(): string
    {
        return "=== NasiyaPro — Xostga joylash qo'llanmasi ===
Sana: " . now()->format('d.m.Y H:i') . "

── TALAB QILINADIGAN VERSIYALAR ──────────────────────────
• PHP 8.2 yoki 8.3
• MySQL 5.7+ yoki MariaDB 10.4+
• Composer 2.x

── BOSQICHLAR ────────────────────────────────────────────

1. FAYLLARNI YUKLASH
   • Barcha fayllarni hosting'ning public_html/ yoki www/ papkasiga yuklang
   • Yoki alohida papkaga yuklang va public/ ni document root qiling

2. .env FAYLNI SOZLASH
   • .env.production_NAMUNA ni .env ga o'zgartiring
   • DB_DATABASE, DB_USERNAME, DB_PASSWORD ni to'ldiring
   • APP_URL ni o'z domeningizga o'zgartiring
   • php artisan key:generate

3. BAZANI IMPORT QILISH
   • phpMyAdmin > yangi DB yarating
   • Import > nasiyapro_db.zip ichidagi SQL faylni yuklang

4. COMPOSER PAKETLARINI O'RNATISH
   composer install --no-dev --optimize-autoloader

5. ARTISAN BUYRUQLARI
   php artisan migrate (agar kerak bo'lsa)
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan storage:link

6. PAPKA RUXSATLARI
   chmod -R 755 storage
   chmod -R 755 bootstrap/cache

7. NGINX/APACHE SOZLAMASI
   Document root: public/ papkasiga ko'rsating

── XAVFSIZLIK ──────────────────────────────────────────
• APP_DEBUG=false bo'lsin
• .env faylni web orqali o'qib bo'lmasligi kerak
• storage/ papkasiga tashqi kirish bloklangan bo'lsin
";
    }
}
