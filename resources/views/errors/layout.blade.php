<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xato — NasiyaPro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f1f3f5; font-family: system-ui, sans-serif; }
        .xato-card { max-width: 860px; margin: 40px auto; }
        .xato-header { background: #dc3545; color: white; border-radius: 12px 12px 0 0; padding: 20px 24px; }
        .xato-body { background: white; border-radius: 0 0 12px 12px; padding: 24px; }
        .stack-trace { background: #1e1e1e; color: #d4d4d4; border-radius: 8px; padding: 16px;
                       font-family: monospace; font-size: 12px; max-height: 400px; overflow: auto;
                       white-space: pre-wrap; word-break: break-all; }
        .code-line { background: #2d2d2d; padding: 2px 8px; border-radius: 4px; }
        .copy-btn { position: sticky; top: 0; float: right; z-index: 10; }
        .sabab-box { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px 16px; }
        .tip-box { background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px; padding: 12px 16px; }
    </style>
</head>
<body>
@yield('content')
<script>
function nusxaOl(btn) {
    var matn = document.getElementById('xato-matn').innerText;
    var origHtml = btn ? btn.innerHTML : '';
    var origClass = btn ? btn.className : '';
    function ok() {
        if (!btn) return;
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Nusxalandi!';
        btn.className = 'btn btn-success';
        setTimeout(function(){ btn.innerHTML = origHtml; btn.className = origClass; }, 2000);
    }
    function fallback() {
        var ta = document.createElement('textarea');
        ta.value = matn;
        ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        try { document.execCommand('copy'); ok(); }
        catch(e) { alert('Nusxalash imkonsiz.'); }
        document.body.removeChild(ta);
    }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(matn).then(ok).catch(fallback);
    } else {
        fallback();
    }
}
</script>
</body>
</html>
