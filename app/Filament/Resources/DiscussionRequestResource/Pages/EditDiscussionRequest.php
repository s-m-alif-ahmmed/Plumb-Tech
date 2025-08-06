<?php

namespace App\Filament\Resources\DiscussionRequestResource\Pages;

use App\Filament\Resources\DiscussionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiscussionRequest extends EditRecord
{
    protected static string $resource = DiscussionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
