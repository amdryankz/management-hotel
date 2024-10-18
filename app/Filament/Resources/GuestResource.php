<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestResource\Pages;
use App\Filament\Resources\GuestResource\RelationManagers;
use App\Models\Guest;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuestResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2) // Membagi form dalam 2 kolom
                    ->schema([
                        TextInput::make('nik')
                            ->label('NIK')
                            ->unique()
                            ->nullable()
                            ->placeholder('Enter NIK'),

                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->placeholder('Enter full name'),

                        Select::make('gender')
                            ->label('Gender')
                            ->options([
                                'Laki-laki' => 'Laki-laki',
                                'Perempuan' => 'Perempuan',
                            ])
                            ->nullable()
                            ->placeholder('Select gender'),

                        TextInput::make('place_of_birth')
                            ->label('Place of Birth')
                            ->nullable()
                            ->placeholder('Enter place of birth'),

                        TextInput::make('date_of_birth')
                            ->label('Date of Birth')
                            ->nullable()
                            ->type('date'),

                        TextInput::make('address')
                            ->label('Address')
                            ->nullable()
                            ->placeholder('Enter address'),

                        TextInput::make('city')
                            ->label('City')
                            ->nullable()
                            ->placeholder('Enter city'),

                        TextInput::make('province')
                            ->label('Province')
                            ->nullable()
                            ->placeholder('Enter province'),

                        Select::make('religion')
                            ->label('Religion')
                            ->options([
                                'Islam' => 'Islam',
                                'Protestan' => 'Protestan',
                                'Katolik' => 'Katolik',
                                'Budha' => 'Budha',
                                'Hindu' => 'Hindu',
                                'Khonghucu' => 'Khonghucu',
                            ])
                            ->nullable()
                            ->placeholder('Select religion'),

                        Select::make('marital_status')
                            ->label('Marital Status')
                            ->options([
                                'Belum Kawin' => 'Belum Kawin',
                                'Kawin' => 'Kawin',
                                'Cerai Hidup' => 'Cerai Hidup',
                                'Cerai Mati' => 'Cerai Mati',
                            ])
                            ->nullable()
                            ->placeholder('Select marital status'),

                        TextInput::make('occupation')
                            ->label('Occupation')
                            ->nullable()
                            ->placeholder('Enter occupation'),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->required()
                            ->placeholder('Enter phone number'),

                        TextInput::make('email')
                            ->label('Email')
                            ->nullable()
                            ->placeholder('Enter email address')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nik')
                    ->label('NIK')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('gender')
                    ->label('Gender')
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label('Gender')
                    ->options([
                        'Laki-laki' => 'Laki-laki',
                        'Perempuan' => 'Perempuan',
                    ]),
                SelectFilter::make('marital_status')
                    ->label('Marital Status')
                    ->options([
                        'Belum Kawin' => 'Belum Kawin',
                        'Kawin' => 'Kawin',
                        'Cerai Hidup' => 'Cerai Hidup',
                        'Cerai Mati' => 'Cerai Mati',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListGuests::route('/'),
            'create' => Pages\CreateGuest::route('/create'),
            'edit' => Pages\EditGuest::route('/{record}/edit'),
        ];
    }
}
