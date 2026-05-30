<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyDocumentResource\Pages;
use App\Models\CompanyDocument;
use Filament\Resources\Resource;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

use Filament\Forms\Components as F;

use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

// ✅ Filament v5: Actions من هنا (وليس Filament\Tables\Actions)
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class CompanyDocumentResource extends Resource
{
    protected static ?string $model = CompanyDocument::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationLabel = 'وثائق المنشآت';
    protected static ?string $modelLabel = 'وثيقة';
    protected static ?string $pluralModelLabel = 'وثائق المنشآت';

    protected static \UnitEnum|string|null $navigationGroup = 'الوثائق والتذاكر';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if (! $user) {
            return $query->whereRaw('1=0');
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('company.users', function (Builder $q) use ($user) {
            $q->whereKey($user->getKey())
                ->wherePivot('is_active', true);
        });
    }

    protected static function scopeCompaniesQuery(Builder $query): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1=0');
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('users', function (Builder $q) use ($user) {
            $q->whereKey($user->getKey())
                ->wherePivot('is_active', true);
        });
    }

    // ── Authorization ──────────────────────────────────────────────────────────

    public static function canViewAny(): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin', 'manager']);
    }

    public static function canCreate(): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin', 'manager']);
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin', 'manager']);
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin']);
    }

    public static function canDeleteAny(): bool
    {
        return static::userHasAnyRole(auth()->user(), ['super_admin', 'admin']);
    }

    protected static function userHasAnyRole(?\App\Models\User $user, array $roles): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->is_admin) {
            return true;
        }
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return true;
        }
        return in_array(strtolower((string) ($user->role ?? '')), $roles, true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات الوثيقة')
                ->schema([
                    F\Select::make('company_id')
                        ->label('المنشأة المستهدفة')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship(
                            name: 'company',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query) => static::scopeCompaniesQuery($query),
                        ),

                    F\TextInput::make('type')
                        ->label('نوع الوثيقة (مثل: سجل تجاري)')
                        ->required()
                        ->maxLength(50),

                    F\TextInput::make('document_number')
                        ->label('رقم الوثيقة')
                        ->maxLength(100),

                    F\DatePicker::make('issue_date')
                        ->label('تاريخ الإصدار'),

                    F\DatePicker::make('expiry_date')
                        ->label('تاريخ الانتهاء')
                        ->required(),

                    F\FileUpload::make('file_path')
                        ->label('ملف الوثيقة')
                        ->disk('private')
                        ->directory('company-documents')
                        ->required()
                        ->preserveFilenames()
                        ->maxSize(10240)
                        ->helperText('يتم حفظ الملف بشكل خاص ولا يظهر عبر رابط مباشر.'),

                    F\Select::make('status')
                        ->label('حالة الوثيقة')
                        ->options([
                            'valid'   => 'سارية',
                            'expired' => 'منتهية',
                            'warning' => 'تقترب من الانتهاء',
                        ])
                        ->default('valid')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('المنشأة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('الوثيقة')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('document_number')
                    ->label('الرقم')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('تاريخ الانتهاء')
                    ->date()
                    ->sortable()
                    ->color(fn ($state) => filled($state) && Carbon::parse($state)->isPast()
                        ? 'danger'
                        : 'success'
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'valid'   => 'صالحة',
                        'expired' => 'منتهية',
                        'warning' => 'قريبة الانتهاء',
                        default   => $state ?? '—',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'valid'   => 'success',
                        'expired' => 'danger',
                        'warning' => 'warning',
                        default   => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('تصفية حسب المنشأة')
                    ->relationship(
                        name: 'company',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => static::scopeCompaniesQuery($query),
                    ),
            ])
            ->recordActions([
                Action::make('viewFile')
                    ->label('عرض الملف')
                    ->icon('heroicon-o-eye')
                    ->url(fn (CompanyDocument $record) => route('company.docs.view', $record))
                    ->openUrlInNewTab(),

                Action::make('downloadFile')
                    ->label('تحميل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (CompanyDocument $record) => route('company.docs.download', $record)),

                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => static::canDeleteAny()),
                ]),
            ])
            ->emptyStateHeading('لا توجد وثائق بعد')
            ->emptyStateDescription('ابدأ بإضافة وثائق المنشأة من زر "إنشاء" أعلى الصفحة.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCompanyDocuments::route('/'),
            'create' => Pages\CreateCompanyDocument::route('/create'),
            'edit'   => Pages\EditCompanyDocument::route('/{record}/edit'),
        ];
    }
}
