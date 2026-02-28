@extends('layouts.app')

@section('title', 'Invoice Pembelian - ' . ($setting->store_name ?? 'Taufiq Store'))

@section('head')
    <style>
        .invoice-wrap {
            max-width: 900px;
            margin: 0 auto;
        }

        .invoice-header {
            margin-bottom: 2rem;
        }

        .invoice-header h1 {
            font-size: 1.75rem;
            font-weight: 900;
            margin-bottom: 0.35rem;
        }

        .invoice-header p {
            color: var(--muted);
            font-size: 0.9rem;
        }

        .search-card {
            background: var(--white);
            border: 1px solid var(--border-2);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-xs);
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .search-field {
            flex: 1;
            min-width: 180px;
        }

        .search-field label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--mid);
            margin-bottom: 0.35rem;
        }

        .search-field input {
            width: 100%;
            height: 42px;
            padding: 0 0.85rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-family: inherit;
            transition: all 0.2s;
            outline: none;
        }

        .search-field input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, .1);
        }

        /* Order cards */
        .order-card {
            background: var(--white);
            border: 1px solid var(--border-2);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-xs);
            margin-bottom: 1.25rem;
            transition: all 0.2s;
        }

        .order-card:hover {
            box-shadow: var(--shadow-sm);
        }

        .order-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            background: var(--light);
            border-bottom: 1px solid var(--border-2);
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .order-code {
            font-weight: 800;
            font-size: 0.92rem;
            color: var(--primary-d);
        }

        .order-date {
            font-size: 0.75rem;
            color: var(--muted);
        }

        .order-status {
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .status-completed {
            background: var(--primary-l);
            color: var(--primary-d);
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-processing {
            background: #e0e7ff;
            color: #4338ca;
        }

        .status-shipped {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #dc2626;
        }

        .order-card-body {
            padding: 1rem 1.25rem;
        }

        .order-items {
            margin-bottom: 0.75rem;
        }

        .order-item-row {
            display: flex;
            justify-content: space-between;
            padding: 0.4rem 0;
            font-size: 0.84rem;
            border-bottom: 1px dashed var(--border-2);
        }

        .order-item-row:last-child {
            border-bottom: none;
        }

        .order-item-name {
            color: var(--dark);
            font-weight: 600;
        }

        .order-item-detail {
            color: var(--muted);
            font-size: 0.78rem;
        }

        .order-item-price {
            font-weight: 700;
            color: var(--primary-d);
            white-space: nowrap;
        }

        .order-summary {
            border-top: 1.5px solid var(--border);
            padding-top: 0.75rem;
            margin-top: 0.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.82rem;
            padding: 0.2rem 0;
        }

        .summary-row.total {
            font-weight: 800;
            font-size: 1rem;
            color: var(--primary-d);
            padding-top: 0.5rem;
            border-top: 1.5px solid var(--border);
            margin-top: 0.35rem;
        }

        .order-payment {
            font-size: 0.78rem;
            color: var(--muted);
            margin-top: 0.5rem;
        }

        /* Print */
        .print-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .print-bar .result-info {
            font-size: 0.88rem;
            color: var(--mid);
            font-weight: 600;
        }

        @media print {

            .navbar,
            .footer,
            .search-card,
            .print-bar,
            .flash-bar {
                display: none !important;
            }

            .page-body {
                padding: 0 !important;
            }

            .order-card {
                break-inside: avoid;
                box-shadow: none !important;
                border: 1px solid #ddd;
            }
        }

        @media (max-width: 480px) {
            .search-form {
                flex-direction: column;
            }

            .search-field {
                min-width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="invoice-wrap">
        <div class="invoice-header">
            <h1>🧾 Invoice Pembelian</h1>
            <p>Masukkan nomor HP untuk melihat riwayat pesanan Anda</p>
        </div>

        <div class="search-card">
            <form action="{{ route('invoice') }}" method="GET" class="search-form">
                <div class="search-field" style="flex:2;">
                    <label>Nomor HP / WhatsApp</label>
                    <input type="tel" name="phone" value="{{ request('phone') }}" placeholder="Contoh: 087739612610"
                        required>
                </div>
                <div class="search-field">
                    <label>Dari Tanggal</label>
                    <input type="date" name="from" value="{{ request('from') }}">
                </div>
                <div class="search-field">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ request('to') }}">
                </div>
                <button type="submit" class="btn btn-primary" style="height:42px;white-space:nowrap;">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
        </div>

        @if($searched)
            @if($orders->count() > 0)
                <div class="print-bar">
                    <div class="result-info">
                        📋 Ditemukan <strong>{{ $orders->count() }}</strong> pesanan
                        @if(request('from') || request('to'))
                            ({{ request('from', '...') }} s/d {{ request('to', '...') }})
                        @endif
                    </div>
                    <button onclick="window.print()" class="btn btn-ghost" style="font-size:0.82rem;">
                        <i class="fas fa-print"></i> Cetak / Export PDF
                    </button>
                </div>

                @foreach($orders as $order)
                    <div class="order-card">
                        <div class="order-card-header">
                            <div>
                                <div class="order-code">{{ $order->order_code }}</div>
                                <div class="order-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ $order->ordered_at ? $order->ordered_at->format('d M Y, H:i') : '-' }}
                                </div>
                            </div>
                            <span class="order-status status-{{ $order->status }}">
                                {{ $order->status_label }}
                            </span>
                        </div>
                        <div class="order-card-body">
                            <div class="order-items">
                                @foreach($order->items as $item)
                                    <div class="order-item-row">
                                        <div>
                                            <div class="order-item-name">{{ $item->product_name }}</div>
                                            @if($item->variant_name)
                                                <div class="order-item-detail">Varian: {{ $item->variant_name }}</div>
                                            @endif
                                            <div class="order-item-detail">{{ $item->quantity }}x @ Rp
                                                {{ number_format($item->price, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="order-item-price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="order-summary">
                                <div class="summary-row">
                                    <span style="color:var(--mid)">Subtotal</span>
                                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                                </div>
                                @if($order->admin_fee > 0)
                                    <div class="summary-row">
                                        <span style="color:var(--mid)">Biaya Admin</span>
                                        <span>Rp {{ number_format($order->admin_fee, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($order->discount_amount > 0)
                                    <div class="summary-row">
                                        <span style="color:var(--mid)">Diskon</span>
                                        <span style="color:var(--primary)">-Rp
                                            {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if($order->unique_code > 0)
                                    <div class="summary-row">
                                        <span style="color:var(--mid)">Kode Unik</span>
                                        <span>+Rp {{ number_format($order->unique_code, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="summary-row total">
                                    <span>TOTAL</span>
                                    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            @if($order->paymentMethod)
                                <div class="order-payment">
                                    💳 {{ $order->paymentMethod->name }} ({{ ucfirst($order->paymentMethod->type) }})
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state" style="padding:3rem 1rem;">
                    <span class="empty-state-icon">🔍</span>
                    <h3>Tidak Ada Pesanan</h3>
                    <p>Tidak ditemukan pesanan untuk nomor <strong>{{ request('phone') }}</strong>
                        @if(request('from') || request('to'))
                            pada tanggal {{ request('from', '...') }} s/d {{ request('to', '...') }}
                        @endif
                    </p>
                </div>
            @endif
        @endif
    </div>
@endsection