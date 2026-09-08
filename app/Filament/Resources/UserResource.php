<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Manajemen Pengguna';

    protected static ?string $modelLabel = 'Staf / Pengguna';

    protected static ?string $pluralModelLabel = 'Manajemen Pengguna';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && $user->hasAnyRole(['admin', 'owner']);
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return static::canAccess();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_guest', false)
            ->whereNull('customer_id')
            ->with('roles');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun Staf')
                    ->description('Lengkapi identitas login dan kontak staf kafe.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Budi Pratama'),

                        Forms\Components\TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('budi@poscafe.com'),

                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor WhatsApp / Telepon')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('081234567890'),

                        Forms\Components\TextInput::make('password')
                            ->label('Kata Sandi (Password)')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->helperText(fn (string $operation): string => $operation === 'edit'
                                ? 'Kosongkan kolom ini jika tidak ingin mengubah kata sandi lama.'
                                : 'Minimal 6 karakter.')
                            ->minLength(6),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Akun Aktif')
                            ->helperText('Jika dinonaktifkan, akun ini tidak akan bisa login ke sistem kasir/admin.')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Peran & Hak Akses (Role)')
                    ->description('Tentukan hak akses modul yang diperbolehkan untuk akun ini.')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Pilih Peran (Role)')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->required()
                            ->options([
                                'admin'   => '👑 Admin — Akses penuh semua modul, master data, dan pengaturan sistem',
                                'owner'   => '🏢 Owner — Akses laporan omset, dashboard analisis bisnis, & katalog menu',
                                'kasir'   => '🧑‍💼 Kasir — Akses POS meja, pemesanan, pembayaran, cetak struk, & shift kasir',
                                'kitchen' => '☕ Kitchen / Barista — Khusus layar Kitchen Display (antrean pesanan dapur)',
                            ])
                            ->default(function () {
                                $staffCount = User::where('is_guest', false)->whereNull('customer_id')->count();

                                return $staffCount === 0 ? ['admin'] : ['kasir'];
                            })
                            ->helperText('Pilih satu atau lebih peran yang sesuai dengan tugas staf kafe.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Staf')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (User $record): string => $record->phone ? "📞 {$record->phone}" : '-'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Peran / Hak Akses')
                    ->badge()
                    ->colors([
                        'danger'  => 'admin',
                        'warning' => 'owner',
                        'success' => 'kasir',
                        'info'    => 'kitchen',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin'   => '👑 Admin',
                        'owner'   => '🏢 Owner',
                        'kasir'   => '🧑‍💼 Kasir',
                        'kitchen' => '☕ Kitchen',
                        default   => ucfirst($state),
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filter Peran')
                    ->relationship('roles', 'name')
                    ->options([
                        'admin'   => 'Admin',
                        'owner'   => 'Owner',
                        'kasir'   => 'Kasir',
                        'kitchen' => 'Kitchen',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->trueLabel('Hanya Akun Aktif')
                    ->falseLabel('Hanya Akun Non-Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Akun Staf')
                    ->modalDescription('Apakah Anda yakin ingin menghapus akun staf ini? Riwayat transaksi dan shift lama tetap tersimpan.')
                    ->before(function (Tables\Actions\DeleteAction $action, User $record) {
                        if ($record->id === Auth::id()) {
                            Notification::make()
                                ->danger()
                                ->title('Tidak dapat menghapus!')
                                ->body('Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
