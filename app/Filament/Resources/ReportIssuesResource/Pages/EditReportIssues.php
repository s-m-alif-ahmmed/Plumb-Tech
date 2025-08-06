<?php

namespace App\Filament\Resources\ReportIssuesResource\Pages;

use App\Filament\Resources\ReportIssuesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReportIssues extends EditRecord
{
    protected static string $resource = ReportIssuesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
