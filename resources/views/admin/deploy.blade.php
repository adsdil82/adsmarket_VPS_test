@extends('layouts.app')
@section('title','Deploy — Xostga joylash')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
<li class="breadcrumb-item active">Deploy / Xost</li>
@endsection

@push('styles')
<style>
.deploy-card{border-radius:12px;transition:transform .15s,box-shadow .15s}
.deploy-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.12)!important}
.step-badge{width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0}
.cmd-block{background:#1a1d23;color:#9cdcfe;border-radius:8px;padding:10px 14px;font-size:12px;line-height:1.7;overflow-x:auto;position:relative}
.cmd-block .cmt{color:#6a9955}
.cmd-block .val{color:#ce9178}
.copy-btn{position:absolute;top:6px;right:6px;background:#2d3748;border:none;color:#9cdcfe;border-radius:5px;padding:2px 8px;font-size:11px;cursor:pointer;transition:background .2s}
.copy-btn:hover{background:#4a5568}
.copy-btn.copied{background:#276749;color:#9ae6b4}
.chk-item{display:flex;align-items:flex-start;gap:8px;padding:6px 0;border-bottom:1px solid #f5f5f5}
.chk-item:last-child{border-bottom:none}
.status-pill{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500}
.env-input{font-family:monospace;font-size:12px}
</style>
@endpush

@section('content')

{{-- SARLAVHA + TIZIM HOLATI --}}
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h5 class="fw-bold mb-1"><i class="bi bi-cloud-upload me-2 text-primary"></i>Deploy — Xostga joylash</h5>
        <p class="text-muted mb-0 small">Loyihani eksport qilib tashqi hostingga joylashtirish uchun to'liq qo'llanma</p>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="status-pill bg-{{ version_compare(PHP_VERSION,'8.2','>=') ? 'success' : 'danger' }} bg-opacity-15 text-{{ version_compare(PHP_VERSION,'8.2','>=') ? 'success' : 'danger' }}">
            <i class="bi bi-{{ version_compare(PHP_VERSION,'8.2','>=') ? 'check' : 'x' }}-circle-fill"></i>
            PHP {{ PHP_VERSION }}
        </span>
        <span class="status-pill bg-{{ class_exists('ZipArchive') ? 'success' : 'danger' }} bg-opacity-15 text-{{ class_exists('ZipArchive') ? 'success' : 'danger' }}">
            <i class="bi bi-{{ class_exists('ZipArchive') ? 'check' : 'x' }}-circle-fill"></i>
            ZipArchive
        </span>
        <span class="status-pill bg-{{ is_writable(storage_path('app')) ? 'success' : 'danger' }} bg-opacity-15 text-{{ is_writable(storage_path('app')) ? 'success' : 'danger' }}">
            <i class="bi bi-{{ is_writable(storage_path('app')) ? 'check' : 'x' }}-circle-fill"></i>
            Storage
        </span>
        <span class="status-pill bg-{{ !config('app.debug') ? 'success' : 'warning' }} bg-opacity-15 text-{{ !config('app.debug') ? 'success' : 'warning' }}">
            <i class="bi bi-{{ !config('app.debug') ? 'check' : 'exclamation' }}-circle-fill"></i>
            Debug {{ config('app.debug') ? 'ON ⚠' : 'OFF' }}
        </span>
        <span class="status-pill bg-primary bg-opacity-10 text-primary">
            <i class="bi bi-boxes"></i> Laravel {{ app()->version() }}
        </span>
    </div>
</div>

{{-- YUKLAB OLISH --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card deploy-card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-15 p-2 d-flex align-items-center justify-content-center" style="min-width:48px;height:48px">
                        <i class="bi bi-database-down fs-4 text-success"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Ma'lumotlar bazasi <small class="text-muted">(SQL dump)</small></div>
                        <div class="d-flex gap-3 mt-1">
                            <span class="text-success fw-bold small">{{ $dbHajm->hajm_mb ?? 0 }} MB</span>
                            <span class="text-muted small">·</span>
                            <span class="text-primary small">{{ count($jadvallar) }} jadval</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm fw-bold px-3" id="db-zip-btn"
                            onclick="zipBoshla('db', this, '{{ route('admin.deploy.db') }}')">
                        <i class="bi bi-download me-1"></i>DB ZIP
                    </button>
                </div>
                <div class="text-muted mt-2" style="font-size:11px">
                    <i class="bi bi-info-circle me-1"></i>Fayl: <code>nasiyapro_db_[sana].zip</code> — phpMyAdmin orqali import qiling
                </div>
                <div class="progress mt-2 d-none" id="db-zip-progress-wrap" style="height:6px">
                    <div class="progress-bar bg-success" id="db-zip-progress" style="width:0%"></div>
                </div>
                <div class="text-success small mt-1 d-none" id="db-zip-progress-text"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card deploy-card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-15 p-2 d-flex align-items-center justify-content-center" style="min-width:48px;height:48px">
                        <i class="bi bi-file-zip fs-4 text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">Ilova fayllari <small class="text-muted">(vendor siz)</small></div>
                        <div class="d-flex gap-3 mt-1">
                            <span class="text-primary fw-bold small">{{ $loyihaHajm }}</span>
                            <span class="text-muted small">·</span>
                            <span class="text-warning small">~30 soniya</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm fw-bold px-3" id="app-zip-btn"
                            onclick="zipBoshla('app', this, '{{ route('admin.deploy.app') }}')">
                        <i class="bi bi-download me-1"></i>APP ZIP
                    </button>
                </div>
                <div class="text-muted mt-2" style="font-size:11px">
                    <i class="bi bi-info-circle me-1"></i>Ichida: <code>.env.production_NAMUNA</code> va <code>DEPLOY_QOLLANMA.txt</code>
                </div>
                <div class="progress mt-2 d-none" id="app-zip-progress-wrap" style="height:6px">
                    <div class="progress-bar bg-primary" id="app-zip-progress" style="width:0%"></div>
                </div>
                <div class="text-primary small mt-1 d-none" id="app-zip-progress-text"></div>
            </div>
        </div>
    </div>
</div>

{{-- JOYLASHTIRISH BOSQICHLARI --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2 px-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-warning"></i>Joylashtirish bosqichlari</h6>
            <ul class="nav nav-pills gap-1 mb-0">
                <li class="nav-item">
                    <button class="nav-link active py-1 px-3 small fw-bold" data-bs-toggle="pill" data-bs-target="#cpanel-tab">
                        <i class="bi bi-layout-sidebar me-1"></i>cPanel
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link py-1 px-3 small fw-bold" data-bs-toggle="pill" data-bs-target="#vps-tab">
                        <i class="bi bi-terminal me-1"></i>VPS/SSH
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="card-body p-3">
        <div class="tab-content">
            {{-- cPanel tab --}}
            <div class="tab-pane fade show active" id="cpanel-tab">
                <div class="accordion" id="cPanelAcc">

                    {{-- CP1: Hosting tayyorlash --}}
                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-3 rounded" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#cp1">
                                <span class="step-badge bg-primary text-white me-2">1</span>
                                <strong>Hosting va domen tayyorlash</strong>
                                <span class="badge bg-light text-muted ms-2 small">~5 daqiqa</span>
                            </button>
                        </h2>
                        <div id="cp1" class="accordion-collapse collapse" data-bs-parent="#cPanelAcc">
                            <div class="accordion-body py-2 px-3 small">
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>Hosting xarid:</strong> PHP 8.2+, MySQL 5.7+ bor (Reg.uz, SpaceWeb, Beget, Timeweb)</div></div>
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>Domen ulash:</strong> DNS A record → hosting IP. Tarqalish: 1-24 soat</div></div>
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>SSL:</strong> cPanel → SSL/TLS → AutoSSL → Run AutoSSL (bepul Let's Encrypt)</div></div>
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>PHP versiya:</strong> cPanel → Software → PHP Version → 8.2 yoki 8.3</div></div>
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>PHP kengaytmalar (yoqish kerak):</strong> mbstring, pdo_mysql, tokenizer, xml, ctype, bcmath, fileinfo, zip</div></div>
                            </div>
                        </div>
                    </div>

                    {{-- CP2: MySQL --}}
                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-3 rounded" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#cp2">
                                <span class="step-badge bg-success text-white me-2">2</span>
                                <strong>MySQL baza yaratish va import</strong>
                                <span class="badge bg-light text-muted ms-2 small">~3 daqiqa</span>
                            </button>
                        </h2>
                        <div id="cp2" class="accordion-collapse collapse" data-bs-parent="#cPanelAcc">
                            <div class="accordion-body py-2 px-3 small">
                                <p class="mb-2 text-muted">cPanel → Databases → MySQL Databases</p>
                                <div class="chk-item"><i class="bi bi-1-circle text-primary mt-1"></i><div><strong>Create New Database:</strong> masalan <code>cpuser_nasiya</code></div></div>
                                <div class="chk-item"><i class="bi bi-2-circle text-primary mt-1"></i><div><strong>Create Database User:</strong> <code>cpuser_dbuser</code> + kuchli parol (yozib oling!)</div></div>
                                <div class="chk-item"><i class="bi bi-3-circle text-primary mt-1"></i><div><strong>Add User To Database:</strong> foydalanuvchiga <strong>ALL PRIVILEGES</strong> bering</div></div>
                                <div class="chk-item"><i class="bi bi-4-circle text-primary mt-1"></i><div><strong>Import:</strong> phpMyAdmin → yangi DB → Import → <code>nasiyapro_db_[sana].zip</code></div></div>
                                <div class="alert alert-warning py-1 px-2 mt-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i><strong>Katta DB:</strong>
                                    SSH orqali: <code>mysql -u user -p dbname &lt; nasiyapro_db.sql</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CP3: Fayllar --}}
                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-3 rounded" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#cp3">
                                <span class="step-badge bg-info text-white me-2">3</span>
                                <strong>Fayllarni yuklash (File Manager / FTP)</strong>
                                <span class="badge bg-light text-muted ms-2 small">~5 daqiqa</span>
                            </button>
                        </h2>
                        <div id="cp3" class="accordion-collapse collapse" data-bs-parent="#cPanelAcc">
                            <div class="accordion-body py-2 px-3 small">
                                <div class="chk-item"><i class="bi bi-1-circle text-primary mt-1"></i><div>cPanel → File Manager → <code>public_html/</code> → <strong>Upload</strong> → <code>nasiyapro_app_[sana].zip</code></div></div>
                                <div class="chk-item"><i class="bi bi-2-circle text-primary mt-1"></i><div>ZIP ustiga o'ng klik → <strong>Extract</strong> → <code>public_html/</code></div></div>
                                <div class="chk-item"><i class="bi bi-3-circle text-primary mt-1"></i><div>
                                    <strong>public/ → Document Root:</strong><br>
                                    <em>A (tavsiya):</em> cPanel → Domains → domen → Document Root = <code>public_html/public</code><br>
                                    <em>B (muqobil):</em> <code>public/</code> ichini <code>public_html/</code> ga ko'chiring, <code>index.php</code> yo'llarini moslashtiring
                                </div></div>
                                <div class="chk-item"><i class="bi bi-4-circle text-primary mt-1"></i><div><code>.env.production_NAMUNA</code> → nusxalab <code>.env</code> nomi bilan saqlang</div></div>
                                <div class="chk-item"><i class="bi bi-5-circle text-primary mt-1"></i><div>ZIP faylini o'chiring: File Manager → Delete</div></div>
                            </div>
                        </div>
                    </div>

                    {{-- CP4: .env konfigurator --}}
                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-3 rounded" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#cp4">
                                <span class="step-badge bg-warning text-dark me-2">4</span>
                                <strong>.env faylni sozlash — Konfigurator</strong>
                                <span class="badge bg-light text-muted ms-2 small">~3 daqiqa</span>
                            </button>
                        </h2>
                        <div id="cp4" class="accordion-collapse collapse" data-bs-parent="#cPanelAcc">
                            <div class="accordion-body py-2 px-3">
                                <p class="small text-muted mb-3">To'ldiring → "Yaratish" → nusxalang → File Manager'da <code>.env</code> fayliga joylashtiring</p>
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold mb-1">APP_URL (sizning domen)</label>
                                        <input type="text" id="env_url" class="form-control form-control-sm env-input" placeholder="https://sizningdomen.uz" value="https://">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold mb-1">DB_DATABASE</label>
                                        <input type="text" id="env_db" class="form-control form-control-sm env-input" placeholder="cpuser_nasiya">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold mb-1">DB_USERNAME</label>
                                        <input type="text" id="env_user" class="form-control form-control-sm env-input" placeholder="cpuser_dbuser">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold mb-1">DB_PASSWORD</label>
                                        <input type="text" id="env_pass" class="form-control form-control-sm env-input" placeholder="KuchliParol_123!">
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-sm btn-warning fw-bold" onclick="envYarat()">
                                            <i class="bi bi-gear me-1"></i>.env matnini yaratish
                                        </button>
                                    </div>
                                </div>
                                <div id="env_output" class="cmd-block d-none" style="max-height:240px;overflow-y:auto">
                                    <button class="copy-btn" onclick="copyCmd(this,'env_output_text')">Nusxa</button>
                                    <pre id="env_output_text" class="mb-0" style="white-space:pre;color:#c8e6c9"></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CP5: Terminal --}}
                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-3 rounded" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#cp5">
                                <span class="step-badge bg-secondary text-white me-2">5</span>
                                <strong>Terminal / SSH buyruqlari</strong>
                                <span class="badge bg-light text-muted ms-2 small">~3 daqiqa</span>
                            </button>
                        </h2>
                        <div id="cp5" class="accordion-collapse collapse" data-bs-parent="#cPanelAcc">
                            <div class="accordion-body py-2 px-3">
                                <p class="small text-muted mb-2">cPanel → Terminal (yoki SSH: <code>ssh username@host</code>)</p>
                                <div class="cmd-block position-relative">
                                    <button class="copy-btn" onclick="copyCmd(this,'cmd_cp5')">Nusxa</button>
                                    <pre id="cmd_cp5" class="mb-0"><span class="cmt"># 1. Loyiha papkasiga o'tish</span>
cd ~/public_html

<span class="cmt"># 2. Composer paketlari (vendor/ yaratadi)</span>
composer install --no-dev --optimize-autoloader

<span class="cmt"># 3. APP_KEY yaratish (.env ga avtomatik yoziladi)</span>
php artisan key:generate

<span class="cmt"># 4. Storage public havola</span>
php artisan storage:link

<span class="cmt"># 5. Kesh yaratish (tezlik uchun muhim!)</span>
php artisan config:cache
php artisan route:cache
php artisan view:cache

<span class="cmt"># 6. Ruxsatlar</span>
chmod -R 755 storage bootstrap/cache</pre>
                                </div>
                                <div class="alert alert-info py-1 px-2 mt-2 small mb-0">
                                    <i class="bi bi-lightbulb me-1"></i>
                                    <strong>Composer yo'q:</strong>
                                    <code>curl -sS https://getcomposer.org/installer | php</code>
                                    → <code>php composer.phar install --no-dev</code>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CP6: Tekshirish --}}
                    <div class="accordion-item border rounded">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-3 rounded" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#cp6">
                                <span class="step-badge bg-success text-white me-2">✓</span>
                                <strong>Tekshirish va faollashtirish</strong>
                                <span class="badge bg-success bg-opacity-15 text-success ms-2 small">Final</span>
                            </button>
                        </h2>
                        <div id="cp6" class="accordion-collapse collapse" data-bs-parent="#cPanelAcc">
                            <div class="accordion-body py-2 px-3 small">
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>Login sahifasi:</strong> <code>https://sizningdomen.uz</code> — login ko'rinishi kerak</div></div>
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>Admin kirish:</strong> email/parol bilan kiring, bosh sahifa ochilsin</div></div>
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>HTTPS:</strong> 🔒 belgisi bor, HTTP → HTTPS avtomatik o'tsin</div></div>
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>Debug OFF:</strong> Bu sahifada yuqorida "Debug OFF" yashil ko'rinsin</div></div>
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>Log tekshirish:</strong> Xato bo'lsa <code>storage/logs/laravel-[sana].log</code></div></div>
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>CRON (ixtiyoriy):</strong> cPanel → Cron Jobs: <code>* * * * * cd ~/public_html && php artisan schedule:run >> /dev/null 2>&1</code></div></div>
                                <div class="chk-item"><i class="bi bi-check2-square text-success mt-1"></i><div><strong>Admin parol:</strong> Deploy'dan so'ng Profil → parolni o'zgartiring</div></div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /accordion cPanel --}}
            </div>{{-- /cpanel-tab --}}

            {{-- VPS/SSH tab --}}
            <div class="tab-pane fade" id="vps-tab">
                <div class="accordion" id="vpsAcc">

                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-3 rounded" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#vps1">
                                <span class="step-badge bg-primary text-white me-2">1</span>
                                <strong>Server tayyorlash — Ubuntu 22.04</strong>
                            </button>
                        </h2>
                        <div id="vps1" class="accordion-collapse collapse" data-bs-parent="#vpsAcc">
                            <div class="accordion-body py-2 px-3">
                                <div class="cmd-block position-relative">
                                    <button class="copy-btn" onclick="copyCmd(this,'vps_cmd1')">Nusxa</button>
                                    <pre id="vps_cmd1" class="mb-0"><span class="cmt"># PHP 8.3 + kengaytmalar</span>
sudo apt update && sudo apt upgrade -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-bcmath php8.3-zip php8.3-curl php8.3-gd

<span class="cmt"># MySQL 8 + Nginx + Composer</span>
sudo apt install -y mysql-server nginx
sudo mysql_secure_installation
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer</pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-3 rounded" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#vps2">
                                <span class="step-badge bg-success text-white me-2">2</span>
                                <strong>MySQL baza va import</strong>
                            </button>
                        </h2>
                        <div id="vps2" class="accordion-collapse collapse" data-bs-parent="#vpsAcc">
                            <div class="accordion-body py-2 px-3">
                                <div class="cmd-block position-relative">
                                    <button class="copy-btn" onclick="copyCmd(this,'vps_cmd2')">Nusxa</button>
                                    <pre id="vps_cmd2" class="mb-0">sudo mysql -u root
CREATE DATABASE nasiya_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nasiya_user'@'localhost' IDENTIFIED BY 'KuchliParol123!';
GRANT ALL PRIVILEGES ON nasiya_db.* TO 'nasiya_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

<span class="cmt"># SQL dump import</span>
mysql -u nasiya_user -p nasiya_db &lt; nasiyapro_db.sql</pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border rounded mb-2">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-3 rounded" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#vps3">
                                <span class="step-badge bg-info text-white me-2">3</span>
                                <strong>Fayllar + Nginx + SSL (Let's Encrypt)</strong>
                            </button>
                        </h2>
                        <div id="vps3" class="accordion-collapse collapse" data-bs-parent="#vpsAcc">
                            <div class="accordion-body py-2 px-3">
                                <div class="cmd-block mb-2 position-relative">
                                    <button class="copy-btn" onclick="copyCmd(this,'vps_cmd3')">Nusxa</button>
                                    <pre id="vps_cmd3" class="mb-0">sudo mkdir -p /var/www/nasiyapro
cd /var/www/nasiyapro && sudo unzip ~/nasiyapro_app_*.zip
sudo chown -R www-data:www-data /var/www/nasiyapro
sudo chmod -R 755 storage bootstrap/cache
sudo cp .env.production_NAMUNA .env && sudo nano .env
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan storage:link
sudo -u www-data php artisan config:cache && php artisan route:cache && php artisan view:cache</pre>
                                </div>
                                <div class="cmd-block mb-2 position-relative">
                                    <button class="copy-btn" onclick="copyCmd(this,'vps_nginx')">Nginx config</button>
                                    <pre id="vps_nginx" class="mb-0"><span class="cmt"># /etc/nginx/sites-available/nasiyapro</span>
server {
    listen 80;
    server_name sizningdomen.uz;
    root /var/www/nasiyapro/public;
    index index.php;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }
}</pre>
                                </div>
                                <div class="cmd-block position-relative">
                                    <button class="copy-btn" onclick="copyCmd(this,'vps_ssl')">SSL</button>
                                    <pre id="vps_ssl" class="mb-0">sudo ln -s /etc/nginx/sites-available/nasiyapro /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl restart nginx
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d sizningdomen.uz</pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border rounded">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-3 rounded" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#vps4">
                                <span class="step-badge bg-warning text-dark me-2">4</span>
                                <strong>CRON va Queue Worker (ixtiyoriy)</strong>
                            </button>
                        </h2>
                        <div id="vps4" class="accordion-collapse collapse" data-bs-parent="#vpsAcc">
                            <div class="accordion-body py-2 px-3">
                                <div class="cmd-block position-relative">
                                    <button class="copy-btn" onclick="copyCmd(this,'vps_cron')">Nusxa</button>
                                    <pre id="vps_cron" class="mb-0"><span class="cmt"># crontab -e (www-data yoki deploy user uchun)</span>
* * * * * cd /var/www/nasiyapro && php artisan schedule:run >> /dev/null 2>&1</pre>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /accordion VPS --}}
            </div>{{-- /vps-tab --}}

        </div>{{-- /tab-content --}}
    </div>{{-- /card-body --}}
</div>{{-- /card bosqichlar --}}

{{-- XAVFSIZLIK + JADVALLAR --}}
<div class="row g-3">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-2 px-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2 text-danger"></i>Xavfsizlik chek-listi</h6>
            </div>
            <div class="card-body p-3">
                <div class="chk-item small"><i class="bi bi-x-circle text-danger mt-1"></i><div><code>APP_DEBUG=false</code> — production'da majburiy</div></div>
                <div class="chk-item small"><i class="bi bi-x-circle text-danger mt-1"></i><div><code>.env</code> brauzer orqali o'qilmasin</div></div>
                <div class="chk-item small"><i class="bi bi-x-circle text-danger mt-1"></i><div><code>storage/</code> ga tashqi HTTP kirishi bloklangan bo'lsin</div></div>
                <div class="chk-item small"><i class="bi bi-check-circle text-success mt-1"></i><div>HTTPS yoqilgan, HTTP → HTTPS redirect bor</div></div>
                <div class="chk-item small"><i class="bi bi-check-circle text-success mt-1"></i><div>DB parol: 12+ belgi, katta-kichik + raqam + belgi</div></div>
                <div class="chk-item small"><i class="bi bi-check-circle text-success mt-1"></i><div>Deploy'dan so'ng admin parolini almashtiring</div></div>
                <div class="chk-item small"><i class="bi bi-check-circle text-success mt-1"></i><div>Muntazam backup: DB ni haftalik yuklab oling</div></div>
                <div class="chk-item small"><i class="bi bi-check-circle text-success mt-1"></i><div><code>php artisan key:generate</code> yangi server'da ishga tushiring</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bi bi-table me-2"></i>Joriy baza jadvallari</h6>
                <span class="badge bg-primary">{{ count($jadvallar) }} ta · {{ $dbHajm->hajm_mb ?? 0 }} MB</span>
            </div>
            <div class="card-body p-0" style="max-height:260px;overflow-y:auto">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="small px-3">Jadval</th>
                            <th class="text-end small px-3">Qatorlar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jadvallar as $j)
                        <tr>
                            <td class="font-monospace small px-3">{{ $j->nomi }}</td>
                            <td class="text-end small px-3 {{ $j->qatorlar > 1000 ? 'text-warning fw-bold' : 'text-muted' }}">
                                {{ number_format($j->qatorlar, 0, '.', ' ') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/**
 * ZIP yaratish (DB yoki APP) — haqiqiy server progressini polling orqali
 * kuzatib, progress-bar (1-100%) ko'rsatadi. Yuklab olish yashirin iframe
 * orqali boshlanadi (sahifadan chiqib ketmaslik uchun), shu vaqtda
 * /admin/deploy/progress/{turi} dan progress so'raladi.
 */
function zipBoshla(turi, btn, url) {
    if (btn.classList.contains('disabled')) return;

    const orig = btn.innerHTML;
    const wrap = document.getElementById(turi + '-zip-progress-wrap');
    const bar  = document.getElementById(turi + '-zip-progress');
    const text = document.getElementById(turi + '-zip-progress-text');

    btn.classList.add('disabled');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Boshlandi...';
    wrap.classList.remove('d-none');
    text.classList.remove('d-none');
    bar.style.width = '1%';
    text.textContent = '1%';

    // Yuklab olishni yashirin iframe orqali boshlaymiz (sahifa qolaveradi)
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = url;
    document.body.appendChild(iframe);

    const progressUrl = "{{ route('admin.deploy.progress', '___turi___') }}".replace('___turi___', turi);
    let oxirgiFoiz = 1;
    let toxtaganSoni = 0;

    const polling = setInterval(async () => {
        let foiz = oxirgiFoiz;
        try {
            const res = await fetch(progressUrl, { cache: 'no-store' });
            const data = await res.json();
            foiz = data.foiz || 0;
        } catch (e) {
            toxtaganSoni++;
        }

        if (foiz > oxirgiFoiz) { oxirgiFoiz = foiz; toxtaganSoni = 0; }

        bar.style.width = Math.max(1, oxirgiFoiz) + '%';
        text.textContent = Math.max(1, oxirgiFoiz) + '%';

        // Yakunlandi (100%) yoki javob 15 marta ketma-ket kelmasa — to'xtatamiz
        if (oxirgiFoiz >= 100 || toxtaganSoni > 15) {
            clearInterval(polling);
            bar.style.width = '100%';
            text.textContent = 'Yakunlandi ✓';
            btn.innerHTML = orig;
            btn.classList.remove('disabled');
            setTimeout(() => {
                wrap.classList.add('d-none');
                text.classList.add('d-none');
                bar.style.width = '0%';
            }, 2500);
            setTimeout(() => iframe.remove(), 3000);
        }
    }, 500);
}

function copyCmd(btn, id) {
    const el = document.getElementById(id);
    if (!el) return;
    navigator.clipboard.writeText(el.innerText).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✓ Nusxalandi';
        btn.classList.add('copied');
        setTimeout(() => { btn.textContent = orig; btn.classList.remove('copied'); }, 2000);
    });
}

function envYarat() {
    const url  = document.getElementById('env_url').value  || 'https://sizningdomen.uz';
    const db   = document.getElementById('env_db').value   || 'hosting_db';
    const user = document.getElementById('env_user').value || 'db_user';
    const pass = document.getElementById('env_pass').value || 'parol';
    const brand = '{{ addslashes($sozlamalar["brand_nomi"] ?? "NasiyaPro") }}';

    const env = `APP_NAME="${brand}"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=${url}
APP_TIMEZONE=Asia/Tashkent
APP_LOCALE=uz

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=${db}
DB_USERNAME=${user}
DB_PASSWORD=${pass}

CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync

BROADCAST_DRIVER=log
FILESYSTEM_DISK=local`;

    const out = document.getElementById('env_output');
    document.getElementById('env_output_text').textContent = env;
    out.classList.remove('d-none');
    out.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
</script>
@endpush
