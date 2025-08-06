<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use App\Services\PaypalPaymentService;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('paymentBy.name')->label('Client Name'),
                TextColumn::make('paymentFor.name')->label('Engineer Name'),
                TextColumn::make('discussionRequest.service.title'),
                TextColumn::make('application_fee'),
                TextColumn::make('amount')->label('Total Amount'),
                TextColumn::make('transaction_id')->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->color(function ($state) {
                        return match ($state) {
                            'pending' => 'warning',
                            'progressing' => 'info',
                            'completed' => 'success',
                            'refunded' => 'danger',
                            default => 'gray',
                        };
                    })
            ])
            ->filters([
                Filter::make('created_between')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Start Date'),
                        DatePicker::make('created_until')
                            ->label('End Date'),
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
                    }),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'refunded' => 'Refunded',
                    ])
                    ->label('Status')
                    ->placeholder('All Statuses')
                    ->multiple()
            ])
            ->actions([
                Action::make('Refund')
                    ->label('Refund')
                    ->icon('heroicon-o-receipt-refund')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->button()
                    ->visible(fn(Payment $payment) => $payment->transaction_id && $payment->status === 'completed')
                    ->form([
//                        TextInput::make('amount')
//                            ->label('Refund Amount')
//                            ->required()
//                            ->numeric()
//                            ->default(fn(Payment $payment) => $payment->amount)
//                            ->lte(fn(Payment $payment) => $payment->amount)
//                            ->gt(0),
                        Textarea::make('note')
                            ->required()
                            ->label('Refund Note')
                            ->placeholder('Reason for refund')
                    ])
                    ->action(function (array $data, Payment $payment, PaypalPaymentService $paymentService) {
                        try {
                            $note = $data['note'] ?? 'Refund Payment';
                            $paymentService->refund($payment->transaction_id,$payment->payment_id, $payment->amount, $note);
                            $payment->status = 'refunded';
                            $payment->save();

                            Notification::make()
                                ->title('Payment refunded successfully')
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Failed to refund payment')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })


            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([

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
            'index' => Pages\ListPayments::route('/'),
//            'create' => Pages\CreatePayment::route('/create'),
//            'edit' => Pages\EditPayment::route('/{record}/edit'),
             'view' =>  Pages\ViewPayment::route('/{record}'),
        ];
    }
}
