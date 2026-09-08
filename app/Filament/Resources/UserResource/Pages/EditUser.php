<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Edit Akun Staf';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->modalHeading('Hapus Akun Staf')
                ->modalDescription('Apakah Anda yakin ingin menghapus akun staf ini?')
                ->before(function (Actions\DeleteAction $action) {
                    if ($this->record->id === Auth::id()) {
                        Notification::make()
                            ->danger()
                            ->title('Tidak dapat menghapus!')
                            ->body('Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.')
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
