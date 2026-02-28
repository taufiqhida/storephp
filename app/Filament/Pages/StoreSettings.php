<?php

namespace App\Filament\Pages;

use App\Models\StoreSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class StoreSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.store-settings';
    protected static ?string $navigationLabel = 'Pengaturan Toko';
    protected static ?string $title = 'Pengaturan Toko';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public function mount(): void
    {
        $setting = StoreSetting::current();
        $this->form->fill($setting->toArray());
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Section::make('Informasi Toko')->schema([
                TextInput::make('store_name')->label('Nama Toko')->required(),
                TextInput::make('store_email')->label('Email Toko')->email(),
                TextInput::make('store_address')->label('Alamat')->columnSpanFull(),
                Textarea::make('store_description')->label('Deskripsi Toko')->rows(3)->columnSpanFull(),
                FileUpload::make('store_logo')->label('Logo Toko')->image()->directory('store'),
            ])->columns(2),
            Section::make('WhatsApp & Pesan')->schema([
                TextInput::make('whatsapp_number')
                    ->label('Nomor WhatsApp')
                    ->placeholder('6281234567890')
                    ->tel()
                    ->required(),
                Textarea::make('message_template')
                    ->label('Template Pesan WhatsApp')
                    ->rows(8)
                    ->helperText('Gunakan: {items}, {total}, {payment}, {name}, {phone}, {note}, {order_code}')
                    ->columnSpanFull(),
            ])->columns(1),
            Section::make('Mode Website')->schema([
                Select::make('site_mode')
                    ->label('Mode Website')
                    ->options([
                        'live' => '🟢 Live (Normal)',
                        'maintenance' => '🔧 Maintenance',
                        'coming_soon' => '⏳ Coming Soon',
                    ])
                    ->required()
                    ->live(),
                DateTimePicker::make('launch_date')
                    ->label('Tanggal Launching')
                    ->helperText('Tanggal & jam kapan toko akan dibuka (untuk countdown Coming Soon)')
                    ->visible(fn($get) => $get('site_mode') === 'coming_soon'),
            ]),
            Section::make('Banner Pengumuman (Header)')->schema([
                \Filament\Forms\Components\Toggle::make('is_announcement_active')
                    ->label('Tampilkan Pengumuman')
                    ->helperText('Tampilkan banner diskon/promo di bagian paling atas web.')
                    ->live(),
                \Filament\Forms\Components\TextInput::make('announcement_text')
                    ->label('Teks Pengumuman')
                    ->maxLength(255)
                    ->placeholder('Misal: Diskon 20% untuk semua produk minggu ini!')
                    ->visible(fn($get) => $get('is_announcement_active')),
                \Filament\Forms\Components\TextInput::make('announcement_link')
                    ->label('Link Pengumuman (Opsional)')
                    ->url()
                    ->placeholder('Misal: https://taufiq.store/flash-sale')
                    ->helperText('Jika diisi, banner bisa diklik. Kosongkan jika teks biasa.')
                    ->visible(fn($get) => $get('is_announcement_active')),
            ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $setting = StoreSetting::current();
        $setting->update($data);
        Notification::make()->title('Pengaturan berhasil disimpan!')->success()->send();
    }

    public static function canAccess(): bool
    {
        $admin = auth('admin')->user();
        return $admin && $admin->hasPermission('manage_settings');
    }
}
