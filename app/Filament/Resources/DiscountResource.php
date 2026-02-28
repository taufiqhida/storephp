<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Models\DiscountCode;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class DiscountResource extends Resource
{
    protected static ?string $model = DiscountCode::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Kode Diskon';
    protected static ?string $modelLabel = 'Kode Diskon';
    protected static ?string $navigationGroup = 'Toko';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('code')->label('Kode Diskon')->required()
                    ->unique(ignoreRecord: true)
                    ->dehydrateStateUsing(fn($state) => strtoupper($state))
                    ->extraInputAttributes(['style' => 'text-transform:uppercase']),
                TextInput::make('description')->label('Deskripsi'),
                Select::make('type')->label('Tipe Diskon')
                    ->options(['fixed' => 'Nominal (Rp)', 'percent' => 'Persentase (%)'])
                    ->default('fixed')->required(),
                TextInput::make('value')->label('Nilai Diskon')->numeric()->required(),
                TextInput::make('min_purchase')->label('Minimal Pembelian (Rp)')->numeric()->default(0),
                TextInput::make('max_uses')->label('Maks Penggunaan (kosong=unlimited)')->numeric(),
                DateTimePicker::make('expired_at')->label('Berlaku Hingga'),
                Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->badge()->color('primary'),
                TextColumn::make('type')->label('Tipe')
                    ->formatStateUsing(fn($state) => $state === 'percent' ? 'Persentase' : 'Nominal')
                    ->badge(),
                TextColumn::make('value')->label('Nilai')
                    ->formatStateUsing(fn($state, $record) =>
                        $record->type === 'percent' ? $state . '%' : 'Rp ' . number_format($state, 0, ',', '.')),
                TextColumn::make('used_count')->label('Dipakai'),
                TextColumn::make('max_uses')->label('Maks')->formatStateUsing(fn($state) => $state ?? '∞'),
                TextColumn::make('expired_at')->label('Kadaluarsa')->dateTime('d/m/Y')->sortable(),
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
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $admin = auth('admin')->user();
        return $admin && $admin->hasPermission('manage_discounts');
    }
}
