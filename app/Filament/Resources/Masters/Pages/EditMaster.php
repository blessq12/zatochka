<?php

namespace App\Filament\Resources\Masters\Pages;

use App\Filament\Resources\Masters\MasterResource;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditMaster extends EditRecord
{
    protected static string $resource = MasterResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var UserModel $record */
        if (UserModel::query()
            ->where('email', $data['email'])
            ->whereKeyNot($record->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'email' => 'Мастер с таким email уже существует.',
            ]);
        }

        $record->fill([
            'name' => $data['name'],
            'surname' => $data['surname'] ?? '',
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if (($data['password'] ?? null) !== null && $data['password'] !== '') {
            $record->password = $data['password'];
        }

        $record->save();

        return $record;
    }
}
