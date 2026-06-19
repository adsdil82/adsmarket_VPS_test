@extends('layouts.app')
@section('title','GitHub')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
<li class="breadcrumb-item active">GitHub</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-github me-2"></i>GitHub versiya boshqaruvi</h5>
</div>

@if($gitBor)
{{-- Git mavjud --}}
<div class="alert alert-success">
    <i class="bi bi-check-circle me-2"></i>Git repositoriyasi topildi!
</div>

@if($gitLog)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header"><h6 class="mb-0">So'nggi commitlar</h6></div>
    <div class="card-body p-0">
        <pre class="m-0 p-3 bg-dark text-success" style="font-size:12px;border-radius:0 0 8px 8px;overflow-x:auto">@foreach($gitLog as $l){{ $l }}
@endforeach</pre>
    </div>
</div>
@endif

@else
{{-- Git yo'q --}}
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>Git repositoriyasi topilmadi. Quyidagi bo'yruqlarni bajaring:
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-terminal me-2"></i>Git setup buyruqlari</h6></div>
    <div class="card-body p-0">
        <pre class="m-0 p-4 bg-dark text-light" style="font-size:13px;border-radius:0 0 8px 8px">
<span class="text-warning"># 1. Git o'rnatish (agar o'rnatilmagan bo'lsa)</span>
<span class="text-warning"># Windows: https://git-scm.com/download/win ni yuklab o'rnating</span>

<span class="text-warning"># 2. Loyiha papkasiga o'tish</span>
cd "E:\ADS Tuyona Shop\OLD_data\TuyonaALLDB\nasiya-pro"

<span class="text-warning"># 3. Git ni boshlash</span>
git init

<span class="text-warning"># 4. GitHub da yangi repo yaratish (https://github.com/new)</span>
<span class="text-warning"># Keyin remote qo'shish:</span>
git remote add origin https://github.com/SIZNING_USERNAME/nasiya-pro.git

<span class="text-warning"># 5. .gitignore yaratish</span>
git add .gitignore

<span class="text-warning"># 6. Birinchi commit</span>
git add .
git commit -m "feat: NasiyaPro v1.0 - dastlabki commit"

<span class="text-warning"># 7. GitHub ga yuborish</span>
git push -u origin main

<span class="text-warning"># 8. Keyingi o'zgarishlar uchun:</span>
git add .
git commit -m "o'zgarish tavsifi"
git push
</pre>
    </div>
</div>
@endif

{{-- .gitignore --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between">
        <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>.gitignore (maxfiy fayllar)</h6>
    </div>
    <div class="card-body p-0">
        <pre class="m-0 p-3 bg-dark text-secondary" style="font-size:11px;max-height:300px;overflow-y:auto;border-radius:0 0 8px 8px">/node_modules
/vendor
/.env
*.env.local
/storage/app/*
!/storage/app/.gitkeep
/storage/logs/*
!/storage/logs/.gitkeep
/bootstrap/cache/*.php
*.zip
*.sql
/public/hot
docker-compose.override.yml</pre>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-shield-check me-2 text-success"></i>Xavfsizlik eslatmasi</h6></div>
    <div class="card-body">
        <ul class="mb-0 small text-muted">
            <li><strong>.env</strong> faylini hech qachon GitHub ga yubormang (maxfiy kalitlar bor)</li>
            <li><strong>vendor/</strong> papkasini yubormang — <code>composer install</code> bilan tiklanadi</li>
            <li><strong>SQL dump fayllarini</strong> yubormang — foydalanuvchi ma'lumotlari bor</li>
            <li>Production serverde <code>APP_DEBUG=false</code> bo'lsin</li>
        </ul>
    </div>
</div>
@endsection
