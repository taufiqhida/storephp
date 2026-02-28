<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = '5 Produk Terlaris';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.*', DB::raw("
                        (SELECT SUM(quantity) 
                         FROM order_items 
                         JOIN orders ON orders.id = order_items.order_id 
                         WHERE order_items.product_id = products.id 
                         AND orders.status IN ('completed', 'shipped', 'processing')) as total_sold
                    "))
                    ->with('category')
                    ->having('total_sold', '>', 0)
                    ->orderByDesc('total_sold')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('images/placeholder.jpg')),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('base_price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id'),
                Tables\Columns\TextColumn::make('total_sold')
                    ->label('Terjual (Qty)')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}
