<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Total Users',
                // Get the total number of users
                User::count()
            )
                ->description('New Users ' . '(' . Carbon::now()->format('F') . ')' . ': ' . User::whereMonth('created_at', Carbon::now()->month)->count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart(
                    // Chart data for last month, with the count for each day in the last month
                    collect(range(0, Carbon::now()->subMonth()->daysInMonth - 1))
                        ->map(
                            fn($day) =>
                            User::whereDate('created_at', Carbon::now()->subMonth()->startOfMonth()->addDays($day))->count()
                        )->toArray()
                )
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),




            Stat::make('Total Services', Service::count())
                ->description('New services ' . '(' . Carbon::now()->format('F') . ')' . ': ' . Service::whereMonth('created_at', Carbon::now()->month)->count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart(
                    // Chart data for the last month's services, dynamically based on the current month
                    collect(range(0, Carbon::now()->subMonth()->daysInMonth - 1))
                        ->map(
                            fn($day) =>
                            Service::whereDate('created_at', Carbon::now()->subMonth()->startOfMonth()->addDays($day)->toDateString())->count()
                        )->toArray()
                )
                ->color('success'),

            Stat::make('Total Revenue', Payment::sum('amount'))
                ->description(
                    //increase percentage
                    Payment::whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()])
                        ->sum('amount') > 0
                        ? (Payment::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()])
                            ->sum('amount') /
                            Payment::whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()])
                            ->sum('amount') * 100 > 100 ? '+' . (Payment::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()])
                                ->sum('amount') /
                                Payment::whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()])
                                ->sum('amount') * 100 - 100) . '%' : '-' . (100 - Payment::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()])
                                ->sum('amount') /
                                Payment::whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()])
                                ->sum('amount') * 100) . '%')
                        : '0%'
                )
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart(
                    collect(range(0, Carbon::now()->subMonth()->daysInMonth - 1))
                        ->map(
                            fn($day) =>
                            Payment::whereDate('created_at', Carbon::now()->subMonth()->startOfMonth()->addDays($day)->toDateString())->sum('amount')
                        )->toArray()
                )
                ->color('success'),


            Stat::make('Total Withdrawal Amount', WithdrawalRequest::where('status', 'approved')->sum('amount'))
                ->description('Total approved requests: ' . WithdrawalRequest::where('status', 'approved')->count())
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart(
                    //for current month
                    collect(range(0, Carbon::now()->daysInMonth - 1))
                        ->map(
                            fn($day) =>
                            WithdrawalRequest::where('status', 'approved')
                                ->whereYear('created_at', Carbon::now()->year)
                                ->whereMonth('created_at', Carbon::now()->month)
                                ->whereDay('created_at', $day + 1)
                                ->sum('amount')
                        )->toArray()
                )
                ->color('success'),

            Stat::make('Total Withdraw Request', WithdrawalRequest::count())
                ->description('Total accepted: ' . (WithdrawalRequest::where('status', 'approved')->count() ?: '0'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart(
                    //for current month
                    collect(range(0, Carbon::now()->daysInMonth - 1))
                        ->map(
                            fn($day) =>
                            WithdrawalRequest::where('status', 'approved')
                                ->whereYear('created_at', Carbon::now()->year)
                                ->whereMonth('created_at', Carbon::now()->month)
                                ->whereDay('created_at', $day + 1)
                                ->sum('amount')
                        )->toArray()
                )
                ->color('success'),


            Stat::make('Total Withdraw Request Pending', WithdrawalRequest::where('status', 'pending')->count())
                ->description('Total Rejected: ' . (WithdrawalRequest::where('status', 'rejected')->count() ?: '0'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart(
                    //for current month
                    collect(range(0, Carbon::now()->daysInMonth - 1))
                        ->map(
                            fn($day) =>
                            WithdrawalRequest::where('status', 'pending')
                                ->whereYear('created_at', Carbon::now()->year)
                                ->whereMonth('created_at', Carbon::now()->month)
                                ->whereDay('created_at', $day + 1)
                                ->count()
                        )->toArray()
                )
                ->color('success'),
        ];
    }
}
