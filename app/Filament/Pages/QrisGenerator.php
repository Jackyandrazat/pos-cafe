<?php

namespace App\Filament\Pages;

use App\Models\QrisConfig;
use App\Services\QrisService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrisGenerator extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Konfigurasi QRIS';
    protected static ?string $navigationGroup = 'Pembayaran';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $title           = 'Konfigurasi QRIS';
    protected static string  $view            = 'filament.pages.qris-generator';

    public ?array $data = [];

    /** SVG QR code hasil preview test generate. */
    public ?string $previewQrSvg    = null;
    public ?string $previewQrisStr  = null;
    public ?int    $previewAmount   = null;
    public bool    $isSaved         = false;

    public function mount(): void
    {
        $config = QrisConfig::active();

        $this->form->fill([
            'merchant_name' => $config?->merchant_name ?? '',
            'static_string' => $config?->static_string ?? '',
            'test_amount'   => 10000,
        ]);

        $this->isSaved = QrisConfig::isConfigured();
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && $user->hasAnyRole(['admin', 'owner', 'superadmin']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konfigurasi QRIS Merchant')
                    ->description('Simpan QRIS statis merchant di sini. Sistem akan otomatis generate QR dinamis saat ada pembayaran QRIS.')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Forms\Components\TextInput::make('merchant_name')
                            ->label('Nama Merchant')
                            ->placeholder('Cafe Saya')
                            ->helperText('Opsional — hanya untuk referensi internal.')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('status_badge')
                            ->label('Status QRIS')
                            ->content(fn () => QrisConfig::isConfigured() ? '✅ QRIS sudah dikonfigurasi dan siap digunakan.' : '⚠️ QRIS belum dikonfigurasi. Payment QRIS akan memakai mode konfirmasi manual.')
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('static_string')
                            ->label('String QRIS Statis (EMVCo)')
                            ->placeholder('00020101021126...')
                            ->helperText('Copy dari stiker/cetakan QRIS merchant Anda. Format standar EMVCo. Wajib diisi agar QR otomatis ter-generate saat payment QRIS dibuat.')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Test Generate QR')
                    ->description('Uji coba generate QRIS dinamis dengan nominal tertentu sebelum disimpan.')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Forms\Components\TextInput::make('test_amount')
                            ->label('Nominal Test (Rp)')
                            ->numeric()
                            ->minValue(1)
                            ->default(10000)
                            ->prefix('Rp')
                            ->helperText('Masukkan nominal untuk preview QR code.'),
                    ])
                    ->collapsed(fn () => ! QrisConfig::isConfigured()),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('💾 Simpan Konfigurasi')
                ->color('success')
                ->icon('heroicon-o-check')
                ->action('save'),

            Action::make('preview')
                ->label('🔍 Preview QR')
                ->color('info')
                ->icon('heroicon-o-qr-code')
                ->action('previewQr'),
        ];
    }

    /** Simpan konfigurasi QRIS ke database. */
    public function save(): void
    {
        $state = $this->form->getState();

        $staticString = trim($state['static_string'] ?? '');

        if ($staticString && strlen($staticString) < 10) {
            Notification::make()
                ->title('String QRIS tidak valid')
                ->body('QRIS string terlalu pendek. Pastikan Anda menempelkan string QRIS yang benar.')
                ->danger()
                ->send();
            return;
        }

        // Nonaktifkan semua config lama
        QrisConfig::query()->update(['is_active' => false]);

        // Buat/update config aktif
        QrisConfig::create([
            'static_string' => $staticString ?: null,
            'merchant_name' => trim($state['merchant_name'] ?? '') ?: null,
            'is_active'     => true,
        ]);

        $this->isSaved = $staticString !== '';

        Notification::make()
            ->title('Konfigurasi QRIS disimpan!')
            ->body($staticString ? 'QRIS siap digunakan untuk payment otomatis.' : 'Static string dikosongkan. Payment QRIS akan pakai mode manual.')
            ->success()
            ->send();
    }

    /** Preview QR code berdasarkan static string yang sedang diisi dan nominal test. */
    public function previewQr(): void
    {
        $state        = $this->form->getState();
        $staticString = trim($state['static_string'] ?? '');
        $amount       = (float) ($state['test_amount'] ?? 10000);

        if (strlen($staticString) < 10) {
            // Coba ambil dari DB jika field kosong
            $staticString = QrisConfig::getStaticString() ?? '';
        }

        if (strlen($staticString) < 10) {
            Notification::make()
                ->title('String QRIS diperlukan')
                ->body('Masukkan Static QRIS string di form di atas, atau simpan konfigurasi terlebih dahulu.')
                ->warning()
                ->send();
            return;
        }

        try {
            $qrisService = app(QrisService::class);

            $this->previewQrisStr = $qrisService->convertToDynamic($staticString, $amount);
            $this->previewAmount  = (int) $amount;
            $this->previewQrSvg   = (string) QrCode::format('svg')
                ->size(300)
                ->errorCorrection('M')
                ->generate($this->previewQrisStr);

            Notification::make()
                ->title('Preview QR berhasil dibuat')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->previewQrSvg   = null;
            $this->previewQrisStr = null;

            Notification::make()
                ->title('Gagal generate QR')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetPreview(): void
    {
        $this->previewQrSvg   = null;
        $this->previewQrisStr = null;
        $this->previewAmount  = null;
    }
}
