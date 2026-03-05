<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Order;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Actions\Action;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationLabel = 'Órdenes';
    protected static ?string $modelLabel = 'Orden';
    protected static ?string $pluralModelLabel = 'Órdenes';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Cliente')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Teléfono')->searchable(),
                Tables\Columns\TextColumn::make('total_cents')
                    ->label('Total')
                    ->money('MXN', divideBy: 100)
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_method')->label('Pago')->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('Status')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Action::make('mark_paid')
                    ->label('Marcar como pagado')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) =>
                        $record->payment_method->value === 'transfer'
                        && $record->status->value !== OrderStatus::Paid->value
                    )
                    ->action(function (Order $record) {
                        $record->markPaid();
                    }),

                Action::make('cancel')
                    ->label('Cancelar')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status->value !== OrderStatus::Cancelled->value)
                    ->action(fn (Order $record) => $record->markCancelled()),

                Action::make('proof')
                    ->label('Ver comprobante')
                    ->visible(fn (Order $record) => !blank($record->transfer_proof_path))
                    ->url(fn (Order $record) => Storage::disk('public')->url($record->transfer_proof_path), true),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos de la orden')
                ->schema([
                    Forms\Components\TextInput::make('name')->disabled(),
                    Forms\Components\TextInput::make('phone')->disabled(),
                    Forms\Components\TextInput::make('email')->disabled(),
                    Forms\Components\TextInput::make('delivery')->disabled(),
                    Forms\Components\Textarea::make('address')->disabled(),
                    Forms\Components\Textarea::make('notes')->disabled(),

                    Forms\Components\TextInput::make('payment_method')->disabled(),
                    Forms\Components\TextInput::make('status')->disabled(),

                    Forms\Components\TextInput::make('total_cents')->label('Total (centavos)')->disabled(),
                ])->columns(2),

            Forms\Components\Section::make('Mercado Pago')
                ->schema([
                    Forms\Components\TextInput::make('mp_preference_id')->disabled(),
                    Forms\Components\TextInput::make('mp_payment_id')->disabled(),
                    Forms\Components\TextInput::make('mp_status')->disabled(),
                ])->columns(3),

            Forms\Components\Section::make('Transferencia')
                ->schema([
                    Forms\Components\TextInput::make('transfer_proof_path')->disabled(),
                    Forms\Components\DateTimePicker::make('transfer_submitted_at')->disabled(),
                    Forms\Components\DateTimePicker::make('paid_at')->disabled(),
                    Forms\Components\DateTimePicker::make('cancelled_at')->disabled(),
                ])->columns(2),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}