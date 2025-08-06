<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportIssuesResource\Pages;
use App\Models\ReportIssues;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Card;
use Filament\Infolists\Components\Tabs;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\DB;

class ReportIssuesResource extends Resource
{
    protected static ?string $model = ReportIssues::class;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client Name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $query) use ($search) {
                            $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('engineer.name')
                    ->label('Engineer Name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('engineer', function (Builder $query) use ($search) {
                            $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->limit(100)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service_title')
                    ->limit(100)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reported Date')
                    ->date('M d, Y')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_resolved')
                    ->label('Resolved')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_resolved')
                    ->label('Resolution Status')
                    ->options([
                        '0' => 'Unresolved',
                        '1' => 'Resolved',
                    ])
                    ->placeholder('All Statuses'),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('From Date'),
                        DatePicker::make('created_until')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'From ' . \Carbon\Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Until ' . \Carbon\Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modal() // Modal Enable
                    ->modalHeading('Issue Report Details')
                    ->modalWidth('4xl')
                    ->infolist(function (Infolist $infolist): Infolist {
                        return $infolist
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextEntry::make('type')
                                            ->label('Issue Type')
                                            ->size(TextEntry\TextEntrySize::Large)
                                            ->weight(FontWeight::Bold)
                                            ->color('primary'),

                                        TextEntry::make('is_resolved')
                                            ->label('Status')
                                            ->badge()
                                            ->formatStateUsing(fn (bool $state): string => $state ? 'Resolved' : 'Unresolved')
                                            ->color(fn (bool $state): string => $state ? 'success' : 'warning'),

                                        TextEntry::make('created_at')
                                            ->label('Reported On')
                                            ->dateTime()
                                            ->icon('heroicon-o-calendar'),
                                    ])
                                    ->columns(3),

                                Section::make()
                                    ->schema([
                                        TextEntry::make('description')
                                            ->label('Issue Description')
                                            ->markdown()
                                            ->icon('heroicon-o-exclamation-circle'),
                                    ]),

                                Tabs::make('Details')
                                    ->tabs([
                                        Tabs\Tab::make('Client Information')
                                            ->schema([
                                                Card::make()
                                                    ->schema([
                                                        TextEntry::make('user.first_name')
                                                            ->label('First Name')
                                                            ->icon('heroicon-o-user'),

                                                        TextEntry::make('user.last_name')
                                                            ->label('Last Name')
                                                            ->icon('heroicon-o-user'),

                                                        TextEntry::make('user.email')
                                                            ->label('Email')
                                                            ->icon('heroicon-o-envelope'),

                                                        TextEntry::make('user.phone')
                                                            ->label('Phone')
                                                            ->icon('heroicon-o-phone'),
                                                    ])
                                                    ->columns(2),
                                            ]),
                                        Tabs\Tab::make('Engineer Information')
                                            ->schema([
                                                Card::make()
                                                    ->schema([
                                                        TextEntry::make('engineer.first_name')
                                                            ->label('First Name')
                                                            ->icon('heroicon-o-user'),

                                                        TextEntry::make('engineer.last_name')
                                                            ->label('Last Name')
                                                            ->icon('heroicon-o-user'),

                                                        TextEntry::make('engineer.email')
                                                            ->label('Email')
                                                            ->icon('heroicon-o-envelope'),

                                                        TextEntry::make('engineer.phone')
                                                            ->label('Phone')
                                                            ->icon('heroicon-o-phone'),
                                                    ])
                                                    ->columns(2),
                                            ]),

                                        Tabs\Tab::make('Service Information')
                                            ->schema([
                                                Card::make()
                                                    ->schema([
                                                        ImageEntry::make('discussionRequest.service.thumbnail')->disk('public')->label('Thumbnail'),
                                                        TextEntry::make('discussionRequest.service.title')
                                                            ->label('Service Title')
                                                            ->icon('heroicon-o-briefcase')
                                                            ->columnSpanFull(),
                                                        TextEntry::make('discussionRequest.service.price')
                                                            ->label('Service Price')
                                                            ->money('USD')
                                                            ->icon('heroicon-o-currency-dollar'),
                                                    ])
                                                    ->columns(2),
                                            ]),

                                        Tabs\Tab::make('Payment Information')
                                            ->schema([
                                                Card::make()
                                                    ->schema([
                                                        TextEntry::make('discussionRequest.payment.payment_id')
                                                            ->label('Payment ID')
                                                            ->icon('heroicon-o-identification'),

                                                        TextEntry::make('discussionRequest.payment.transaction_id')
                                                            ->label('Transaction ID')
                                                            ->icon('heroicon-o-key'),

                                                        TextEntry::make('discussionRequest.payment.status')
                                                            ->label('Payment Status')
                                                            ->badge()
                                                            ->icon('heroicon-o-credit-card')
                                                            ->color(fn (string $state): string => match ($state) {
                                                                'completed' => 'success',
                                                                'pending' => 'warning',
                                                                'failed' => 'danger',
                                                                default => 'gray',
                                                            }),

                                                        TextEntry::make('discussionRequest.payment.amount')
                                                            ->label('Amount')
                                                            ->money(fn ($record) => $record->payment->currency_code ?? 'USD')
                                                            ->icon('heroicon-o-banknotes'),

                                                        TextEntry::make('discussionRequest.payment.application_fee')
                                                            ->label('Application Fee')
                                                            ->money('USD')
                                                            ->icon('heroicon-o-receipt-percent'),

                                                        TextEntry::make('discussionRequest.payment.currency_code')
                                                            ->label('Currency')
                                                            ->icon('heroicon-o-currency-dollar'),
                                                    ])
                                                    ->columns(2),
                                            ])->default('N/A'),
                                    ]),
                            ]);
                    }),
                Tables\Actions\Action::make('resolve')
                    ->label('Mark as Resolved')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark Issue as Resolved')
                    ->modalDescription('Are you sure you want to mark this issue as resolved?')
                    ->modalSubmitActionLabel('Yes, resolve it')
                    ->action(function (ReportIssues $record) {
                        $record->update(['is_resolved' => true]);
                        Notification::make()->success()->title('Issue resolved successfully')->send();
                    })
                    ->visible(fn (ReportIssues $record): bool => !$record->is_resolved),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('resolveMultiple')
                        ->label('Resolve Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update(['is_resolved' => true]);
                            });
                            Notification::make()->success()->title('Selected issues resolved successfully')->send();
                        })
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportIssues::route('/'),
        ];
    }
}
