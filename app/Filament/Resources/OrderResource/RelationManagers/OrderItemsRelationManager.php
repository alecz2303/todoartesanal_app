<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_id')->label('Producto ID'),
                Tables\Columns\TextColumn::make('name_snapshot')->label('Nombre'),
                Tables\Columns\TextColumn::make('price_cents_snapshot')
                    ->label('Precio')
                    ->money('MXN', divideBy: 100),
                Tables\Columns\TextColumn::make('qty')->label('Qty'),
            ])
            ->paginated(false);
    }
}