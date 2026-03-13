<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopBuyersWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = '📱 Top Pembeli (Nomor HP Terbanyak Order)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->select(
                        'customer_phone',
                        DB::raw('MAX(customer_name) as customer_name'),
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw('SUM(total) as total_spent')
                    )
                    ->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])
                    ->groupBy('customer_phone')
                    ->orderByDesc('total_orders')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Nomor HP')
                    ->formatStateUsing(function ($state) {
                        // Masking: tampilkan 4 digit pertama dan 2 terakhir
                        if (strlen($state) > 6) {
                            return substr($state, 0, 4) . str_repeat('*', strlen($state) - 6) . substr($state, -2);
                        }
                        return $state;
                    })
                    ->weight('bold')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Nama Customer')
                    ->formatStateUsing(function ($state) {
                        // Masking nama
                        if (strlen($state) <= 2) {
                            return $state[0] . '*';
                        }
                        return $state[0] . str_repeat('*', min(5, strlen($state) - 2)) . substr($state, -1);
                    }),

                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Total Pesanan')
                    ->badge()
                    ->color('success')
                    ->alignCenter()
                    ->suffix('x'),

                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Belanja')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->weight('bold')
                    ->color('success'),
            ])
            ->paginated(false);
    }
}
