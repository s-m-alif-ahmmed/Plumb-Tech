<?php

namespace App\Filament\Resources\ReportIssuesResource\Pages;

use App\Filament\Resources\ReportIssuesResource;
use App\Models\DiscussionRequest;
use App\Models\ReportIssues;
use Filament\Resources\Pages\Page;

class ViewReportIssues extends Page
{
    protected static string $resource = ReportIssuesResource::class;

    protected static string $view = 'filament.resources.report-issues-resource.pages.view-report-issues';

    public ReportIssues $record;
    public ?DiscussionRequest $discussionRequest = null;

    public function mount(ReportIssues $record)
    {
        // Load the record and its related user and engineer
        // dd($record);
        $this->record = $record;

        // Get latest discussion request for the given user_id
        $this->discussionRequest = DiscussionRequest::where('user_id', $record->user_id)
            ->where('status', 'completed')
            ->latest()
            ->first();
    }
}
