<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Models\Store;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use Filament\Forms\Components;
use Filament\Resources\Resource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model           = User::class;
    protected static ?string $navigationIcon  = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Configuración';

    public static function getNavigationLabel(): string
    {
        return 'Usuarios';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Usuarios';
    }

    public static function getModelLabel(): string
    {
        return 'usuario';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\Grid::make(['sm' => 1, 'md' => 2])
                            ->schema([
                                Components\Select::make('store_id')
                                    ->label('Sucursal')
                                    ->options(Store::all()->pluck('name', 'id'))
                                    ->disablePlaceholderSelection()
                                    ->required(),
                                Components\TextInput::make('name')
                                    ->label('Nombre')
                                    ->required(),
                                Components\TextInput::make('email')
                                    ->label('Correo')
                                    ->required(),
                                // Components\TextInput::make('password')
                                //     ->label('Contraseña')
                                //     ->revealable()
                                //     ->password()
                                //     ->required(),
                                Forms\Components\Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->disablePlaceholderSelection()
                                    ->searchable()
                                    ->preload(),
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('store.name')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable(),
                Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Columns\TextColumn::make('roles.name')
                    ->label('Rol')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),
                ])
            ])
            ->bulkActions([]);
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
