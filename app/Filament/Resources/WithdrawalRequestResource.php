<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WithdrawalRequestResource\Pages;
use App\Filament\Resources\WithdrawalRequestResource\RelationManagers;
use App\Models\BankDetails;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Filament\Tables\Actions\Action;
use Filament\Forms\Get;
use Filament\Infolists\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Card;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WithdrawalRequestResource extends Resource
{
    protected static ?string $model = WithdrawalRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function getNavigationBadge(): ?string
    {
        $model = static::$model;
        return $model::where('status', 'pending')->count();
    }

    public static function canCreate():bool
    {
        return false;
    }

    public static function canEdit(Model $record):bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

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
                Tables\Columns\TextColumn::make('user.name')->label('Engineer Name')->searchable(),
                Tables\Columns\TextColumn::make('user.wallet.balance')->label('Wallet Balance')->money(),
                Tables\Columns\TextColumn::make('amount')->label('Amount')->money()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested On')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Approve Action with Confirmation Modal
                Tables\Actions\Action::make('approve')
                    ->button()
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Withdrawal Request')
                    ->modalDescription('Are you sure you want to approve this withdrawal request? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, Approve')
                    ->modalIcon('heroicon-o-check-circle')
                    ->action(function (WithdrawalRequest $record) {
                        $record->status = 'approved';
                        $record->save();

                        Notification::make()
                            ->title('Withdrawal request approved successfully')
                            ->success()
                            ->send();
                    }),

                // Reject Action with Reason Modal
                Tables\Actions\Action::make('reject')
                    ->button()
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->placeholder('Please provide a reason for rejecting this withdrawal request')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->modalHeading('Reject Withdrawal Request')
                    ->modalDescription('Please provide a reason for rejecting this withdrawal request.')
                    ->modalSubmitActionLabel('Reject Request')
                    ->action(function (WithdrawalRequest $record, array $data) {
                        try {
                            \DB::beginTransaction();
                            $record->status = 'rejected';
                            $record->rejection_reason = $data['rejection_reason'];
                            $record->save();
                            $record->user->wallet()->increment('balance', $record->amount);
                            DB::commit();
                            Notification::make()
                                ->title('Withdrawal request rejected')
                                ->success()
                                ->send();
                        }catch (\Exception $exception){
                            DB::rollBack();
                            Notification::make()->title('Withdrawal request rejected')->body($exception->getMessage())->danger()->send();
                        }
                    }),

                // View Action
                Tables\Actions\ViewAction::make()
                    ->modal() // Modal Enable
                    ->modalHeading('Withdrawal Request Details')
                    ->modalWidth('4xl')
                    ->infolist(function (Infolist $infolist): Infolist {
                        return $infolist
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        Section::make()
                                            ->schema([
                                                TextEntry::make('amount')
                                                    ->label('Withdrawal Amount')
                                                    ->money('USD')
                                                    ->size(TextEntry\TextEntrySize::Large)
                                                    ->weight(FontWeight::Bold)
                                                    ->color('primary'),

                                                TextEntry::make('status')
                                                    ->label('Request Status')
                                                    ->badge()
                                                    ->size(TextEntry\TextEntrySize::Large)
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        default => 'warning',
                                                    }),
                                            ])
                                            ->columns(2)
                                    ])
                                    ->columnSpan(2),

                                Tabs::make('Details')
                                    ->tabs([
                                        Tabs\Tab::make('Engineer Information')
                                            ->schema([
                                                Card::make()
                                                    ->schema([
                                                        TextEntry::make('user.name')
                                                            ->label('Name')
                                                            ->icon('heroicon-o-user')
                                                            ->weight(FontWeight::Bold),

                                                        TextEntry::make('user.email')
                                                            ->label('Email')
                                                            ->icon('heroicon-o-envelope'),

                                                        TextEntry::make('user.wallet.balance')
                                                            ->label('Current Wallet Balance')
                                                            ->money('USD')
                                                            ->icon('heroicon-o-credit-card'),

                                                        TextEntry::make('user.status')
                                                            ->label('Account Status')
                                                            ->badge()
                                                            ->icon('heroicon-o-shield-check')
                                                            ->color(fn (string $state): string => match ($state) {
                                                                'inactive' => 'danger',
                                                                'active' => 'success',
                                                                default => 'gray',
                                                            })
                                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                                'inactive' => 'Deactivated',
                                                                'active' => 'Active',
                                                                default => $state,
                                                            }),
                                                    ])
                                                    ->columns(2),
                                            ]),

                                        Tabs\Tab::make('Bank Details')
                                            ->schema([
                                                Card::make()
                                                    ->schema([
                                                        TextEntry::make('bankDetails.bank_name')
                                                            ->label('Bank Name')
                                                            ->icon('heroicon-o-building-office'),

                                                        TextEntry::make('bankDetails.account_holder_name')
                                                            ->label('Account Holder')
                                                            ->icon('heroicon-o-identification'),

                                                        TextEntry::make('bankDetails.account_number')
                                                            ->label('Account Number')
                                                            ->icon('heroicon-o-key'),

                                                        TextEntry::make('bankDetails.branch_name')
                                                            ->label('Branch Name')
                                                            ->icon('heroicon-o-map-pin'),

                                                        TextEntry::make('bankDetails.swift_code')
                                                            ->label('SWIFT Code')
                                                            ->icon('heroicon-o-code-bracket'),

                                                        TextEntry::make('bankDetails.ifsc_code')
                                                            ->label('IFSC Code')
                                                            ->icon('heroicon-o-code-bracket-square'),

                                                        TextEntry::make('bankDetails.is_default')
                                                            ->label('Default Account')
                                                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                                            ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                                                            ->color(fn ($state) => $state ? 'success' : 'gray'),
                                                    ])
                                                    ->columns(2),
                                            ]),

                                        Tabs\Tab::make('Request Details')
                                            ->schema([
                                                Card::make()
                                                    ->schema([
                                                        TextEntry::make('created_at')
                                                            ->label('Requested On')
                                                            ->dateTime()
                                                            ->icon('heroicon-o-calendar'),

                                                        TextEntry::make('updated_at')
                                                            ->label('Last Updated')
                                                            ->dateTime()
                                                            ->icon('heroicon-o-clock'),

                                                        TextEntry::make('rejection_reason')
                                                            ->label('Rejection Reason')
                                                            ->visible(fn ($record) => $record->status === 'rejected')
                                                            ->icon('heroicon-o-exclamation-triangle')
                                                            ->color('danger')
                                                            ->columnSpanFull(),
                                                    ])
                                                    ->columns(2),
                                            ]),
                                    ])
                                    ->columnSpan(2),
                            ])
                            ->columns(2);
                    }),
            ])
            ->filters([
                // Status Filter
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->placeholder('Select Status')
                    ->label('Status')
                    ->indicator('Status'),

                // Date Range Filter
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->placeholder('Select start date')
                            ->maxDate(now()),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->placeholder('Select end date')
                            ->maxDate(now())
                            ->minDate(fn (Get $get) => $get('start_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['end_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['start_date'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('From: ' . Carbon::parse($data['start_date'])->toFormattedDateString())
                                ->removeField('start_date');
                        }

                        if ($data['end_date'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('To: ' . Carbon::parse($data['end_date'])->toFormattedDateString())
                                ->removeField('end_date');
                        }

                        return $indicators;
                    })
                    ->label('Date Range'),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWithdrawalRequests::route('/'),
        ];
    }
}
