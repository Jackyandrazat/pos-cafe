<?php

namespace App\Filament\Resources;

use App\Enums\LoyaltyChallengeType;
use App\Filament\Resources\LoyaltyChallengeResource\Pages;
use App\Filament\Resources\LoyaltyChallengeResource\RelationManagers\AwardsRelationManager;
use App\Filament\Resources\LoyaltyChallengeResource\RelationManagers\ProgressesRelationManager;
use App\Models\LoyaltyChallenge;
use App\Support\Feature;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoyaltyChallengeResource extends Resource
{
    protected static ?string $model = LoyaltyChallenge::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Loyalty';

    protected static ?string $navigationLabel = 'Gamified Challenges';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Detail Misi')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Misi')
                        ->required()
                        ->maxLength(120)
                        ->reactive()
                        ->afterStateUpdated(fn (?string $state, callable $set, callable $get) => $set('slug', $get('slug') ?: Str::slug((string) $state))),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(150)
                        ->unique(table: LoyaltyChallenge::class, column: 'slug', ignoreRecord: true),
                    Forms\Components\Select::make('type')
                        ->label('Tipe Misi')
                        ->options(self::typeOptions())
                        ->enum(LoyaltyChallengeType::class)
                        ->required()
                        ->live(),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->nullable(),
                ])->columns(2),
            Forms\Components\Section::make('Target & Jadwal')
                ->schema([
                    Forms\Components\TextInput::make('target_value')
                        ->label('Target')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    Forms\Components\TextInput::make('bonus_points')
                        ->label('Bonus Poin')
                        ->numeric()
                        ->minValue(0)
                        ->required(),
                    Forms\Components\Select::make('reset_period')
                        ->label('Reset Progress')
                        ->options([
                            'none' => 'Tidak pernah reset',
                            'weekly' => 'Mingguan',
                            'monthly' => 'Bulanan (30 hari)',
                        ])
                        ->required(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktifkan Misi')
                        ->default(true),
                    Forms\Components\DateTimePicker::make('active_from')
                        ->label('Aktif Mulai')
                        ->seconds(false),
                    Forms\Components\DateTimePicker::make('active_until')
                        ->label('Aktif Sampai')
                        ->seconds(false)
                        ->minDate(fn (callable $get) => $get('active_from')),
                    Forms\Components\TextInput::make('config.min_unique_count')
                        ->label('Minimal Menu Baru')
                        ->numeric()
                        ->minValue(1)
                        ->visible(fn (callable $get) => $get('type') === LoyaltyChallengeType::NewVariant->value)
                        ->helperText('Jumlah menu unik baru yang harus dicoba kasir.'),
                ])->columns(3),
            Forms\Components\Section::make('Badge & Branding')
                ->schema([
                    Forms\Components\TextInput::make('badge_name')
                        ->label('Nama Badge')
                        ->maxLength(120)
                        ->nullable(),
                    Forms\Components\TextInput::make('badge_code')
                        ->label('Kode Badge')
                        ->maxLength(150)
                        ->nullable(),
                    Forms\Components\ColorPicker::make('badge_color')
                        ->label('Warna Badge')
                        ->nullable(),
                    Forms\Components\TextInput::make('badge_icon')
                        ->label('Ikon (Heroicons / Iconify)')
                        ->maxLength(120)
                        ->placeholder('mdi:calendar-check')
                        ->nullable(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withCount([
                    'progresses as active_participants_count' => fn ($q) => $q->whereNull('rewarded_at'),
                    'awards as awards_count',
                ]))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Misi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => self::typeLabel($state)),
                Tables\Columns\TextColumn::make('target_value')
                    ->label('Target')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bonus_points')
                    ->label('Bonus')
                    ->suffix(' pts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reset_period')
                    ->label('Reset')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'weekly' => 'Mingguan',
                        'monthly' => 'Bulanan',
                        default => 'Tidak reset',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('active_participants_count')
                    ->label('Peserta Aktif')
                    ->sortable(),
                Tables\Columns\TextColumn::make('awards_count')
                    ->label('Badge Dibagikan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif'),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Misi')
                    ->options(self::typeOptions()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProgressesRelationManager::class,
            AwardsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoyaltyChallenges::route('/'),
            'create' => Pages\CreateLoyaltyChallenge::route('/create'),
            'edit' => Pages\EditLoyaltyChallenge::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::authorizedForAdmin();
    }

    public static function canViewAny(): bool
    {
        return self::authorizedForAdmin();
    }

    public static function canCreate(): bool
    {
        return self::authorizedForAdmin();
    }

    public static function canEdit($record): bool
    {
        return self::authorizedForAdmin();
    }

    public static function canDelete($record): bool
    {
        return self::authorizedForAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return self::authorizedForAdmin();
    }

    protected static function authorizedForAdmin(): bool
    {
        if (! Feature::enabled('loyalty')) {
            return false;
        }

        $user = Auth::user();

        return $user?->hasAnyRole(['admin', 'owner', 'superadmin']) ?? false;
    }

    protected static function typeOptions(): array
    {
        return collect(LoyaltyChallengeType::cases())
            ->mapWithKeys(fn (LoyaltyChallengeType $type) => [$type->value => $type->label()])
            ->toArray();
    }

    protected static function typeLabel($state): string
    {
        $key = $state instanceof LoyaltyChallengeType ? $state->value : (string) $state;

        return self::typeOptions()[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }
}
