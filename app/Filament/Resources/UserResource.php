<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('avatar')
                    ->image()
                    ->disk('public')
                    ->directory('user/avatar')
                    ->label('Avatar')
                    ->rules([
                        'image',
                        'max:2048',
                    ])->columnSpanFull(),

                TextInput::make('first_name')
                    ->required()
                    ->string()
                    ->maxLength(100),

                TextInput::make('last_name')
                    ->required()
                    ->string()
                    ->maxLength(100),

                TextInput::make('email')
                    ->required()
                    ->email()
                    ->unique('users', 'email', ignoreRecord: true)
                    ->maxLength(100),

                TextInput::make('password')
                    ->password()
                    ->string()
                    ->required()
                    ->confirmed()
                    ->maxLength(255)
                    ->visible(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),

                TextInput::make('password_confirmation')
                    ->password()
                    ->string()
                    ->required()
                    ->maxLength(255)
                    ->visible(fn ($livewire) => $livewire instanceof Pages\CreateUser),

                Select::make('role')
                    ->options([
                        'admin' => 'Administrator',
                        'customer' => 'Customer',
                        'engineer' => 'Engineer',
                    ])
                    ->required()
                    ->live(),

                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Deactivate',
                    ])
                    ->default('active'),

                TextInput::make('service')
                    ->string()
                    ->maxLength(255)
                    ->visible(fn (Get $get) => $get('role') === 'engineer'),

                Textarea::make('about')
                    ->string()
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => $get('role') === 'engineer'),

                TextInput::make('address')
                    ->string()
                    ->maxLength(255)
                    ->visible(fn (Get $get) => $get('role') === 'engineer' || $get('role') === 'customer'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->formatStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('email')->searchable(),
                TextColumn::make('service'),
                TextColumn::make('role')->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inactive' => 'danger',
                        'active' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'inactive' => 'Deactivate',
                        'active' => 'Active',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')->options([
                    'admin' => 'Administrator',
                    'customer' => 'Customer',
                    'engineer' => 'Engineer',
                ]),
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'inactive' => 'Deactivate',
                ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
