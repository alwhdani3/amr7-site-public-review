<?php

namespace App\Filament\Resources;

use App\Enums\FinancialStatementStatus;
use App\Filament\Resources\FinancialStatementRequestResource\Pages;
use App\Models\FinancialStatementFile;
use App\Models\FinancialStatementRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

// Forms inputs
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class FinancialStatementRequestResource extends Resource
{
    protected static ?string $model = FinancialStatementRequest::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationLabel = 'طلبات القوائم المالية';
    protected static \UnitEnum|string|null $navigationGroup = 'العملاء والخدمات';
    protected static ?int $navigationSort = 1;

    // Arabic model labels for breadcrumbs, page titles, and notifications —
    // Filament falls back to the English class name otherwise.
    public static function getModelLabel(): string
    {
        return 'طلب قوائم مالية';
    }

    public static function getPluralModelLabel(): string
    {
        return 'طلبات القوائم المالية';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereIn('status', ['waiting_docs', 'new'])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'waiting_docs')->count() > 0 ? 'danger' : 'primary';
    }

    protected static function statusOptions(): array
    {
        return collect(FinancialStatementStatus::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
            ->toArray();
    }

    protected static function secureUrlForFile(?FinancialStatementFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        return route('financial-statements.file.download', $file);
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
            Group::make()
                ->schema([
                    Section::make('تفاصيل الطلب الأساسية')
                        ->schema([
                            TextInput::make('public_id')
                                ->label('رقم الطلب المرجعي')
                                ->default(fn () => 'REQ-' . strtoupper(uniqid()))
                                ->disabled()
                                ->dehydrated(),

                            Select::make('status')
                                ->label('حالة الطلب')
                                ->options(static::statusOptions())
                                ->required()
                                ->selectablePlaceholder(false)
                                ->native(false),

                            TextInput::make('company_name')
                                ->label('اسم المنشأة')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('cr_number')
                                ->label('رقم السجل التجاري')
                                ->required()
                                ->maxLength(20),

                            TextInput::make('fiscal_year')
                                ->label('السنة المالية')
                                ->numeric()
                                ->minValue(2000)
                                ->maxValue(2099),
                        ])
                        ->columns(2),

                    Section::make('الملاحظات والتواصل')
                        ->schema([
                            Textarea::make('client_notes')
                                ->label('ملاحظات العميل (للقراءة فقط)')
                                ->rows(3)
                                ->disabled()
                                ->columnSpanFull(),

                            Textarea::make('admin_notes')
                                ->label('ملاحظات الإدارة (داخلية)')
                                ->placeholder('اكتب أي ملاحظات خاصة بالفريق هنا...')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),

                    Section::make('جميع ملفات الطلب')
                        ->schema([
                            Repeater::make('files')
                                ->relationship('files')
                                ->label('جميع الملفات')
                                ->schema([
                                    Hidden::make('id'),

                                    TextInput::make('original_name')
                                        ->label('اسم الملف')
                                        ->disabled()
                                        ->columnSpan(1),

                                    TextInput::make('file_key')
                                        ->label('نوع الملف')
                                        ->disabled()
                                        ->columnSpan(1),

                                    Hidden::make('path'),

                                    Placeholder::make('download_link')
                                        ->label('تحميل الملف')
                                        ->content(function ($record) {
                                            if (! $record) {
                                                return 'لا يوجد ملف';
                                            }

                                            $url = static::secureUrlForFile($record);

                                            if (! $url) {
                                                return 'لا يوجد ملف';
                                            }

                                            return new HtmlString(
                                                '<a href="' . e($url) . '" target="_blank" style="color:#1FA7A2;text-decoration:underline;font-weight:700;">
                                                    تحميل الملف <span style="font-size:12px">⬇️</span>
                                                </a>'
                                            );
                                        })
                                        ->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->columnSpanFull(),
                        ])
                        ->collapsed(),
                ])
                ->columnSpan(['lg' => 2]),

            Group::make()
                ->schema([
                    Section::make('إنجاز الطلب وتسليم العمل')
                        ->description('رفع القوائم المالية النهائية ليراها العميل عبر رابط آمن.')
                        ->schema([
                            FileUpload::make('final_outputs_temp')
                                ->label('رفع الملفات النهائية')
                                ->multiple()
                                ->disk('private')
                                ->directory(fn ($record) => $record
                                    ? "financial-statements/{$record->public_id}/final"
                                    : "financial-statements/temp"
                                )
                                ->preserveFilenames()
                                ->maxSize(50120)
                                ->acceptedFileTypes([
                                    'application/pdf',
                                    'application/vnd.ms-excel',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'image/jpeg',
                                    'image/png',
                                ])
                                ->downloadable()
                                ->dehydrated(false)
                                ->visible(fn ($record) => filled($record)),
                        ])
                        ->collapsible(),

                    Section::make('بيانات العميل')
                        ->schema([
                            TextInput::make('user.name')->label('اسم العميل')->disabled(),
                            TextInput::make('user.email')->label('البريد الإلكتروني')->disabled(),
                            TextInput::make('user.phone')->label('رقم الجوال')->disabled(),

                            Placeholder::make('created_at')
                                ->label('تاريخ ووقت الطلب')
                                ->content(fn ($record) => $record?->created_at?->format('d/m/Y - h:i A')),
                        ]),
                ])
                ->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (FinancialStatementRequest $record) => static::getUrl('view', ['record' => $record]))
            ->columns([
                Tables\Columns\TextColumn::make('public_id')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->fontFamily(\Filament\Support\Enums\FontFamily::Mono)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('company_name')
                    ->label('المنشأة')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            \App\Models\User::select('name')
                                ->whereColumn('users.id', 'financial_statement_requests.user_id'),
                            $direction
                        );
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state) => FinancialStatementStatus::tryFrom($state)?->label() ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new'                                          => 'gray',
                        'waiting_docs', 'files_uploaded'               => 'warning',
                        'in_review', 'under_review'                    => 'primary',
                        'client_approval'                              => 'info',
                        'moc_approval', 'moci_approval', 'moci_pending'=> 'info',
                        'internal_approved', 'moci_approved',
                        'approved', 'completed'                        => 'success',
                        'rejected', 'cancelled'                        => 'danger',
                        'closed'                                       => 'gray',
                        default                                        => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('تصفية حسب الحالة')
                    ->options(static::statusOptions()),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('من تاريخ'),
                        DatePicker::make('created_until')->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل / معالجة'),

                Action::make('download_files')
                    ->label('تحميل الملفات')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => route('fs.show', $record->public_id))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => static::canDeleteAny()),

                    BulkAction::make('change_status')
                        ->label('تغيير الحالة')
                        ->icon('heroicon-o-pencil-square')
                        ->form([
                            Select::make('status')
                                ->label('الحالة الجديدة')
                                ->options(static::statusOptions())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('لا توجد طلبات قوائم مالية')
            ->emptyStateDescription('بمجرد استلام طلبات من العملاء، ستظهر هنا.')
            ->poll('10s');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات الطلب')
                ->description('البيانات الأساسية للعميل وحالة الطلب')
                ->icon('heroicon-o-information-circle')
                ->columns(3)
                ->schema([
                    TextEntry::make('public_id')->label('رقم الطلب')->copyable()->weight('bold'),
                    TextEntry::make('user.name')->label('اسم العميل'),
                    TextEntry::make('company_name')->label('اسم المنشأة')->placeholder('غير محدد'),

                    TextEntry::make('user.phone')
                        ->label('رقم الجوال')
                        ->icon('heroicon-m-phone')
                        ->url(fn ($record) => 'tel:' . ($record->user->phone ?? '')),

                    TextEntry::make('user.email')
                        ->label('البريد الإلكتروني')
                        ->icon('heroicon-m-envelope')
                        ->copyable(),

                    TextEntry::make('created_at')->label('تاريخ الطلب')->dateTime('d/m/Y h:i A'),

                    TextEntry::make('status')
                        ->label('حالة الطلب')
                        ->badge()
                        ->formatStateUsing(fn ($state) => FinancialStatementStatus::tryFrom($state)?->label() ?? $state),
                ]),

            Section::make('الملاحظات')
                ->icon('heroicon-o-pencil-square')
                ->columns(2)
                ->schema([
                    TextEntry::make('client_notes')->label('ملاحظات العميل')->placeholder('لا توجد ملاحظات'),
                    TextEntry::make('admin_notes')->label('ملاحظات الإدارة')->placeholder('لا توجد ملاحظات'),
                ]),

            Section::make('المرفقات')
                ->icon('heroicon-o-paper-clip')
                ->schema([
                    RepeatableEntry::make('files')
                        ->label('')
                        ->schema([
                            Grid::make(2)->schema([
                                TextEntry::make('original_name')->label('اسم الملف')->icon('heroicon-o-document'),

                                TextEntry::make('path')
                                    ->label('تحميل')
                                    ->formatStateUsing(fn () => 'تحميل الملف')
                                    ->url(fn ($record) => $record?->url)
                                    ->openUrlInNewTab(),
                            ]),
                        ])
                        ->grid(2)
                        ->placeholder('لا توجد ملفات مرفقة'),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFinancialStatementRequests::route('/'),
            'create' => Pages\CreateFinancialStatementRequest::route('/create'),
            'view'   => Pages\ViewFinancialStatementRequest::route('/{record}'),
            'edit'   => Pages\EditFinancialStatementRequest::route('/{record}/edit'),
        ];
    }
}