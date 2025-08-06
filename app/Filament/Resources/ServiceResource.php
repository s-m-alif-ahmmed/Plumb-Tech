<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?int $navigationSort = 8;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')->schema([
                    TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    TextInput::make('price')->required()->numeric()->minValue(0)->columnSpanFull(),
                    FileUpload::make('thumbnail')
                        ->required()
                        ->image()
                        ->maxSize(2048)
                        ->columnSpanFull()
                        ->directory('services/thumbnails')
                        ->visibility('public')
                        ->imageEditor()
                        ->deleteUploadedFileUsing(function ($state) {
                            Storage::disk('public')->delete($state);
                        }),
                    Forms\Components\Select::make('skills')
                        ->multiple()
                        ->relationship('skills','name')
                        ->preload()
                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('name')->required()->maxLength(255),
                        ])->columnSpanFull()
                ])->columns(2),
                Forms\Components\Section::make('Questions')->schema([
                    Repeater::make('questions')
                    ->relationship()->schema([
                           TextInput::make('question_text')->required()->maxLength(255)->columnSpanFull(),
                            Repeater::make('answers')->relationship()->schema([
                                TextInput::make('answer_text')->required()->maxLength(255)->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->defaultItems(1)->label('Answers')
                        ])->collapsible()->defaultItems(1)->label('Questions')
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')->width(50)->height(50),
                Tables\Columns\TextColumn::make('title')->limit(100),
                Tables\Columns\TextColumn::make('price')->limit(100),
                Tables\Columns\TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('Questions'),
                Tables\Columns\ToggleColumn::make('status')->afterStateUpdated(function ($state) {
                    Notification::make()->title('Service Status changed successfully.')->success()->send();
                })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()->after(function ($record) {
                    if ($record->thumbnail && Storage::disk('public')->exists($record->thumbnail)) {
                        Storage::disk('public')->delete($record->thumbnail);
                    }
                })->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Service has been updated successfully.')
                ),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
            'view' => Pages\ViewService::route('/{record}'),
        ];
    }
}
