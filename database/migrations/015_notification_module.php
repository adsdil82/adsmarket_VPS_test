<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// 015 -- Xabarnoma (Notification) moduli
// SMS, Telegram, Email, Gibrid Pochta uchun yagona tizim
return new class extends Migration {
    public function up(): void
    {
        // ══════════════════════════════════════════════════════════
        // 1. NOTIFICATION SETTINGS
        // Har kanal uchun API kalitlari va sozlamalar
        // ══════════════════════════════════════════════════════════
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('channel', ['sms','telegram','email','hybrid_mail']);
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->boolean('is_secret')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['channel','key']);
        });

        // Default sozlamalar
        $defaults = [
            ['channel'=>'sms',         'key'=>'provider',     'value'=>'test_mode', 'is_secret'=>false],
            ['channel'=>'sms',         'key'=>'api_url',      'value'=>'',          'is_secret'=>false],
            ['channel'=>'sms',         'key'=>'login',        'value'=>'',          'is_secret'=>false],
            ['channel'=>'sms',         'key'=>'password',     'value'=>'',          'is_secret'=>true],
            ['channel'=>'sms',         'key'=>'sender_id',    'value'=>'NasiyaPro', 'is_secret'=>false],
            ['channel'=>'sms',         'key'=>'test_phone',   'value'=>'',          'is_secret'=>false],
            ['channel'=>'sms',         'key'=>'enabled',      'value'=>'1',         'is_secret'=>false],
            ['channel'=>'sms',         'key'=>'test_mode',    'value'=>'1',         'is_secret'=>false],
            ['channel'=>'telegram',    'key'=>'bot_token',    'value'=>'',          'is_secret'=>true],
            ['channel'=>'telegram',    'key'=>'bot_username', 'value'=>'',          'is_secret'=>false],
            ['channel'=>'telegram',    'key'=>'test_chat_id', 'value'=>'',          'is_secret'=>false],
            ['channel'=>'telegram',    'key'=>'enabled',      'value'=>'0',         'is_secret'=>false],
            ['channel'=>'telegram',    'key'=>'parse_mode',   'value'=>'HTML',      'is_secret'=>false],
            ['channel'=>'email',       'key'=>'mailer',       'value'=>'smtp',      'is_secret'=>false],
            ['channel'=>'email',       'key'=>'host',         'value'=>'',          'is_secret'=>false],
            ['channel'=>'email',       'key'=>'port',         'value'=>'587',       'is_secret'=>false],
            ['channel'=>'email',       'key'=>'username',     'value'=>'',          'is_secret'=>false],
            ['channel'=>'email',       'key'=>'password',     'value'=>'',          'is_secret'=>true],
            ['channel'=>'email',       'key'=>'encryption',   'value'=>'tls',       'is_secret'=>false],
            ['channel'=>'email',       'key'=>'from_address', 'value'=>'',          'is_secret'=>false],
            ['channel'=>'email',       'key'=>'from_name',    'value'=>'NasiyaPro', 'is_secret'=>false],
            ['channel'=>'email',       'key'=>'test_email',   'value'=>'',          'is_secret'=>false],
            ['channel'=>'email',       'key'=>'enabled',      'value'=>'0',         'is_secret'=>false],
            ['channel'=>'hybrid_mail', 'key'=>'api_url',      'value'=>'',          'is_secret'=>false],
            ['channel'=>'hybrid_mail', 'key'=>'login',        'value'=>'',          'is_secret'=>false],
            ['channel'=>'hybrid_mail', 'key'=>'token',        'value'=>'',          'is_secret'=>true],
            ['channel'=>'hybrid_mail', 'key'=>'sender_name',  'value'=>'',          'is_secret'=>false],
            ['channel'=>'hybrid_mail', 'key'=>'test_mode',    'value'=>'1',         'is_secret'=>false],
            ['channel'=>'hybrid_mail', 'key'=>'enabled',      'value'=>'0',         'is_secret'=>false],
        ];
        foreach ($defaults as $d) {
            DB::table('notification_settings')->insert(array_merge($d, [
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        // ══════════════════════════════════════════════════════════
        // 2. NOTIFICATION TEMPLATES
        // ══════════════════════════════════════════════════════════
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('channel', ['sms','telegram','email','hybrid_mail'])->default('sms');
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('subject', 300)->nullable();
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // Default SMS shablonlar
        $templates = [
            [
                'channel'=>'sms', 'code'=>'sms_oldindan_ogohlantirish',
                'name'=>'Oldindan ogohlantirish',
                'body'=>"Hurmatli {client_name}, {payment_date} sanasida {monthly_payment} so'm to'lovingiz bor. Shartnoma: {contract_number}. {company_name}",
                'is_default'=>true,
            ],
            [
                'channel'=>'sms', 'code'=>'sms_kechikkan_tolov',
                'name'=>"Kechikkan to'lov",
                'body'=>"Hurmatli {client_name}, {contract_number} shartnomangiz bo'yicha {overdue_days} kun kechikish mavjud. Qarz: {overdue_amount} so'm. Iltimos to'lovni amalga oshiring.",
                'is_default'=>true,
            ],
            [
                'channel'=>'sms', 'code'=>'sms_umumiy_qarz',
                'name'=>"Umumiy qarzdorlik",
                'body'=>"Hurmatli {client_name}, umumiy qarzdorligingiz {total_debt} so'm. Iltimos, to'lovni amalga oshiring. {company_name}",
                'is_default'=>false,
            ],
            [
                'channel'=>'sms', 'code'=>'sms_tolov_qabul',
                'name'=>"To'lov qabul qilindi",
                'body'=>"Hurmatli {client_name}, {paid_amount} so'm to'lovingiz qabul qilindi. Qoldiq: {remaining_amount} so'm. Rahmat!",
                'is_default'=>false,
            ],
            [
                'channel'=>'sms', 'code'=>'sms_shartnoma_yopildi',
                'name'=>"Shartnoma yopildi",
                'body'=>"Hurmatli {client_name}, {contract_number} shartnomangiz to'liq yopildi. Hamkorligingiz uchun rahmat! {company_name}",
                'is_default'=>false,
            ],
        ];
        foreach ($templates as $t) {
            DB::table('notification_templates')->insert(array_merge($t, [
                'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        // ══════════════════════════════════════════════════════════
        // 3. NOTIFICATION LOGS
        // ══════════════════════════════════════════════════════════
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('channel', ['sms','telegram','email','hybrid_mail']);
            $table->string('recipient_type', 50)->nullable(); // customer, test, manual
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('telegram_chat_id', 100)->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('subject', 300)->nullable();
            $table->text('message');
            $table->enum('status', ['pending','sent','failed','skipped','test'])->default('pending');
            $table->string('provider', 50)->nullable();
            $table->string('provider_message_id', 200)->nullable();
            $table->text('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['channel', 'status', 'created_at']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['contract_id', 'created_at']);
        });

        // ══════════════════════════════════════════════════════════
        // 4. NOTIFICATION BATCHES (Guruhli yuborish)
        // ══════════════════════════════════════════════════════════
        Schema::create('notification_batches', function (Blueprint $table) {
            $table->id();
            $table->enum('channel', ['sms','telegram','email','hybrid_mail'])->default('sms');
            $table->string('type', 50)->default('manual'); // overdue, upcoming, branch, manual, custom
            $table->string('title', 300);
            $table->json('filters_json')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('total_sent')->default(0);
            $table->unsignedInteger('total_failed')->default(0);
            $table->unsignedInteger('total_skipped')->default(0);
            $table->enum('status',['draft','previewed','sending','completed','failed','cancelled'])
                  ->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════════
        // 5. NOTIFICATION BATCH ITEMS
        // ══════════════════════════════════════════════════════════
        Schema::create('notification_batch_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id')->constrained('notification_batches')->cascadeOnDelete();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('message')->nullable();
            $table->enum('status', ['pending','sent','failed','skipped'])->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('notification_log_id')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'status']);
        });

        // ══════════════════════════════════════════════════════════
        // 6. Mijozlar jadvaliga telegram_chat_id qo'shish
        // ══════════════════════════════════════════════════════════
        Schema::table('mijozlar', function (Blueprint $table) {
            $table->string('telegram_chat_id', 100)->nullable()->after('telefon')
                  ->comment('Telegram bot orqali bog\'langan chat ID');
            $table->string('email', 200)->nullable()->after('telegram_chat_id')
                  ->comment('Mijoz email manzili (ixtiyoriy)');
        });
    }

    public function down(): void
    {
        Schema::table('mijozlar', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'email']);
        });
        Schema::dropIfExists('notification_batch_items');
        Schema::dropIfExists('notification_batches');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('notification_settings');
    }
};