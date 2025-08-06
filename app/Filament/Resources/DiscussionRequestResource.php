<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscussionRequestResource\Pages;
use App\Filament\Resources\DiscussionRequestResource\RelationManagers;
use App\Models\DiscussionRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DiscussionRequestResource extends Resource
{
    protected static ?string $model = DiscussionRequest::class;

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool{
        return false;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->latest();
    }

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Forms\Components\Select::make('engineer_id')
                    ->relationship('engineer', 'id'),
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'title'),
                Forms\Components\TextInput::make('service_title')
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\Textarea::make('question_answer')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('images')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Client Name'),
                Tables\Columns\TextColumn::make('engineer.name')->label('Engineer Name'),
                Tables\Columns\TextColumn::make('service.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->color(function ($state) {
                    return match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    };
                }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
//                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDiscussionRequests::route('/'),
            'view' => Pages\ViewDiscussionRequest::route('/{record}'),
        ];
    }
}
