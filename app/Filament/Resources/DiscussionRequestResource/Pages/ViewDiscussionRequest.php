<?php

namespace App\Filament\Resources\DiscussionRequestResource\Pages;

use App\Filament\Resources\DiscussionRequestResource;
use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\UserResource;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewDiscussionRequest extends ViewRecord
{
    protected static string $resource = DiscussionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\EditAction::make(),
        ];
    }
    public function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                Section::make('Service Details')
                    ->schema([
                        Grid::make(3)->schema([
                            // Service Title
                            TextEntry::make('service_title')
                                ->label('Service Title')
                                ->icon('heroicon-m-document-text'),
                            // Price
                            TextEntry::make('price')
                                ->label('Service Price')
                                ->money('USD')
                                ->icon('heroicon-m-currency-dollar'),
                            // Status
                            TextEntry::make('status')
                                ->label('Service Status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'pending' => 'warning',
                                    'in_progress' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'gray',
                                }),
                        ]),

                        // Description
                        TextEntry::make('description')
                            ->label('Request Description')
                            ->prose(),

                        // Images
                        ImageEntry::make('images')
                            ->label('Service Images')
                            ->circular()
                            ->disk('public_path')
                            ->height(100)
                            ->width(100),

                        // Questions and Answers
                        RepeatableEntry::make('question_answer')
                            ->label('Service Q&A')
                            ->schema([
                                TextEntry::make('question')
                                    ->label('Question')
                                    ->icon('heroicon-m-question-mark-circle'),
                                TextEntry::make('answer')
                                    ->label('Answer')
                                    ->icon('heroicon-m-check-circle'),
                            ])
                    ]),
                Section::make('User Details')->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('user.name')->label('Name')->icon('heroicon-o-user'),
                        TextEntry::make('user.email')->label('Email')->icon('heroicon-o-envelope'),
                        TextEntry::make('user.role')->label('Role')->badge(),
                    ])
                ])->headerActions([
                    Action::make('View User Details')->url(UserResource::getUrl('view', ['record' => $this->record->user])),
                ]),
                Section::make('Engineer Details')->schema([
                    Grid::make(3)->schema([
                        TextEntry::make('engineer.name')->label('Name')->icon('heroicon-o-user'),
                        TextEntry::make('engineer.email')->label('Email')->icon('heroicon-o-envelope'),
                        TextEntry::make('engineer.role')->label('Role')->badge(),
                        TextEntry::make('address')
                            ->icon('heroicon-o-map-pin')
                            ->default('N/A'),

                        TextEntry::make('engineer.service')
                            ->label('Service')
                            ->icon('heroicon-o-wrench')
                            ->default('N/A'),

                        TextEntry::make('engineer.about')
                            ->label('About')
                            ->markdown()
                            ->columnSpanFull()
                            ->default('N/A'),
                    ])
                ])->headerActions([
                    Action::make('View Engineer Details')->url(UserResource::getUrl('view', ['record' => $this->record->engineer])),
                ])->visible(fn (Model $record) => (bool) $record->engineer),

                Section::make('payment')->label('Payment Info')->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('payment.payment_id')
                                ->label('Payment ID'),
                            TextEntry::make('payment.transaction_id')
                                ->label('Transaction ID'),
                            TextEntry::make('payment.status')
                                ->label('Payment Status')
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
                            TextEntry::make('payment.amount')
                                ->label('Amount')
                                ->money(fn ($record) => $record->currency_code),
                            TextEntry::make('payment.application_fee')->label('Application Fee')->money('USD'),
                            TextEntry::make('payment.currency_code')->label('Currency Code')->money('USD'),
                        ]),
                ])->visible(fn (Model $record) => (bool) $record->payment)->headerActions([
                    Action::make('View Payment Details')->url(PaymentResource::getUrl('view', ['record' => $this->record->payment])),
                ]),

            ]);
    }
}
