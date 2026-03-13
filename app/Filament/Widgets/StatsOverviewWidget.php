<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // === Data Penjualan ===
        $todayRevenue = Order::whereDate('ordered_at', today())
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])
            ->sum('total');

        $todayOrders = Order::whereDate('ordered_at', today())->count();

        $weekRevenue = Order::whereBetween('ordered_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])
            ->sum('total');

        $monthRevenue = Order::whereMonth('ordered_at', now()->month)
            ->whereYear('ordered_at', now()->year)
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])
            ->sum('total');

        $pendingOrders = Order::where('status', 'pending')->count();
        $totalProducts = Product::where('is_active', true)->count();

        $totalRevenue = Order::whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])
            ->sum('total');

        // Chart 7 hari terakhir (data real)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $chartData[] = (float) Order::whereDate('ordered_at', now()->subDays($i))
                ->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])
                ->sum('total');
        }

        // === Top Buyer Hari Ini ===
        $topToday = Order::whereDate('ordered_at', today())
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])
            ->select('customer_phone', 'customer_name', DB::raw('COUNT(*) as cnt'))
            ->groupBy('customer_phone', 'customer_name')
            ->orderByDesc('cnt')
            ->first();

        $topBuyerLabel = $topToday
            ? substr($topToday->customer_phone, 0, 4) . '****  (' . $topToday->cnt . 'x)'
            : 'Belum ada';

        return [
            Stat::make('💰 Total Pendapatan', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Sepanjang waktu')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($chartData),

            Stat::make('📅 Hari Ini', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description($todayOrders . ' pesanan masuk hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('📊 Minggu Ini', 'Rp ' . number_format($weekRevenue, 0, ',', '.'))
                ->description('Pendapatan minggu ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('📈 Bulan Ini', 'Rp ' . number_format($monthRevenue, 0, ',', '.'))
                ->description(now()->isoFormat('MMMM YYYY'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('⏳ Pesanan Pending', number_format($pendingOrders))
                ->description('Perlu segera dikonfirmasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'danger' : 'success'),

            Stat::make('🏆 Top Buyer Hari Ini', $topBuyerLabel)
                ->description('Nomor HP paling aktif hari ini')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
        ];
    }
}
