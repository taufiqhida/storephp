<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class DailySalesWidget extends Widget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.daily-sales-widget';

    public function getDailySales(): array
    {
        $rows = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $orders = Order::whereDate('ordered_at', $date)
                ->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed']);

            $rows[] = [
                'hari'       => $date->isoFormat('ddd, D MMM'),
                'isToday'    => $date->isToday(),
                'jumlah'     => $orders->count(),
                'pendapatan' => $orders->sum('total'),
            ];
        }
        return $rows;
    }
}
