<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Company;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components as F;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CompaniesRelationManager extends RelationManager
{
    protected static string $relationship = 'companies';
    protected static ?string $title = 'شركات المستخدم';

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            F\Select::make('role')
                ->label('الدور داخل الشركة')
                ->options([
                    'owner' => 'المالك',
                    'manager' => 'مدير',
                    'employee' => 'موظف',
                    'viewer' => 'مشاهد',
                ])
                ->required(),

            F\TextInput::make('designation')
                ->label('المسمى الوظيفي')
                ->maxLength(255)
                ->nullable(),

            F\Toggle::make('is_active')
                ->label('نشط')
                ->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')

            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Model $record) => $record->commercial_name),

                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('المنصب')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'owner' => 'المالك',
                        'manager' => 'مدير',
                        'employee' => 'موظف',
                        'viewer' => 'مشاهد',
                        default => (string) $state,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'owner' => 'danger',
                        'manager' => 'warning',
                        'employee' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('pivot.designation')
                    ->label('المسمى')
                    ->placeholder('-'),

                Tables\Columns\IconColumn::make('pivot.is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label('تاريخ الربط')
                    ->dateTime('Y-m-d h:i A')
                    ->description(fn (Model $record) => $record->pivot?->created_at?->diffForHumans() ?? '')
                    ->sortable(),
            ])

            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        F\DatePicker::make('created_from')->label('تم الربط من تاريخ'),
                        F\DatePicker::make('created_until')->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date) => $query->wherePivot('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date) => $query->wherePivot('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('pivot.role')
                    ->label('تصفية حسب الدور')
                    ->options([
                        'owner' => 'المالك',
                        'manager' => 'مدير',
                        'employee' => 'موظف',
                        'viewer' => 'مشاهد',
                    ]),
            ])

            ->toolbarActions([
                Action::make('attachCompany')
                    ->label('ربط شركة موجودة')
                    ->icon('heroicon-m-link')
                    ->color('primary')
                    ->modalHeading('ربط شركة موجودة')
                    ->form([
                        // ✅✅✅ بحث فقط (لا تظهر شركات إلا بعد الكتابة)
                        F\Select::make('company_id')
                            ->label('الشركة')
                            ->required()
                            ->searchable()
                            ->placeholder('اكتب للبحث عن شركة...')
                            ->helperText('ابدأ بالكتابة (اسم / اسم تجاري / رقم سجل). لن تظهر قائمة قبل الكتابة.')
                            ->noSearchResultsMessage('ما لقينا شركة تطابق البحث')
                            ->getSearchResultsUsing(function (string $search): array {
                                $search = trim($search);

                                // اختياري: لا تبحث لو أقل من حرفين (لتخفيف الضغط)
                                if (mb_strlen($search) < 2) {
                                    return [];
                                }

                                return Company::query()
                                    ->where(function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%")
                                          ->orWhere('commercial_name', 'like', "%{$search}%")
                                          ->orWhere('cr_number', 'like', "%{$search}%");
                                    })
                                    ->orderBy('name')
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => Company::query()->whereKey($value)->value('name')),

                        F\Select::make('role')
                            ->label('الدور')
                            ->options([
                                'owner' => 'المالك',
                                'manager' => 'مدير',
                                'employee' => 'موظف',
                                'viewer' => 'مشاهد',
                            ])
                            ->default('viewer')
                            ->required(),

                        F\TextInput::make('designation')
                            ->label('المسمى الوظيفي')
                            ->maxLength(255)
                            ->nullable(),

                        F\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])
                    ->action(function (array $data): void {
                        $this->getOwnerRecord()
                            ->companies()
                            ->syncWithoutDetaching([
                                (int) $data['company_id'] => [
                                    'role' => $data['role'],
                                    'designation' => $data['designation'] ?? null,
                                    'is_active' => (bool) ($data['is_active'] ?? true),
                                ],
                            ]);
                    }),

                Action::make('createCompany')
                    ->label('إنشاء شركة جديدة')
                    ->icon('heroicon-m-plus')
                    ->color('gray')
                    ->modalHeading('إنشاء شركة جديدة')
                    ->form([
                        F\TextInput::make('name')->label('اسم الشركة')->required()->maxLength(255),
                        F\TextInput::make('commercial_name')->label('الاسم التجاري')->maxLength(255)->nullable(),
                        F\TextInput::make('cr_number')->label('رقم السجل')->maxLength(50)->nullable(),

                        F\Select::make('role')
                            ->label('الدور')
                            ->options([
                                'owner' => 'المالك',
                                'manager' => 'مدير',
                                'employee' => 'موظف',
                                'viewer' => 'مشاهد',
                            ])
                            ->default('viewer')
                            ->required(),

                        F\TextInput::make('designation')->label('المسمى الوظيفي')->maxLength(255)->nullable(),
                        F\Toggle::make('is_active')->label('نشط')->default(true),
                    ])
                    ->action(function (array $data): void {
                        $company = Company::create([
                            'name' => $data['name'],
                            'commercial_name' => $data['commercial_name'] ?? null,
                            'cr_number' => $data['cr_number'] ?? null,
                        ]);

                        $this->getOwnerRecord()->companies()->attach($company->id, [
                            'role' => $data['role'],
                            'designation' => $data['designation'] ?? null,
                            'is_active' => (bool) ($data['is_active'] ?? true),
                        ]);
                    }),
            ])

            ->recordActions([
                ActionGroup::make([
                    Action::make('editPivot')
                        ->label('تعديل الدور')
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning')
                        ->form([
                            F\Select::make('role')
                                ->label('الدور')
                                ->options([
                                    'owner' => 'المالك',
                                    'manager' => 'مدير',
                                    'employee' => 'موظف',
                                    'viewer' => 'مشاهد',
                                ])
                                ->required(),
                            F\TextInput::make('designation')->label('المسمى الوظيفي')->maxLength(255)->nullable(),
                            F\Toggle::make('is_active')->label('نشط')->default(true),
                        ])
                        ->fillForm(fn (Model $record): array => [
                            'role' => $record->pivot->role ?? 'viewer',
                            'designation' => $record->pivot->designation ?? null,
                            'is_active' => (bool) ($record->pivot->is_active ?? true),
                        ])
                        ->action(function (Model $record, array $data): void {
                            $this->getOwnerRecord()->companies()->updateExistingPivot($record->id, [
                                'role' => $data['role'],
                                'designation' => $data['designation'] ?? null,
                                'is_active' => (bool) ($data['is_active'] ?? true),
                            ]);
                        }),

                    Action::make('detachCompany')
                        ->label('فصل')
                        ->icon('heroicon-m-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('فصل الشركة')
                        ->modalDescription('هل أنت متأكد من فصل هذه الشركة عن المستخدم؟')
                        ->action(fn (Model $record) => $this->getOwnerRecord()->companies()->detach($record->id)),
                ])
                ->tooltip('الإجراءات')
                ->icon('heroicon-m-ellipsis-vertical'),
            ]);
    }
}
