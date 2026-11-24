<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Support\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name_en';

    public static function getNavigationBadge(): ?string
    {
        $low = Product::where('stock', '<=', Product::LOW_STOCK)->count();

        return $low > 0 ? (string) $low : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Low or out of stock';
    }

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema([
            Forms\Components\Group::make()->columnSpan(2)->schema([
                Forms\Components\Section::make('Details')->schema([
                    Forms\Components\Tabs::make('Language')->tabs([
                        Forms\Components\Tabs\Tab::make('English')->schema([
                            Forms\Components\TextInput::make('name_en')->label('Name')->required()->maxLength(255),
                            Forms\Components\Textarea::make('description_en')->label('Description')->rows(4),
                        ]),
                        Forms\Components\Tabs\Tab::make('العربية')->schema([
                            Forms\Components\TextInput::make('name_ar')->label('الاسم')->required()->maxLength(255)->extraInputAttributes(['dir' => 'rtl']),
                            Forms\Components\Textarea::make('description_ar')->label('الوصف')->rows(4)->extraInputAttributes(['dir' => 'rtl']),
                        ]),
                    ]),
                ]),
                Forms\Components\Section::make('Pricing & stock')->columns(3)->schema([
                    // Stored in fils; edited in JOD.
                    Forms\Components\TextInput::make('price')->label('Price (JOD)')->numeric()->minValue(0)->step(0.01)->required()
                        ->formatStateUsing(fn (?int $state) => $state !== null ? Money::toMajor($state) : null)
                        ->dehydrateStateUsing(fn ($state) => Money::fromMajor($state)),
                    Forms\Components\TextInput::make('compare_at_price')->label('Was (JOD)')->numeric()->minValue(0)->step(0.01)
                        ->helperText('Shows a sale badge when higher than the price.')
                        ->formatStateUsing(fn (?int $state) => $state !== null ? Money::toMajor($state) : null)
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Money::fromMajor($state) : null),
                    Forms\Components\TextInput::make('stock')->numeric()->integer()->minValue(0)->required()->default(0),
                ]),
            ]),
            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Status')->schema([
                    Forms\Components\Toggle::make('is_active')->label('Visible in store')->default(true),
                    Forms\Components\Toggle::make('is_featured')->label('Featured on home page'),
                    Forms\Components\Select::make('category_id')->relationship('category', 'name_en')->required()->preload()->searchable(),
                ]),
                Forms\Components\Section::make('Image')->schema([
                    Forms\Components\FileUpload::make('image_path')->label('Photo')->image()->disk('public')->directory('products')->imageEditor()->maxSize(2048),
                    Forms\Components\TextInput::make('emoji')->label('Emoji (used when there is no photo)')->maxLength(16),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('emoji')->label(''),
                Tables\Columns\TextColumn::make('name_en')->label('Product')->searchable(['name_en', 'name_ar'])->sortable()
                    ->description(fn (Product $p) => $p->name_ar),
                Tables\Columns\TextColumn::make('category.name_en')->label('Category')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('price')->formatStateUsing(fn (int $state) => Money::format($state, 'en'))->sortable(),
                Tables\Columns\TextColumn::make('stock')->sortable()->badge()
                    ->color(fn (int $state) => match (true) {
                        $state === 0 => 'danger',
                        $state <= Product::LOW_STOCK => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\IconColumn::make('is_featured')->label('Featured')->boolean(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name_en'),
                Tables\Filters\Filter::make('low_stock')->label('Low stock')
                    ->query(fn (Builder $q) => $q->where('stock', '<=', Product::LOW_STOCK)),
                Tables\Filters\TernaryFilter::make('is_active')->label('Visible'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('hide')->label('Hide from store')->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
