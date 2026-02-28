<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Artikel / Blog';
    protected static ?string $modelLabel = 'Artikel';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Artikel')->schema([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
                Textarea::make('excerpt')->label('Ringkasan')->rows(2)->columnSpanFull(),
                FileUpload::make('thumbnail')->label('Thumbnail')->image()->directory('articles')->columnSpanFull(),
                Toggle::make('is_published')->label('Publikasikan')->live()
                    ->afterStateUpdated(fn($state, $set) => $state ? $set('published_at', now()) : null),
                DateTimePicker::make('published_at')->label('Tanggal Publikasi'),
            ])->columns(2),
            Section::make('Konten')->schema([
                RichEditor::make('content')->label('Isi Artikel')->required()->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')->label('Foto'),
                TextColumn::make('title')->label('Judul')->searchable()->sortable(),
                ToggleColumn::make('is_published')->label('Dipublikasi'),
                TextColumn::make('published_at')->label('Tanggal Publik')->dateTime('d/m/Y')->sortable(),
                TextColumn::make('created_at')->label('Dibuat')->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $admin = auth('admin')->user();
        return $admin && $admin->hasPermission('manage_articles');
    }
}
