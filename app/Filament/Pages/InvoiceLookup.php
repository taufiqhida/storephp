<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class InvoiceLookup extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static string $view = 'filament.pages.invoice-lookup';
    protected static ?string $navigationLabel = 'Invoice Lookup';
    protected static ?string $title = 'Invoice Lookup';
    protected static ?string $navigationGroup = 'Toko';
    protected static ?int $navigationSort = 4;

    public string $phone = '';
    public string $status = 'semua';
    public string $from = '';
    public string $to = '';
    public bool $searched = false;

    public function search(): void
    {
        $this->searched = true;
    }

    public function getOrdersProperty(): Collection
    {
        if (!$this->searched || empty(trim($this->phone))) {
            return collect();
        }

        $query = Order::where('customer_phone', $this->phone)
            ->with(['items', 'paymentMethod']);

        if ($this->status !== 'semua') {
            if ($this->status === 'proses') {
                $query->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped']);
            } elseif ($this->status === 'selesai') {
                $query->where('status', 'completed');
            } elseif ($this->status === 'batal') {
                $query->where('status', 'cancelled');
            }
        }

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
        return $this->orders->where('status', 'completed')->sum('total');
    }

    public static function canAccess(): bool
    {
        $admin = auth('admin')->user();
        return $admin && $admin->hasPermission('manage_orders');
    }
}
