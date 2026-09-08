<?php

namespace App\Filament\Pages;

use App\Helpers\SettingHelper;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ReceiptSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-printer';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $title = 'Pengaturan Struk';

    protected static ?string $navigationLabel = 'Pengaturan Struk';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.receipt-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && $user->hasAnyRole(['admin', 'owner', 'superadmin']);
    }

    public function mount(): void
    {
        $this->form->fill([
            'receipt_cafe_name'     => setting('receipt_cafe_name', 'KAFE DIGITAL POS'),
            'receipt_cafe_address'  => setting('receipt_cafe_address', 'Jl. Kopi Harapan No. 12, Jakarta'),
            'receipt_cafe_phone'    => setting('receipt_cafe_phone', '0812-3456-7890'),
            'receipt_wifi_ssid'     => setting('receipt_wifi_ssid', 'KafeDigital'),
            'receipt_wifi_password' => setting('receipt_wifi_password', 'kopiEnak2026'),
            'receipt_feedback_info' => setting('receipt_feedback_info', '@kafedigital'),
            'receipt_footer_text'   => setting('receipt_footer_text', 'TERIMA KASIH ATAS KUNJUNGAN ANDA!'),
            'receipt_footer_subtext'=> setting('receipt_footer_subtext', '* Struk ini sah sebagai bukti pembayaran *'),
            'receipt_paper_width'   => setting('receipt_paper_width', '58mm'),
            'self_order_allow_cash' => filter_var(setting('self_order_allow_cash', true), FILTER_VALIDATE_BOOLEAN),
            'cafe_latitude'         => setting('cafe_latitude', ''),
            'cafe_longitude'        => setting('cafe_longitude', ''),
            'cafe_geofence_radius'  => (int) setting('cafe_geofence_radius', 100),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Kafe pada Header Struk')
                    ->description('Data nama kafe, alamat, dan nomor kontak yang akan tercetak di bagian paling atas struk thermal.')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Forms\Components\TextInput::make('receipt_cafe_name')
                            ->label('Nama Kafe / Usaha')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: KAFE DIGITAL POS')
                            ->helperText('Tampil dengan huruf tebal (bold) di judul struk.'),

                        Forms\Components\TextInput::make('receipt_cafe_phone')
                            ->label('Nomor Telepon / WhatsApp')
                            ->maxLength(50)
                            ->placeholder('Contoh: 0812-3456-7890')
                            ->helperText('Kontak resmi kafe yang bisa dihubungi pelanggan.'),

                        Forms\Components\Textarea::make('receipt_cafe_address')
                            ->label('Alamat Lengkap Kafe')
                            ->rows(2)
                            ->maxLength(255)
                            ->placeholder('Contoh: Jl. Kopi Harapan No. 12, Jakarta')
                            ->columnSpanFull()
                            ->helperText('Alamat toko fisik atau lokasi outlet kafe.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Fasilitas & Masukan Pelanggan')
                    ->description('Informasi koneksi WiFi dan saluran kritik/saran yang tercetak di bagian footer struk.')
                    ->icon('heroicon-o-wifi')
                    ->schema([
                        Forms\Components\TextInput::make('receipt_wifi_ssid')
                            ->label('Nama Jaringan WiFi (SSID)')
                            ->placeholder('Contoh: KafeDigital')
                            ->maxLength(60)
                            ->helperText('Kosongkan jika tidak ingin menampilkan info WiFi pada struk.'),

                        Forms\Components\TextInput::make('receipt_wifi_password')
                            ->label('Kata Sandi WiFi (Password)')
                            ->placeholder('Contoh: kopiEnak2026')
                            ->maxLength(60)
                            ->helperText('Kata sandi yang otomatis tercetak untuk pelanggan yang membeli.'),

                        Forms\Components\TextInput::make('receipt_feedback_info')
                            ->label('Kontak Kritik & Saran / Medsos')
                            ->placeholder('Contoh: @kafedigital atau 0812-xxxx-xxxx')
                            ->maxLength(100)
                            ->columnSpanFull()
                            ->helperText('Akun Instagram, nomor CS, atau email masukan pelanggan.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Catatan Kaki & Format Struk')
                    ->description('Pesan ucapan terima kasih dan ukuran lebar kertas printer thermal.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('receipt_footer_text')
                            ->label('Pesan Penutup Utama')
                            ->placeholder('Contoh: TERIMA KASIH ATAS KUNJUNGAN ANDA!')
                            ->maxLength(150)
                            ->helperText('Ucapan terima kasih atau slogan kafe.'),

                        Forms\Components\TextInput::make('receipt_footer_subtext')
                            ->label('Teks Legal / Bukti Pembayaran')
                            ->placeholder('Contoh: * Struk ini sah sebagai bukti pembayaran *')
                            ->maxLength(150)
                            ->helperText('Teks kecil di baris paling akhir struk.'),

                        Forms\Components\Select::make('receipt_paper_width')
                            ->label('Format Lebar Kertas Printer')
                            ->options([
                                '58mm' => '58mm (Standar printer kasir mini / EDC bluetooth)',
                                '80mm' => '80mm (Printer thermal kasir besar / desktop)',
                            ])
                            ->default('58mm')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Menentukan batas lebar layout struk saat dicetak.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Keamanan Pemesanan Mandiri & Geofencing GPS')
                    ->description('Proteksi pemesanan mandiri QR Meja agar terhindar dari order fiktif jarak jauh.')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Toggle::make('self_order_allow_cash')
                            ->label('Izinkan Pembayaran Tunai (Cash di Kasir) pada Self-Order')
                            ->default(true)
                            ->helperText('Jika dinonaktifkan, pesanan QR Meja HANYA menerima pembayaran digital (QRIS, E-Wallet, Transfer) untuk mencegah order fiktif jarak jauh.'),

                        Forms\Components\TextInput::make('cafe_geofence_radius')
                            ->label('Radius Batas Lokasi (Meter)')
                            ->numeric()
                            ->default(100)
                            ->placeholder('100')
                            ->helperText('Jarak maksimal pengunjung dari kafe untuk bisa memesan di meja (misal: 100 meter). Isi 0 untuk menonaktifkan validasi jarak.'),

                        Forms\Components\TextInput::make('cafe_latitude')
                            ->label('Latitude Kafe')
                            ->placeholder('Contoh: -6.2088')
                            ->helperText('Koordinat lintang lokasi kafe.'),

                        Forms\Components\TextInput::make('cafe_longitude')
                            ->label('Longitude Kafe')
                            ->placeholder('Contoh: 106.8456')
                            ->helperText('Koordinat bujur lokasi kafe.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SettingHelper::setMany([
            'receipt_cafe_name'     => trim($state['receipt_cafe_name'] ?? 'KAFE DIGITAL POS'),
            'receipt_cafe_address'  => trim($state['receipt_cafe_address'] ?? ''),
            'receipt_cafe_phone'    => trim($state['receipt_cafe_phone'] ?? ''),
            'receipt_wifi_ssid'     => trim($state['receipt_wifi_ssid'] ?? ''),
            'receipt_wifi_password' => trim($state['receipt_wifi_password'] ?? ''),
            'receipt_feedback_info' => trim($state['receipt_feedback_info'] ?? ''),
            'receipt_footer_text'   => trim($state['receipt_footer_text'] ?? ''),
            'receipt_footer_subtext'=> trim($state['receipt_footer_subtext'] ?? ''),
            'receipt_paper_width'   => $state['receipt_paper_width'] ?? '58mm',
            'self_order_allow_cash' => ! empty($state['self_order_allow_cash']) ? '1' : '0',
            'cafe_latitude'         => trim($state['cafe_latitude'] ?? ''),
            'cafe_longitude'        => trim($state['cafe_longitude'] ?? ''),
            'cafe_geofence_radius'  => (string) ((int) ($state['cafe_geofence_radius'] ?? 100)),
        ]);

        Notification::make()
            ->title('Pengaturan Berhasil Disimpan!')
            ->body('Pengaturan identitas kafe, format struk, dan keamanan self-order telah diperbarui.')
            ->success()
            ->send();
    }
}
