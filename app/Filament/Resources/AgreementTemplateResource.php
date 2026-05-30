<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgreementTemplateResource\Pages;
use App\Models\AgreementTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DbSchema;

/**
 * Filament admin for accounting agreement templates.
 * Templates use {{placeholder}} syntax; the renderer substitutes per-client.
 */
class AgreementTemplateResource extends Resource
{
    protected static ?string $model = AgreementTemplate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'قوالب الاتفاقيات';
    protected static ?string $modelLabel = 'قالب اتفاقية';
    protected static ?string $pluralLabel = 'قوالب الاتفاقيات';
    protected static string|\UnitEnum|null $navigationGroup = 'الاشتراكات والالتزامات';
    protected static ?int $navigationSort = 15;

    // Agreement templates are legal documents — the previous gate was a
    // schema-only check that let any backoffice user (employee, support,
    // accountant) view AND modify them. Real permission now required;
    // the table check stays as a guard so we don't 500 when the migration
    // hasn't been run on a given environment.

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('agreement_templates')
            && static::userCanManageAgreements(auth()->user());
    }

    public static function canCreate(): bool
    {
        return DbSchema::hasTable('agreement_templates')
            && static::userCanManageAgreements(auth()->user());
    }

    public static function canEdit($record): bool
    {
        return static::userCanManageAgreements(auth()->user());
    }

    public static function canDelete($record): bool
    {
        return static::userIsAdmin(auth()->user());
    }

    public static function canDeleteAny(): bool
    {
        return static::userIsAdmin(auth()->user());
    }

    protected static function userCanManageAgreements(?\App\Models\User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['manager'])) {
            return true;
        }

        return strtolower((string) ($user->role ?? '')) === 'manager';
    }

    protected static function userIsAdmin(?\App\Models\User $user): bool
    {
        return (bool) ($user?->is_admin);
    }

    public static function kindOptions(): array
    {
        return [
            'monthly'   => 'شهرية',
            'quarterly' => 'ربع سنوية',
            'yearly'    => 'سنوية',
            'custom'    => 'مخصصة',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        $placeholders = collect(AgreementTemplate::supportedPlaceholders())
            ->map(fn (string $p) => '{{' . $p . '}}')
            ->implode(', ');

        return $schema->components([
            Section::make('بيانات القالب')
                ->description('الاسم والـslug والنوع.')
                ->columns(2)
                ->schema([
                    F\TextInput::make('name')
                        ->label('اسم القالب')
                        ->required()
                        ->maxLength(191)
                        ->placeholder('مثال: اتفاقية المحاسبة الأساسية'),

                    F\TextInput::make('slug')
                        ->label('المعرّف (slug)')
                        ->required()
                        ->maxLength(80)
                        ->unique(ignoreRecord: true)
                        ->regex('/^[a-z0-9-]+$/')
                        ->helperText('حروف إنجليزية صغيرة وأرقام وشَرطة فقط، مثل: accounting-basic')
                        ->placeholder('accounting-basic'),

                    F\Select::make('kind')
                        ->label('نوع الاتفاقية')
                        ->options(static::kindOptions())
                        ->nullable(),

                    F\Toggle::make('is_active')
                        ->label('قالب فعّال')
                        ->default(true)
                        ->helperText('فقط القوالب الفعّالة تظهر للاختيار عند ربط الباقات.'),
                ]),

            Section::make('المحتوى')
                ->description('نص الاتفاقية. استخدم العناصر النائبة مثل {{client_company_name}} ليتم استبدالها تلقائياً عند توليد اتفاقية لعميل معيّن.')
                ->schema([
                    F\Textarea::make('body')
                        ->label('نص الاتفاقية')
                        ->required()
                        ->default(AgreementTemplate::defaultBodyTemplate())
                        ->rows(18)
                        ->columnSpanFull()
                        ->helperText('يقبل النص Markdown أو HTML بسيط — لا تضمّن سكربتات أو CSS مخصص هنا.'),
                ]),

            Section::make('العناصر النائبة المدعومة')
                ->collapsible()
                ->collapsed()
                ->description('الـplaceholders التالية مدعومة في الـrenderer. انسخها بالشكل {{placeholder}} داخل نص الاتفاقية.')
                ->schema([
                    F\Placeholder::make('supported_placeholders')
                        ->label('')
                        ->content($placeholders),

                    F\KeyValue::make('placeholders')
                        ->label('عناصر مخصصة (اختياري)')
                        ->keyLabel('المفتاح')
                        ->valueLabel('الوصف')
                        ->helperText('أضف هنا أي placeholders إضافية تستخدمها في هذا القالب فقط. لن يستبدلها الـrenderer تلقائياً إلا إذا أُضيفت لـsupportedPlaceholders().')
                        ->columnSpanFull(),
                ]),

            Section::make('بيانات إضافية')
                ->collapsible()
                ->collapsed()
                ->schema([
                    F\KeyValue::make('metadata')
                        ->label('metadata')
                        ->keyLabel('المفتاح')
                        ->valueLabel('القيمة')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('slug')
                    ->label('المعرّف')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('kind')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => static::kindOptions()[$state] ?? ($state ?: '—'))
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('فعّال')
                    ->boolean(),

                TextColumn::make('packages_count')
                    ->label('باقات مرتبطة')
                    ->counts('packages')
                    ->badge(),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kind')
                    ->label('النوع')
                    ->options(static::kindOptions()),

                TernaryFilter::make('is_active')
                    ->label('فعّال')
                    ->trueLabel('فعّال')
                    ->falseLabel('موقوف'),
            ])
            ->recordActions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد قوالب اتفاقيات بعد')
            ->emptyStateDescription('ابدأ بإضافة قالب اتفاقية لربطه بباقات المحاسبة.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAgreementTemplates::route('/'),
            'create' => Pages\CreateAgreementTemplate::route('/create'),
            'edit'   => Pages\EditAgreementTemplate::route('/{record}/edit'),
        ];
    }
}
