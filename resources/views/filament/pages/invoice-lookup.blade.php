<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Search Form --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Nomor HP Pelanggan</label>
                    <input type="tel" wire:model="phone" placeholder="Contoh: 087739612610"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Dari Tanggal</label>
                    <input type="date" wire:model="from"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Sampai Tanggal</label>
                    <input type="date" wire:model="to"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                </div>
            </div>
            <div class="mt-4 flex gap-3">
                <x-filament::button wire:click="search" icon="heroicon-m-magnifying-glass">
                    Cari Invoice
                </x-filament::button>
            </div>
        </div>

        @if($searched)
            @if($this->orders->count() > 0)
                {{-- Summary --}}
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 rounded-lg bg-primary-50 dark:bg-primary-900/20">
                            <div class="text-2xl font-black text-primary-600">{{ $this->orders->count() }}</div>
                            <div class="text-xs text-gray-500 mt-1">Total Pesanan</div>
                        </div>
                        <div class="text-center p-4 rounded-lg bg-green-50 dark:bg-green-900/20">
                            <div class="text-2xl font-black text-green-600">{{ $this->orders->where('status', 'completed')->count() }}</div>
                            <div class="text-xs text-gray-500 mt-1">Selesai</div>
                        </div>
                        <div class="text-center p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20">
                            <div class="text-2xl font-black text-amber-600">Rp {{ number_format($this->totalRevenue, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500 mt-1">Total Belanja</div>
                        </div>
                    </div>
                </div>

                {{-- Print Button --}}
                <div class="flex justify-end">
                    <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600">
                        <x-heroicon-m-printer class="w-4 h-4" />
                        Cetak / Export PDF
                    </button>
                </div>

                {{-- Order Cards --}}
                @foreach($this->orders as $order)
                    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                        {{-- Header --}}
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <span class="font-bold text-primary-600 dark:text-primary-400">{{ $order->order_code }}</span>
                                <span class="text-xs text-gray-500 ml-2">{{ $order->ordered_at?->format('d M Y, H:i') }}</span>
                            </div>
                            <div>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                    @if($order->status === 'completed') bg-green-100 text-green-700
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-700
                                    @elseif($order->status === 'pending') bg-amber-100 text-amber-700
                                    @else bg-blue-100 text-blue-700
                                    @endif">
                                    {{ $order->status_label }}
                                </span>
                            </div>
                        </div>

                        {{-- Body --}}
                        <div class="p-6">
                            <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">
                                <strong>{{ $order->customer_name }}</strong> — {{ $order->customer_phone }}
                            </div>

                            <table class="w-full text-sm mb-4">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700 text-xs text-gray-500 uppercase">
                                        <th class="text-left py-2">Produk</th>
                                        <th class="text-center py-2">Qty</th>
                                        <th class="text-right py-2">Harga</th>
                                        <th class="text-right py-2">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr class="border-b border-gray-100 dark:border-gray-800">
                                            <td class="py-2">
                                                {{ $item->product_name }}
                                                @if($item->variant_name)
                                                    <span class="text-xs text-gray-400">({{ $item->variant_name }})</span>
                                                @endif
                                            </td>
                                            <td class="text-center py-2">{{ $item->quantity }}</td>
                                            <td class="text-right py-2">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                            <td class="text-right py-2 font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Subtotal</span>
                                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                                </div>
                                @if($order->admin_fee > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Biaya Admin</span>
                                        <span>Rp {{ number_format($order->admin_fee, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($order->discount_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Diskon</span>
                                        <span class="text-green-600">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($order->unique_code > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Kode Unik</span>
                                        <span>+Rp {{ number_format($order->unique_code, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between font-bold text-base pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <span>TOTAL</span>
                                    <span class="text-primary-600">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            @if($order->paymentMethod)
                                <div class="mt-3 text-xs text-gray-500">
                                    💳 {{ $order->paymentMethod->name }} ({{ ucfirst($order->paymentMethod->type) }})
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-12 text-center">
                    <div class="text-4xl mb-3">🔍</div>
                    <div class="font-bold text-gray-700 dark:text-gray-300">Tidak Ada Pesanan</div>
                    <div class="text-sm text-gray-500 mt-1">
                        Tidak ditemukan pesanan untuk nomor <strong>{{ $phone }}</strong>
                        @if($from || $to)
                            pada tanggal {{ $from ?: '...' }} s/d {{ $to ?: '...' }}
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>

    @push('styles')
        <style>
            @media print {
                .fi-sidebar, .fi-topbar, .fi-header, .fi-footer, button { display: none !important; }
                .fi-main { padding: 0 !important; }
            }
        </style>
    @endpush
</x-filament-panels::page>
