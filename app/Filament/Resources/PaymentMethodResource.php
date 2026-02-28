<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Metode Pembayaran';
    protected static ?string $modelLabel = 'Metode Pembayaran';
    protected static ?string $navigationGroup = 'Toko';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('name')->label('Nama')->required(),
                Select::make('type')->label('Tipe')
                    ->options(['ewallet' => 'E-Wallet', 'bank' => 'Bank Transfer', 'qris' => 'QRIS'])
                    ->required(),
                TextInput::make('account_number')->label('Nomor Rekening/Akun'),
                TextInput::make('account_name')->label('Nama Pemilik Rekening'),
                TextInput::make('admin_fee')->label('Biaya Admin')->numeric()->prefix('Rp')->default(0),
                Select::make('fee_type')->label('Tipe Biaya')
                    ->options(['fixed' => 'Nominal Tetap', 'percent' => 'Persentase (%)'])
                    ->default('fixed'),
                TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                Toggle::make('is_active')->label('Aktif')->default(true),
                FileUpload::make('logo')->label('Logo')->image()->directory('payments'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')->label('Logo')->circular(),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('type')->label('Tipe')->badge()
                    ->color(fn($state) => match ($state) {
                        'ewallet' => 'success',
                        'bank' => 'info',
                        'qris' => 'warning',
                    }),
                TextColumn::make('account_number')->label('Rekening'),
                TextColumn::make('admin_fee')->label('Biaya Admin')->money('IDR'),
                ToggleColumn::make('is_active')->label('Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $admin = auth('admin')->user();
        return $admin && $admin->hasPermission('manage_payments');
    }
}
