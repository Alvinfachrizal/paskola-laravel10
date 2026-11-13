{{-- 
    Komponen Push Notification Prompt
    Tampil jika browser mendukung Web Push & user belum memberikan izin
--}}

@auth
<div id="pwa-push-permission-card" class="card border-0 shadow-sm rounded-4 mb-4 border-start border-primary border-4 d-none" style="display:none;background:#f0f7ff;">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
                    <i class="bi bi-bell-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1 text-dark" style="font-size:0.95rem;">Aktifkan Notifikasi di HP Anda</h6>
                    <p class="text-muted mb-0" style="font-size:0.825rem;">
                        Terima pemberitahuan langsung saat ada pengumuman mendesak, jadwal ujian, atau tugas sekolah.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 justify-content-end">
                <button type="button" class="btn btn-sm btn-light border px-3 py-2 text-muted" id="btn-push-later" style="font-size:0.8rem;">
                    Nanti Saja
                </button>
                <button type="button" class="btn btn-sm btn-primary px-3 py-2 fw-semibold" id="btn-push-enable" style="font-size:0.8rem;">
                    <i class="bi bi-bell me-1"></i> Aktifkan Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    const pushCard    = document.getElementById('pwa-push-permission-card');
    const btnEnable   = document.getElementById('btn-push-enable');
    const btnLater    = document.getElementById('btn-push-later');
    const vapidKey    = '{{ config("webpush.vapid.public_key") }}';

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
    }

    async function checkPushStatus() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
        if (!('Notification' in window)) return;

        // Jika sudah ditolak permanen, jangan ganggu user
        if (Notification.permission === 'denied') return;

        // Cek localStorage dismissal
        const dismissed = localStorage.getItem('push_card_dismissed');
        const days = (Date.now() - parseInt(dismissed || '0', 10)) / (1000 * 60 * 60 * 24);
        if (days < 3) return;

        try {
            const reg = await navigator.serviceWorker.ready;
            const existingSub = await reg.pushManager.getSubscription();

            // Jika belum ada subscription aktif dan permission belum granted
            if (!existingSub && Notification.permission !== 'granted') {
                if (pushCard) {
                    pushCard.classList.remove('d-none');
                    pushCard.style.display = 'block';
                }
            }
        } catch (e) {
            console.warn('Push status check failed:', e);
        }
    }

    if (btnEnable) {
        btnEnable.addEventListener('click', async () => {
            btnEnable.disabled = true;
            btnEnable.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengaktifkan...';

            try {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    alert('Izin notifikasi tidak diberikan. Anda dapat mengaktifkannya melalui pengaturan browser.');
                    if (pushCard) pushCard.style.display = 'none';
                    return;
                }

                const reg = await navigator.serviceWorker.ready;
                const sub = await reg.pushManager.subscribe({
                    userVisibleOnly:      true,
                    applicationServerKey: urlBase64ToUint8Array(vapidKey),
                });

                const key   = sub.getKey('p256dh');
                const token = sub.getKey('auth');

                const res = await fetch('{{ route("push.subscribe") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        endpoint:   sub.endpoint,
                        public_key: btoa(String.fromCharCode(...new Uint8Array(key))),
                        auth_token: btoa(String.fromCharCode(...new Uint8Array(token))),
                    }),
                });

                if (res.ok) {
                    if (pushCard) pushCard.style.display = 'none';
                    // Tampilkan toast konfirmasi
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-success position-fixed bottom-0 end-0 m-4 shadow rounded-4';
                    toast.style.cssText = 'z-index:9999;font-size:0.875rem;';
                    toast.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Notifikasi HP berhasil diaktifkan!';
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 4000);
                }
            } catch (err) {
                console.error('Subscription error:', err);
                alert('Gagal mengaktifkan notifikasi: ' + err.message);
            } finally {
                btnEnable.disabled = false;
                btnEnable.innerHTML = '<i class="bi bi-bell me-1"></i> Aktifkan Sekarang';
            }
        });
    }

    if (btnLater) {
        btnLater.addEventListener('click', () => {
            localStorage.setItem('push_card_dismissed', Date.now().toString());
            if (pushCard) pushCard.style.display = 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', checkPushStatus);
})();
</script>
@endauth

