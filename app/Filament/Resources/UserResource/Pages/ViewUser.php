<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('User Profile')
                    ->schema([
                        ImageEntry::make('avatar')
                            ->disk('public')
                            ->getStateUsing(fn($record)=>Str::contains($record->avatar, 'public/') ? str_replace('public/', '', $record->avatar) : $record->avatar)
                            ->circular()
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->first_name . ' ' . $record->last_name) . '&color=7F9CF5&background=EBF4FF')
                            ->columnSpan(['md' => 2]),

                        TextEntry::make('first_name')
                            ->label('First Name'),

                        TextEntry::make('last_name')
                            ->label('Last Name'),

                        TextEntry::make('email')
                            ->icon('heroicon-o-envelope'),

                        TextEntry::make('role')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'admin' => 'primary',
                                'engineer' => 'success',
                                'customer' => 'info',
                                default => 'gray',
                            }),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'inactive' => 'danger',
                                'active' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'inactive' => 'Deactivate',
                                'active' => 'Active',
                                default => $state,
                            }),
                    ])
                    ->columns(['md' => 2]),

                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('address')
                            ->icon('heroicon-o-map-pin')
                            ->default('N/A'),

                        TextEntry::make('service')
                            ->icon('heroicon-o-wrench')
                            ->default('N/A'),

                        TextEntry::make('about')
                            ->markdown()
                            ->columnSpanFull()
                             ->default('N/A'),
                    ])
                    ->columns(['md' => 2])
                    ->visible(fn ($record) => $record->role === 'engineer'),

                Section::make('Account Information')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Joined On')
                            ->date('F j, Y')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('F j, Y \a\t g:i A')
                            ->icon('heroicon-o-clock'),
                    ])
                    ->columns(['md' => 2]),
            ]);
    }
}
