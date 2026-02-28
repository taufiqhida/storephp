<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\StoreSetting;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class InvoiceLookup extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static string $view = 'filament.pages.invoice-lookup';
    protected static ?string $navigationLabel = 'Invoice Pelanggan';
    protected static ?string $title = 'Invoice Pelanggan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 1;

    public string $phone = '';
    public string $from = '';
    public string $to = '';
    public bool $searched = false;

    public function search(): void
    {
        $this->searched = true;
    }

    public function getOrdersProperty(): Collection
    {
        if (!$this->searched || empty($this->phone)) {
            return collect();
        }

        $query = Order::where('customer_phone', $this->phone)
            ->with(['items', 'paymentMethod']);

        if (!empty($this->from)) {
            $query->whereDate('ordered_at', '>=', $this->from);
        }
        if (!empty($this->to)) {
            $query->whereDate('ordered_at', '<=', $this->to);
        }

        return $query->orderBy('ordered_at', 'desc')->get();
    }

    public function getTotalRevenueProperty(): float
    {
        return $this->orders->sum('total');
    }

    public static function canAccess(): bool
    {
        $admin = auth('admin')->user();
        return $admin && $admin->hasPermission('manage_orders');
    }
}
