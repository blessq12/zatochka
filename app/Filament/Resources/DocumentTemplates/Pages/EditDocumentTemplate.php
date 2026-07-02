<?php

namespace App\Filament\Resources\DocumentTemplates\Pages;

use App\Application\OrderFulfillment\Command\PreviewDocumentTemplateCommand;
use App\Application\OrderFulfillment\Command\UpdateDocumentTemplateCommand;
use App\Application\OrderFulfillment\CommandHandler\PreviewDocumentTemplateHandler;
use App\Application\OrderFulfillment\CommandHandler\UpdateDocumentTemplateHandler;
use App\Domain\OrderFulfillment\Enum\DocumentType;
use App\Filament\Resources\DocumentTemplates\DocumentTemplateResource;
use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\DocumentTemplateModel;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EditDocumentTemplate extends EditRecord
{
    protected static string $resource = DocumentTemplateResource::class;

    public function getTitle(): string
    {
        /** @var DocumentTemplateModel $record */
        $record = $this->getRecord();

        return DocumentType::from($record->type)->label();
    }

  /** @return array<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Предпросмотр')
                ->icon('heroicon-o-eye')
                ->action(function (): void {
                    $data = $this->form->getState();

                    /** @var DocumentTemplateModel $record */
                    $record = $this->getRecord();

                    /** @var UserModel $user */
                    $user = auth()->user();

                    $document = app(PreviewDocumentTemplateHandler::class)->handle(new PreviewDocumentTemplateCommand(
                        type: DocumentType::from($record->type),
                        body: $data['body'],
                        orderId: isset($data['preview_order_id']) ? (int) $data['preview_order_id'] : null,
                        managerName: trim($user->name.' '.$user->surname),
                    ));

                    $token = (string) Str::uuid();
                    Cache::put('document_template_preview_'.$token, $document->content, now()->addMinute());

                    $url = route('filament.admin.document-templates.preview', ['token' => $token]);
                    $this->js('window.open('.json_encode($url).', "_blank")');
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        app(UpdateDocumentTemplateHandler::class)->handle(new UpdateDocumentTemplateCommand(
            type: DocumentType::from($record->type),
            body: $data['body'],
            userId: auth()->id(),
        ));

        Notification::make()
            ->success()
            ->title('Шаблон сохранён')
            ->send();

        return $record->refresh();
    }
}
