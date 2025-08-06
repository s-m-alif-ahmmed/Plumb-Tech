<?php

namespace App\Filament\Resources\DiscussionRequestResource\Pages;

use App\Filament\Resources\DiscussionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiscussionRequests extends ListRecords
{
    protected static string $resource = DiscussionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
