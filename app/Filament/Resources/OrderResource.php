<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pesanan';
    protected static ?string $modelLabel = 'Pesanan';
    protected static ?string $navigationGroup = 'Toko';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Update Status')->schema([
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'processing' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required(),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Detail Pesanan')->schema([
                TextEntry::make('order_code')->label('Kode Pesanan')->badge()->color('primary'),
                TextEntry::make('status')->label('Status')->badge()
                    ->color(fn($state) => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'processing' => 'primary',
                        'shipped' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'processing' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                TextEntry::make('ordered_at')->label('Tanggal')->dateTime('d M Y H:i'),
            ])->columns(3),
            InfoSection::make('Data Pembeli')->schema([
                TextEntry::make('customer_name')->label('Nama'),
                TextEntry::make('customer_phone')->label('Nomor HP'),
                TextEntry::make('paymentMethod.name')->label('Metode Bayar'),
                TextEntry::make('customer_note')->label('Catatan')->columnSpanFull(),
            ])->columns(3),
            InfoSection::make('Rincian Harga')->schema([
                TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                TextEntry::make('admin_fee')->label('Biaya Admin')->money('IDR'),
                TextEntry::make('discount_amount')->label('Diskon')->money('IDR'),
                TextEntry::make('unique_code')->label('Kode Unik'),
                TextEntry::make('total')->label('TOTAL')->money('IDR')->weight('bold')->size('lg'),
            ])->columns(5),
            InfoSection::make('Item Pesanan')->schema([
                RepeatableEntry::make('items')->schema([
                    TextEntry::make('product_name')->label('Produk'),
                    TextEntry::make('variant_name')->label('Varian'),
                    TextEntry::make('price')->label('Harga')->money('IDR'),
                    TextEntry::make('quantity')->label('Qty'),
                    TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                ])->columns(5),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_code')->label('Kode')->searchable()->copyable(),
                TextColumn::make('customer_name')->label('Pembeli')->searchable(),
                TextColumn::make('customer_phone')->label('HP'),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'pending' => 'warning',
                        'confirmed', 'processing' => 'info',
                        'shipped', 'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'Menunggu',
                        'confirmed' => 'Dikonfirmasi',
                        'processing' => 'Diproses',
                        'shipped' => 'Dikirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                TextColumn::make('total')->label('Total')->money('IDR')->sortable(),
                TextColumn::make('ordered_at')->label('Tanggal')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Menunggu',
                    'confirmed' => 'Dikonfirmasi',
                    'processing' => 'Diproses',
                    'shipped' => 'Dikirim',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                ])->label('Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->label('Update Status'),
            ])
            ->defaultSort('ordered_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $admin = auth('admin')->user();
        return $admin && $admin->hasPermission('manage_orders');
    }
}
