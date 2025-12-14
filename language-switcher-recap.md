# Language Switcher Implementation Recap

## 1. Features Added
- **Locale Middleware** (`app/Http/Middleware/SetLocaleFromSession.php`): Syncs the selected locale across Laravel, Filament, and Carbon by reading `app_locale` from the session.
- **Livewire Switcher** (`app/Livewire/LanguageSwitcher.php` + Blade views in `resources/views/livewire` and `resources/views/filament/components`): Provides a dropdown next to the Filament notification bell (via `AppServiceProvider` render hook) to toggle Indonesian/English.
- **Localization Assets**:
  - Default app locale set to Indonesian (`config/app.php`).
  - Added JSON & PHP translation resources (`resources/lang/en|id.json`, `resources/lang/en|id/orders.php`).
  - Updated Order & Payment card views to call translation keys and use `app()->getLocale()` for currency output.
- **Card View Enhancements**: Payment cards now mirror order cards with bilingual labels, menu summaries, payment lists, and CTA text.

## 2. Bug History & Fixes
1. **Unsupported GET /livewire/update**
   - *Cause*: Using `redirect(..., navigate: true)` after switching locales caused Livewire to fire a GET request against `/livewire/update`, which only accepts POST.
   - *Fix*: Switched to a standard redirect so Livewire completes normally without the invalid GET.

2. **Redirect Target Was Still /livewire/update**
   - *Cause*: The redirect URL was captured mid-Livewire request (`URL::full()`), so it pointed back to the Livewire endpoint.
   - *Fix*: Cached the actual page URL during `mount()` (`url()->current()`) and redirected to that stored value, ensuring the browser lands on the Filament page instead of `/livewire/update`.

3. **Switcher Label Didn’t Update**
   - *Cause*: SPA-style redirects prevented a full reload, so the dropdown trigger stayed on the old label even though translations updated.
   - *Fix*: Forced a full page reload with a normal redirect, guaranteeing the switcher reflects the latest locale everywhere.

## 3. Testing
- `php artisan test`

## 4. Menambahkan Terjemahan Baru
1. **Tentukan frasa sumber**: Cari teks yang ingin diterjemahkan di view atau komponen (misal Blade). Bungkus dengan helper `__()` atau `trans_choice()` jika belum.
2. **Pilih lokasi berkas bahasa**:
   - Gunakan `resources/lang/{locale}.json` untuk string tunggal sederhana.
   - Gunakan berkas PHP (mis. `resources/lang/{locale}/orders.php`) untuk struktur bertingkat atau grouping domain tertentu.
3. **Tambahkan kunci & nilai**: Masukkan pasangan key/value pada locale yang relevan, lalu tambahkan padanan di locale lain (contoh: `en.json` dan `id.json`).
4. **Gunakan kunci tersebut** di view: panggil `__('key')` atau `trans_choice('key', $count)` agar teks berubah mengikuti locale aktif.
5. **Uji hasil**: Ganti bahasa via switcher, reload halaman, dan pastikan teks tampil sesuai.
