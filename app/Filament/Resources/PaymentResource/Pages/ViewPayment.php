<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Payment Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('payment_id')
                                    ->label('Payment ID'),
                                TextEntry::make('transaction_id')
                                    ->label('Transaction ID'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('amount')
                                    ->money(fn ($record) => $record->currency_code),
                                TextEntry::make('application_fee')->money('USD'),
                                TextEntry::make('currency_code'),
                            ]),
                    ]),

                Grid::make(2)
                    ->schema([
                        Section::make('Client Information')
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('paymentBy.name')
                                            ->label('Name')
                                            ->size('lg')
                                            ->weight('bold'),
                                        TextEntry::make('paymentBy.email')
                                            ->label('Email')
                                            ->icon('heroicon-o-envelope'),
                                        TextEntry::make('paymentBy.role')
                                            ->label('Role')
                                            ->badge()
                                            ->color('primary'),
                                    ]),
                            ])
                            ->collapsible(),

                        Section::make('Engineer Information')
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('paymentFor.name')
                                            ->label('Name')
                                            ->size('lg')
                                            ->weight('bold'),
                                        TextEntry::make('paymentFor.email')
                                            ->label('Email')
                                            ->icon('heroicon-o-envelope'),
                                        TextEntry::make('paymentFor.service')
                                            ->label('Service Offered')
                                            ->icon('heroicon-o-wrench-screwdriver'),
                                        TextEntry::make('paymentFor.role')
                                            ->label('Role')
                                            ->badge()
                                            ->color('success'),
                                    ]),
                            ])
                            ->collapsible(),
                    ]),

                Section::make('Engineer Additional Information')
                    ->schema([
                        TextEntry::make('paymentFor.address')
                            ->label('Address')
                            ->icon('heroicon-o-map-pin'),
                        TextEntry::make('paymentFor.about')
                            ->label('About')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Discussion Request Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('discussionRequest.service_title')
                                    ->label('Service Title')
                                    ->weight('medium'),
                                TextEntry::make('discussionRequest.price')
                                    ->money(fn ($record) => $record->currency_code)
                                    ->label('Service Price'),
                            ]),
                        TextEntry::make('discussionRequest.description')
                            ->label('Description')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
