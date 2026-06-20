<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Application\Catalog\Command\SaveBranchCommand;
use App\Application\Catalog\CommandHandler\SaveBranchHandler;
use App\Filament\Resources\Branches\BranchResource;
use App\Infrastructure\Catalog\Persistence\Eloquent\BranchModel;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var BranchModel $record */
        $branch = app(SaveBranchHandler::class)->handle(new SaveBranchCommand(
            id: $record->id,
            name: $data['name'],
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            isActive: (bool) ($data['is_active'] ?? false),
        ));

        return BranchModel::query()->findOrFail($branch->id());
    }
}
