<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceRequestResource\Pages;
use App\Models\ServiceRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static \UnitEnum|string|null $navigationGroup = 'العملاء والخدمات';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'طلبات الخدمات';
    protected static ?string $modelLabel = 'طلب خدمة';
    protected static ?string $pluralModelLabel = 'طلبات الخدمات';

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
            Section::make('بيانات الطلب')
                ->schema([
                    F\Select::make('service_id')
                        ->relationship('service', 'title_ar')
                        ->label('الخدمة')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    F\Select::make('company_id')
                        ->relationship('company', 'name')
                        ->label('الشركة')
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    F\TextInput::make('phone')
                        ->label('الجوال')
                        ->tel()
                        ->maxLength(30)
                        ->required(),

                    F\Select::make('payment_method')
                        ->label('طريقة الدفع')
                        ->options([
                            'bank_transfer' => 'تحويل بنكي',
                            'card'          => 'بطاقة',
                            'cash'          => 'نقدًا',
                            'other'         => 'أخرى',
                        ])
                        ->nullable()
                        ->helperText('طلبات الزوار قد تكون بدون طريقة دفع'),

                    F\Textarea::make('description')
                        ->label('تفاصيل الطلب')
                        ->rows(10)
                        ->required()
                        ->columnSpanFull(),

                    F\FileUpload::make('attachment')
                        ->label('المرفق')
                        ->disk('private')
                        ->directory('service-requests/attachments')
                        ->preserveFilenames()
                        ->downloadable()
                        ->openable()
                        ->nullable()
                        ->columnSpanFull(),

                    F\Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'new'        => 'جديد',
                            'pending'    => 'قيد الانتظار',
                            'processing' => 'جاري التنفيذ',
                            'completed'  => 'مكتمل',
                            'rejected'   => 'مرفوض',
                            'canceled'   => 'ملغي',
                        ])
                        ->required(),
                ])
                ->columns(2),

            // Phase 3: surface wizard-uploaded attachments (polymorphic via the
            // attachments table). Hidden on create or when nothing was uploaded.
            Section::make('مرفقات معالج العميل')
                ->visible(fn ($record) => $record && method_exists($record, 'attachments') && $record->attachments()->exists())
                ->schema([
                    F\Placeholder::make('wizard_attachments')
                        ->label('')
                        ->content(function ($record) {
                            if (! $record) {
                                return '—';
                            }
                            $attachments = $record->attachments()->latest()->get();
                            if ($attachments->isEmpty()) {
                                return '—';
                            }
                            $items = $attachments->map(function ($att) {
                                $name = e($att->original_name ?: ('attachment-' . $att->id));
                                $url  = route('attachments.download', $att->id);
                                $size = (int) ($att->size ?? 0);
                                $sizeLabel = $size >= 1048576
                                    ? round($size / 1048576, 1) . ' MB'
                                    : ($size >= 1024 ? round($size / 1024, 1) . ' KB' : ($size . ' B'));
                                return '<a href="' . $url . '" target="_blank" rel="noopener" '
                                    . 'class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm m-0.5 no-underline">'
                                    . '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>'
                                    . '<span>' . $name . '</span>'
                                    . '<span style="opacity:.6;font-family:monospace;font-size:11px">' . $sizeLabel . '</span>'
                                    . '</a>';
                            })->implode(' ');
                            return new \Illuminate\Support\HtmlString('<div style="display:flex;flex-wrap:wrap;gap:4px">' . $items . '</div>');
                        }),
                ])
                ->collapsible(),

            // Phase 4: read-only message thread between customer and team.
            // Hidden on create or when the request has no messages yet so
            // the form stays compact for greenfield rows.
            Section::make('سلسلة الرسائل')
                ->visible(fn ($record) => $record && method_exists($record, 'messages') && $record->messages()->exists())
                ->schema([
                    F\Placeholder::make('request_messages_thread')
                        ->label('')
                        ->content(function ($record) {
                            if (! $record) {
                                return '—';
                            }
                            $messages = $record->messages()
                                ->with('sender:id,name')
                                ->orderBy('created_at')
                                ->orderBy('id')
                                ->get();
                            if ($messages->isEmpty()) {
                                return '—';
                            }
                            $bubbles = $messages->map(function ($msg) {
                                $type = strtolower((string) ($msg->sender_type ?? 'client'));
                                $isClient = $type === 'client';
                                $isSystem = $type === 'system';
                                $senderName = $isSystem
                                    ? 'تنبيه نظام'
                                    : (optional($msg->sender)->name
                                        ?: ($isClient ? 'العميل' : 'فريق آمر سبعة'));
                                $bg = $isSystem
                                    ? '#fffbeb;border-color:#fde68a;color:#92400e'
                                    : ($isClient
                                        ? '#f1f5f9;border-color:#e2e8f0;color:#0f172a'
                                        : '#ecfeff;border-color:#a5f3fc;color:#155e75');
                                $when = $msg->created_at ? e($msg->created_at->format('Y-m-d H:i')) : '';
                                $body = nl2br(e((string) $msg->body));
                                return '<div style="border:1px solid;border-radius:10px;padding:8px 10px;margin:0 0 6px 0;background:' . $bg . '">'
                                    . '<div style="display:flex;justify-content:space-between;font-size:11px;opacity:.75;margin-bottom:4px"><strong>' . e($senderName) . '</strong><span style="font-family:monospace">' . $when . '</span></div>'
                                    . '<div style="font-size:13px;line-height:1.5;white-space:normal">' . $body . '</div>'
                                    . '</div>';
                            })->implode('');
                            return new \Illuminate\Support\HtmlString('<div style="display:flex;flex-direction:column;max-height:340px;overflow-y:auto">' . $bubbles . '</div>');
                        }),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('service.title_ar')
                    ->label('الخدمة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service.price')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2) . ' ر.س' : '-')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('service.govt_fees')
                    ->label('الرسوم الحكومية')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2) . ' ر.س' : '-')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('service.duration')
                    ->label('المدة')
                    ->formatStateUsing(fn ($state) => $state ? (string) $state : '-')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('الإجمالي')
                    ->state(function ($record) {
                        $price = (float) ($record->service?->price ?? 0);
                        $fees  = (float) ($record->service?->govt_fees ?? 0);
                        return $price + $fees;
                    })
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2) . ' ر.س')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('الشركة')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('الجوال')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'bank_transfer' => 'تحويل بنكي',
                        'card'          => 'بطاقة',
                        'cash'          => 'نقدًا',
                        'other'         => 'أخرى',
                        default         => $state ?: '-',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('المصدر')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'website_chat' => 'شات الموقع',
                        'service_form' => 'نموذج الخدمة',
                        'mobile_app'   => 'تطبيق الجوال',
                        default        => $state ?: '-',
                    })
                    ->color(fn ($state) => match ($state) {
                        'website_chat' => 'info',
                        'service_form' => 'gray',
                        'mobile_app'   => 'success',
                        default        => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('preferred_contact_method')
                    ->label('طريقة التواصل المفضلة')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'whatsapp' => 'واتساب',
                        'phone'    => 'اتصال هاتفي',
                        default    => $state ?: '-',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status_label')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn ($record) => $record->status_color)
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('التفاصيل')
                    ->limit(70)
                    ->wrap(),

                Tables\Columns\TextColumn::make('attachment_url')
                    ->label('المرفق')
                    ->url(fn ($record) => $record->attachment_url)
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn ($state) => $state ? 'فتح' : '-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'new'        => 'جديد',
                        'pending'    => 'قيد الانتظار',
                        'processing' => 'جاري التنفيذ',
                        'completed'  => 'مكتمل',
                        'rejected'   => 'مرفوض',
                        'canceled'   => 'ملغي',
                    ]),

                SelectFilter::make('source')
                    ->label('المصدر')
                    ->options([
                        'website_chat' => 'شات الموقع',
                        'service_form' => 'نموذج الخدمة',
                        'mobile_app'   => 'تطبيق الجوال',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => static::canDeleteAny()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServiceRequests::route('/'),
            'create' => Pages\CreateServiceRequest::route('/create'),
            'edit'   => Pages\EditServiceRequest::route('/{record}/edit'),
        ];
    }
}

