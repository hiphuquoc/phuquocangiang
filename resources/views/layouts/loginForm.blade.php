<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dang nhap Quan tri - {{ config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <style>
        :root { --primary:#0174be; --primary-dark:#015d9b; --danger:#dc2626; --gray-50:#f9fafb; --gray-200:#e5e7eb; --gray-400:#9ca3af; --gray-500:#6b7280; --gray-700:#374151; --gray-900:#111827; --radius:12px; --shadow:0 10px 25px rgba(0,0,0,.15); }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Inter,Segoe UI,Roboto,sans-serif; background:linear-gradient(135deg,#0f172a,#1e293b,#0f172a); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1rem; }
        .card { width:100%; max-width:430px; background:#fff; border-radius:16px; box-shadow:var(--shadow); overflow:hidden; }
        .card_header { padding:1.6rem 1.6rem 1.1rem; border-bottom:1px solid #f1f5f9; }
        .card_title { font-size:1.35rem; font-weight:700; color:var(--gray-900); margin-bottom:.35rem; }
        .card_desc { font-size:.92rem; color:var(--gray-500); }
        .card_body { padding:1.2rem 1.6rem 1.5rem; }
        .form_group { margin-bottom:1rem; }
        .form_group label { display:block; font-size:.88rem; font-weight:600; color:var(--gray-700); margin-bottom:.45rem; }
        .form_group input { width:100%; border:2px solid var(--gray-200); border-radius:var(--radius); padding:.84rem .95rem; font-size:.94rem; outline:none; }
        .form_group input:focus { border-color:var(--primary); }
        .form_help { display:flex; align-items:center; gap:.5rem; margin:.2rem 0 1rem; font-size:.86rem; color:var(--gray-500); }
        .submit_btn { width:100%; border:none; border-radius:var(--radius); background:linear-gradient(135deg,var(--primary),var(--primary-dark)); color:#fff; font-weight:600; padding:.95rem 1rem; cursor:pointer; }
        .submit_btn:disabled { opacity:.7; cursor:not-allowed; }
        .alert { display:none; margin-bottom:.9rem; padding:.75rem .9rem; border-radius:10px; font-size:.9rem; border:1px solid #fecaca; background:#fef2f2; color:#991b1b; }
        .alert.show { display:block; }
        .card_footer { padding:1rem 1.6rem 1.2rem; border-top:1px solid #f8fafc; background:#f8fafc; text-align:center; }
        .card_footer a { color:var(--primary); text-decoration:none; font-size:.86rem; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card_header">
            <div class="card_title">Dang nhap Quan tri</div>
            <div class="card_desc">Vui long dang nhap de tiep tuc thao tac.</div>
        </div>
        <div class="card_body">
            <div id="alertBox" class="alert"></div>
            <form id="loginForm" method="post">
                @csrf
                <div class="form_group">
                    <label for="email">Email / Username</label>
                    <input type="text" id="email" name="email" required autocomplete="username">
                </div>
                <div class="form_group">
                    <label for="password">Mat khau</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <label class="form_help"><input type="checkbox" id="remember" name="remember" value="1">Ghi nho dang nhap</label>
                <button id="submitBtn" class="submit_btn" type="submit">Dang nhap</button>
            </form>
        </div>
        <div class="card_footer"><a href="/">Quay ve trang chu</a></div>
    </div>
    <script>
        const form = document.getElementById('loginForm');
        const alertBox = document.getElementById('alertBox');
        const submitBtn = document.getElementById('submitBtn');
        function showError(msg){ alertBox.textContent = msg; alertBox.classList.add('show'); }
        function hideError(){ alertBox.classList.remove('show'); alertBox.textContent = ''; }
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            hideError();
            submitBtn.disabled = true;
            try {
                const payload = {
                    email: document.getElementById('email').value.trim(),
                    password: document.getElementById('password').value,
                    remember: document.getElementById('remember').checked
                };
                const resp = await fetch('{{ route("admin.loginAdmin") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const raw = await resp.text();
                let data = null;
                try {
                    data = raw ? JSON.parse(raw) : null;
                } catch (parseErr) {
                    showError('Phản hồi máy chủ không hợp lệ (HTTP ' + resp.status + '). Kiểm tra log hoặc cấu hình HTTPS/DB.');
                    return;
                }
                if (data && data.success) { window.location.href = data.redirect_url || '{{ route("admin.booking.list") }}'; return; }
                showError((data && data.message) ? data.message : 'Đăng nhập thất bại.');
            } catch (err) {
                showError('Co loi xay ra, vui long thu lai.');
            } finally {
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
