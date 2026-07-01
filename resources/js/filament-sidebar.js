/**
 * POS Cafe — Filament Sidebar Smart Manager
 *
 * Fitur:
 *  1. Auto-close saat navigasi (Livewire navigated) jika unpinned.
 *  2. Tombol Pin (📌) di header sidebar — menjaga sidebar tetap terbuka.
 *  3. State pin disimpan di localStorage.
 *  4. Integrasi dengan Alpine.js store 'sidebar' bawaan Filament.
 *  5. Tombol Tab / Ribbon (🔖) di samping sidebar untuk toggle.
 *  6. Keyboard: [ = toggle sidebar.
 */

(function () {
    'use strict';

    const PIN_KEY = 'pos_sb_pinned';
    let pinned = localStorage.getItem(PIN_KEY) === 'true';
    let sidebarObserver = null;

    /** Cek apakah sidebar sedang terbuka (mendukung Alpine store & class fallback) */
    function sidebarIsOpen() {
        if (window.Alpine && Alpine.store && Alpine.store('sidebar')) {
            return Alpine.store('sidebar').isOpen;
        }
        return document.body.classList.contains('fi-sidebar-open') ||
            document.body.classList.contains('pos-sidebar-open');
    }

    /** Sinkronisasikan class body agar CSS responsive transisi ribbon tab bekerja */
    function syncBodyClass() {
        const open = sidebarIsOpen();
        document.body.classList.toggle('pos-sidebar-open', open);
        document.body.classList.toggle('fi-sidebar-open', open);
    }

    /** Terapkan state open/close ke store Alpine milik Filament */
    function applySidebarState() {
        if (window.Alpine && Alpine.store && Alpine.store('sidebar')) {
            const sidebarStore = Alpine.store('sidebar');
            if (pinned) {
                sidebarStore.isOpen = true;
            } else {
                sidebarStore.isOpen = false;
            }
        }
        syncBodyClass();
    }

    /** Tunggu Alpine store siap lalu terapkan state */
    function initSidebarState() {
        applySidebarState();

        // Polling singkat untuk memastikan Alpine store dimuat & ter-sync
        let attempts = 0;
        const interval = setInterval(() => {
            attempts++;
            if (window.Alpine && Alpine.store && Alpine.store('sidebar')) {
                clearInterval(interval);
                applySidebarState();
            }
            if (attempts > 60) {
                clearInterval(interval);
            }
        }, 50);
    }

    /** Render pin button di sidebar header */
    function renderPinButton() {
        const oldBtn = document.getElementById('pos-pin-btn');
        if (oldBtn) oldBtn.remove();

        const btn = document.createElement('button');
        btn.id = 'pos-pin-btn';
        btn.type = 'button';
        btn.title = pinned ? 'Unpin sidebar (saat ini ter-pin)' : 'Pin sidebar (selalu terbuka)';
        btn.setAttribute('aria-label', 'Toggle sidebar pin');
        btn.innerHTML = pinned ? PIN_SVG_ON : PIN_SVG_OFF;
        btn.classList.toggle('is-pinned', pinned);

        btn.addEventListener('click', togglePin);

        const header = document.querySelector('.fi-sidebar-header');
        if (header) {
            header.appendChild(btn);
        }
    }

    /** Render Tab Ribbon di samping sidebar */
    function renderTabButton() {
        let tab = document.getElementById('pos-sidebar-tab');
        if (!tab) {
            tab = document.createElement('button');
            tab.id = 'pos-sidebar-tab';
            tab.type = 'button';
            tab.setAttribute('aria-label', 'Toggle sidebar');

            tab.addEventListener('click', (e) => {
                e.stopPropagation();
                if (window.Alpine && Alpine.store && Alpine.store('sidebar')) {
                    const store = Alpine.store('sidebar');
                    store.isOpen = !store.isOpen;
                }
                syncBodyClass();
            });
            document.body.appendChild(tab);
        }

        const open = sidebarIsOpen();
        const icon = open ? CHEVRON_LEFT : CHEVRON_RIGHT;

        tab.innerHTML = `
            <div class="tab-icon">${icon}</div>
            <span>Navigasi</span>
        `;
    }

    /** Logic click pin button */
    function togglePin() {
        pinned = !pinned;
        localStorage.setItem(PIN_KEY, pinned ? 'true' : 'false');

        const btn = document.getElementById('pos-pin-btn');
        if (btn) {
            btn.innerHTML = pinned ? PIN_SVG_ON : PIN_SVG_OFF;
            btn.title = pinned ? 'Unpin sidebar' : 'Pin sidebar';
            btn.classList.toggle('is-pinned', pinned);
        }

        applySidebarState();

        showToast(pinned
            ? '📌 Sidebar di-pin — selalu terbuka.'
            : '📌 Sidebar unpin — otomatis disembunyikan saat pindah halaman.'
        );
    }

    /* ─── SVG Icons ──────────────────────────────────────────────────── */
    const PIN_SVG_OFF = `<svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14" aria-hidden="true">
        <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/>
    </svg>`;

    const PIN_SVG_ON = `<svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14" aria-hidden="true" style="transform: rotate(45deg)">
        <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/>
    </svg>`;

    const CHEVRON_LEFT = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="12" height="12">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
    </svg>`;

    const CHEVRON_RIGHT = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="12" height="12">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
    </svg>`;

    /* ─── Toast ──────────────────────────────────────────────────────── */
    function showToast(msg) {
        let toast = document.getElementById('pos-sb-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'pos-sb-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        toast.classList.add('show');
        clearTimeout(toast._t);
        toast._t = setTimeout(() => toast.classList.remove('show'), 2500);
    }

    /* ─── Mutation Observer & Polling Sync ─────────────────────────────── */
    function startSidebarSync() {
        if (sidebarObserver) {
            sidebarObserver.disconnect();
            sidebarObserver = null;
        }

        const sidebar = document.querySelector('.fi-sidebar');
        if (sidebar) {
            sidebarObserver = new MutationObserver(() => {
                syncBodyClass();
                renderTabButton();
            });
            sidebarObserver.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class', 'style', 'data-open']
            });
        }
    }

    // Loop polling untuk backup sinkronisasi state
    function initPollingSync() {
        setInterval(() => {
            const sidebar = document.querySelector('.fi-sidebar');
            if (!sidebar) {
                const tab = document.getElementById('pos-sidebar-tab');
                if (tab) tab.remove();
                document.body.classList.remove('pos-sidebar-open', 'fi-sidebar-open');
                return;
            }

            syncBodyClass();
            const open = sidebarIsOpen();
            const tab = document.getElementById('pos-sidebar-tab');
            if (tab) {
                const iconEl = tab.querySelector('.tab-icon');
                const currentIcon = open ? CHEVRON_LEFT : CHEVRON_RIGHT;
                if (iconEl && iconEl.innerHTML !== currentIcon) {
                    iconEl.innerHTML = currentIcon;
                }
            }
        }, 100);
    }

    /* ─── Events ─────────────────────────────────────────────────────── */
    function onPageLoad() {
        const sidebar = document.querySelector('.fi-sidebar');
        if (!sidebar) {
            const tab = document.getElementById('pos-sidebar-tab');
            if (tab) tab.remove();
            document.body.classList.remove('pos-sidebar-open', 'fi-sidebar-open');
            if (sidebarObserver) {
                sidebarObserver.disconnect();
                sidebarObserver = null;
            }
            return;
        }

        initSidebarState();
        renderPinButton();
        renderTabButton();
        syncBodyClass();
        startSidebarSync();
    }

    // Keyboard shortcut '[' to toggle sidebar manually
    document.addEventListener('keydown', (e) => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName ?? '')) return;
        if (e.metaKey || e.ctrlKey || e.altKey) return;

        if (e.key === '[') {
            e.preventDefault();
            if (window.Alpine && Alpine.store && Alpine.store('sidebar')) {
                const store = Alpine.store('sidebar');
                store.isOpen = !store.isOpen;
            }
            syncBodyClass();
        }
    });

    // Wire Livewire SPA navigation
    document.addEventListener('livewire:navigated', onPageLoad);

    // Run initial setup
    if (document.readyState !== 'loading') {
        onPageLoad();
        initPollingSync();
    } else {
        document.addEventListener('DOMContentLoaded', () => {
            onPageLoad();
            initPollingSync();
        });
    }

    // Fallback hook untuk Alpine
    document.addEventListener('alpine:init', () => {
        setTimeout(initSidebarState, 100);
    });

})();
