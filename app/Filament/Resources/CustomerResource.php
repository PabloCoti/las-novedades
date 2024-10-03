<?php

namespace App\Filament\Resources;

use App\Models\Customer;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;

use Illuminate\View\ComponentSlot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model          = Customer::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Tienda';

    public static function getNavigationLabel(): string
    {
        return 'Clientes';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Clientes';
    }

    public static function getModelLabel(): string
    {
        return 'cliente';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\TextInput::make('tributary_number')
                            ->label('NIT')
                            ->required()
                            ->numeric(),
                        Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        Components\TextInput::make('email')
                            ->label('Correo electrónico')
                            ->required()
                            ->email(),
                        Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->required()
                            ->tel(),
                        Components\Toggle::make('special')
                            ->label('Cliente especial')
                            ->default(false)
                            ->inline(false),
                    ])
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Columns\TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable(),
                Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                Columns\BooleanColumn::make('special')
                    ->label('Cliente especial')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filters\SelectFilter::make('active')
                    ->label('Activo')
                    ->default(true)
                    ->options([
                        true  => 'Activo',
                        false => 'Inactivo',
                    ]),
                Filters\SelectFilter::make('special')
                    ->label('Cliente especial')
                    ->options([
                        true  => 'Sí',
                        false => 'No',
                    ]),
            ])->hiddenFilterIndicators()
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),
                    Actions\Action::make('deactivate')
                        ->label('Desactivar')
                        ->action(fn ($record) => $record->update(['active' => false]))
                        ->hidden(fn ($record) => !$record->active)
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->color('danger'),
                ]),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
