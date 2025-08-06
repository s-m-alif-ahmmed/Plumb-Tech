<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\DiscussionRequest;

class DiscussionRequestChart extends ChartWidget
{
    protected static ?string $heading = 'Discussion Request Added by Month';

    protected function getData(): array
    {
        // Get the completed DiscussionRequest count for each of the past 12 months
        $userCountByMonth = DiscussionRequest::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->pluck('count', 'month')
            ->toArray();

        // Fill missing months with 0
        $data = array_fill(1, 12, 0);
        foreach ($userCountByMonth as $month => $count) {
            $data[$month] = $count;
        }
        return [
            'datasets' => [
                [
                    'label' => 'Discussion Request Added (' . now()->year . ')',
                    'data' => array_values($data),
                    'backgroundColor' => '#4CAF50',
                ],
            ],
            'labels' => [
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
