{{-- 
    Komponen PWA Install Prompt & Push Notification Banner
    Mendukung Android (A2HS beforeinstallprompt) & iOS Safari (Share to Home Screen)
--}}

<div id="pwa-install-banner" class="pwa-prompt-container d-none" style="display:none;">
    <div class="pwa-prompt-card shadow-lg">
        <div class="d-flex align-items-center gap-3">
            <img src="/icons/icon-192.png" alt="Paskola" class="pwa-app-icon rounded-3">
            <div class="flex-grow-1">
                <div class="fw-bold text-dark pwa-title" style="font-size:0.95rem;line-height:1.2;">
                    Pasang Aplikasi Paskola
                </div>
                <div class="text-muted pwa-desc" style="font-size:0.775rem;">
                    Akses instan dari layar utama HP & terima notifikasi sekolah.
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-light border text-muted px-2 py-1" id="btn-pwa-dismiss" style="font-size:0.75rem;">
                    Nanti
                </button>
                <button type="button" class="btn btn-sm btn-primary px-3 py-1 fw-medium" id="btn-pwa-install" style="font-size:0.8rem;">
                    <i class="bi bi-download me-1"></i> Pasang
                </button>
            </div>
        </div>
    </div>
</div>

{{-- iOS Safari Guidance Modal / Bottom Sheet --}}
<div id="pwa-ios-banner" class="pwa-prompt-container d-none" style="display:none;">
    <div class="pwa-prompt-card shadow-lg border-primary-subtle">
        <div class="d-flex align-items-start gap-3">
            <img src="/icons/icon-192.png" alt="Paskola" class="pwa-app-icon rounded-3">
            <div class="flex-grow-1">
                <div class="fw-bold text-dark" style="font-size:0.9rem;">
                    Pasang Paskola di iPhone
                </div>
                <div class="text-muted mt-1" style="font-size:0.775rem;line-height:1.4;">
                    Tekan tombol <i class="bi bi-box-arrow-up text-primary fw-bold mx-1"></i> <strong>Bagikan (Share)</strong> di bar bawah Safari, lalu pilih <span class="badge bg-light text-dark border">Tambahkan ke Layar Utama</span>.
                </div>
            </div>
            <button type="button" class="btn-close ms-auto" id="btn-pwa-ios-dismiss" aria-label="Close" style="font-size:0.7rem;"></button>
        </div>
    </div>
</div>

{{-- Offline Status Toast Indicator --}}
<div id="pwa-offline-pill" class="pwa-offline-status d-none" style="display:none;">
    <div class="badge bg-dark text-white px-3 py-2 shadow-sm rounded-pill d-flex align-items-center gap-2">
        <span class="pwa-pulse-dot"></span>
        <span id="pwa-offline-text" style="font-size:0.75rem;">Koneksi terputus (Offline)</span>
    </div>
</div>

<style>
    .pwa-prompt-container {
        position: fixed;
        bottom: 85px; /* di atas bottom navigation mobile */
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 2rem);
        max-width: 480px;
        z-index: 1050;
        animation: pwaSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @media (min-width: 992px) {
        .pwa-prompt-container {
            bottom: 24px;
            right: 24px;
            left: auto;
            transform: none;
        }
    }
    .pwa-prompt-card {
        background: #ffffff;
        border: 1px solid rgba(13, 110, 253, 0.2);
        border-radius: 14px;
        padding: 0.875rem 1rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    }
    .pwa-app-icon {
        width: 44px;
        height: 44px;
        object-fit: cover;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .pwa-offline-status {
        position: fixed;
        top: 16px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1060;
        animation: pwaFadeIn 0.3s ease;
    }
    .pwa-pulse-dot {
        width: 8px;
        height: 8px;
        background-color: #ef4444;
        border-radius: 50%;
        display: inline-block;
        animation: pwaPulse 1.5s infinite;
    }
    @keyframes pwaSlideUp {
        from { transform: translate(-50%, 40px); opacity: 0; }
        to { transform: translate(-50%, 0); opacity: 1; }
    }
    @media (min-width: 992px) {
        @keyframes pwaSlideUp {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    }
    @keyframes pwaPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(1.3); }
    }
</style>

<script>
(function() {
    'use strict';

    let deferredPrompt = null;
    const installBanner = document.getElementById('pwa-install-banner');
    const iosBanner     = document.getElementById('pwa-ios-banner');
    const btnInstall    = document.getElementById('btn-pwa-install');
    const btnDismiss    = document.getElementById('btn-pwa-dismiss');
    const btnIosDismiss = document.getElementById('btn-pwa-ios-dismiss');

    // ─── 1. Deteksi Mode Standalone (Sudah Terpasang) ─────────────────────────
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                         (window.navigator.standalone === true);

    if (isStandalone) {
        // Sudah terpasang sebagai PWA, jangan tampilkan prompt install
        return;
    }

    // ─── 2. Handle Android & Chromium A2HS (beforeinstallprompt) ─────────────
    const isDismissed = localStorage.getItem('pwa_install_dismissed');
    const dismissedTime = isDismissed ? parseInt(isDismissed, 10) : 0;
    const daysSinceDismiss = (Date.now() - dismissedTime) / (1000 * 60 * 60 * 24);

    window.addEventListener('beforeinstallprompt', (e) => {
        // Cegah mini-infobar default browser
        e.preventDefault();
        deferredPrompt = e;

        // Hanya tampilkan jika belum ditolak dalam 7 hari terakhir
        if (daysSinceDismiss >= 7 || isNaN(daysSinceDismiss)) {
            if (installBanner) {
                installBanner.classList.remove('d-none');
                installBanner.style.display = 'block';
            }
        }
    });

    if (btnInstall) {
        btnInstall.addEventListener('click', async () => {
            if (!deferredPrompt) return;

            installBanner.classList.add('d-none');
            installBanner.style.display = 'none';

            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log('PWA user choice:', outcome);
            deferredPrompt = null;
        });
    }

    if (btnDismiss) {
        btnDismiss.addEventListener('click', () => {
            localStorage.setItem('pwa_install_dismissed', Date.now().toString());
            installBanner.classList.add('d-none');
            installBanner.style.display = 'none';
        });
    }

    window.addEventListener('appinstalled', () => {
        if (installBanner) {
            installBanner.classList.add('d-none');
            installBanner.style.display = 'none';
        }
        deferredPrompt = null;
        console.log('PWA Paskola sukses terpasang!');
    });

    // ─── 3. Deteksi iOS Safari ────────────────────────────────────────────────
    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isSafari = /Safari/.test(navigator.userAgent) && !/CriOS|FxiOS|OPiOS|mercury/i.test(navigator.userAgent);

    if (isIos && isSafari && !isStandalone) {
        const iosDismissed = localStorage.getItem('pwa_ios_dismissed');
        const iosDays = (Date.now() - parseInt(iosDismissed || '0', 10)) / (1000 * 60 * 60 * 24);

        if (iosDays >= 7 || isNaN(iosDays)) {
            setTimeout(() => {
                if (iosBanner) {
                    iosBanner.classList.remove('d-none');
                    iosBanner.style.display = 'block';
                }
            }, 3000); // Muncul setelah 3 detik
        }
    }

    if (btnIosDismiss) {
        btnIosDismiss.addEventListener('click', () => {
            localStorage.setItem('pwa_ios_dismissed', Date.now().toString());
            iosBanner.classList.add('d-none');
            iosBanner.style.display = 'none';
        });
    }

    // ─── 4. Indikator Status Online / Offline ─────────────────────────────────
    const offlinePill = document.getElementById('pwa-offline-pill');
    const offlineText = document.getElementById('pwa-offline-text');

    function updateOnlineStatus() {
        if (!navigator.onLine) {
            if (offlinePill) {
                offlinePill.classList.remove('d-none');
                offlinePill.style.display = 'block';
                offlineText.textContent = 'Koneksi terputus (Offline)';
            }
        } else {
            if (offlinePill && offlinePill.style.display !== 'none') {
                offlineText.textContent = 'Kembali online ✅';
                setTimeout(() => {
                    offlinePill.classList.add('d-none');
                    offlinePill.style.display = 'none';
                }, 2500);
            }
        }
    }

    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
})();
</script>

