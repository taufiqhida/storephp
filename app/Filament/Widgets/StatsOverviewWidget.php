<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayRevenue = Order::whereDate('ordered_at', today())
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])
            ->sum('total');

        $todayOrders = Order::whereDate('ordered_at', today())->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalProducts = Product::where('is_active', true)->count();
        $totalRevenue = Order::whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])
            ->sum('total');

        return [
            Stat::make('Pendapatan Hari Ini', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description($todayOrders . ' pesanan hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 8, 9]),

            Stat::make('Pesanan Menunggu', number_format($pendingOrders))
                ->description('Perlu segera dikonfirmasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Produk Aktif', number_format($totalProducts))
                ->description('Katalog yang tersedia')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Total Omzet', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Sepanjang waktu')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->chart([2, 4, 8, 12, 16, 20]),
        ];
    }
}
