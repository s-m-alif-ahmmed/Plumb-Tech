<?php

namespace App\Filament\Resources\ReportIssuesResource\Pages;

use App\Filament\Resources\ReportIssuesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReportIssues extends ListRecords
{
    protected static string $resource = ReportIssuesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
