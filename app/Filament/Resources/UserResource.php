<?php
namespace App\Filament\Resources;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\{AssignedTasksRelationManager,AssignedTicketsRelationManager,CompaniesRelationManager,UploadedAttachmentsRelationManager};
use App\Models\{Department,User};
use Filament\Actions\{Action,ActionGroup};
use Filament\Forms\Components as F;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|\UnitEnum|null $navigationGroup = 'الإدارة';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'المستخدمون';
    protected static ?string $modelLabel = 'مستخدم';
    protected static ?string $pluralModelLabel = 'المستخدمون';

    protected static function isAdmin(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return method_exists($user, 'isAdmin')
            ? (bool) $user->isAdmin()
            : ($user->role === 'admin');
    }

    /**
     * Normalize a Saudi mobile number to E.164 format (+9665XXXXXXXX).
     *
     * Accepts any of: 0538381925, 538381925, 966538381925, +966538381925,
     * 00966538381925, with or without spaces/dashes/parens/dots. Returns
     * null on blank input OR on input that doesn't match a valid Saudi
     * mobile pattern — callers must decide whether to accept null (blank)
     * or reject as invalid (use isValidSaudiMobile to disambiguate).
     */
    public static function normalizeSaudiMobile(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // Drop separators humans use when typing phone numbers.
        $digits = preg_replace('/[\s\-\(\)\.]/', '', $value);
        // Strip leading + (we'll add it back after recognising the prefix).
        $digits = ltrim($digits, '+');
        // International "00" prefix is equivalent to "+".
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Already E.164 body: 9665XXXXXXXX
        if (preg_match('/^9665\d{8}$/', $digits)) {
            return '+' . $digits;
        }
        // Local form: 05XXXXXXXX (10 digits) → strip leading 0, prepend 966
        if (preg_match('/^05\d{8}$/', $digits)) {
            return '+966' . substr($digits, 1);
        }
        // Short form: 5XXXXXXXX (9 digits) → prepend 966
        if (preg_match('/^5\d{8}$/', $digits)) {
            return '+966' . $digits;
        }

        return null;
    }

    public static function canViewAny(): bool
    {
        return static::isAdmin();
    }

    public static function canCreate(): bool
    {
        return static::isAdmin();
    }

    public static function canEdit($record): bool
    {
        return static::isAdmin();
    }

    public static function canDelete($record): bool
    {
        return static::isAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::isAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات المستخدم')
                ->description('الاسم والمعرّفات الأساسية للمستخدم.')
                ->columns(2)
                ->schema([
                    F\TextInput::make('name')
                        ->label('الاسم الكامل')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('مثال: محمد علي'),

                    F\TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('name@example.com'),

                    F\TextInput::make('mobile')
                        ->label('الجوال')
                        ->tel()
                        ->maxLength(20)
                        ->nullable()
                        ->placeholder('05XXXXXXXX')
                        ->helperText('يُقبل: 05XXXXXXXX أو 5XXXXXXXX أو 9665XXXXXXXX أو +9665XXXXXXXX. يُحفظ بصيغة +9665XXXXXXXX.')
                        // Custom rule runs against the *normalized* number so
                        // a user entering "0538381925" still collides with an
                        // existing "+966538381925" instead of slipping past
                        // Filament's raw-string unique() check and hitting the
                        // DB's users_mobile_unique index as a 500.
                        ->rule(static function (?User $record) {
                            return function (string $attribute, $value, \Closure $fail) use ($record): void {
                                if (blank($value)) {
                                    return;
                                }
                                $normalized = self::normalizeSaudiMobile($value);
                                if ($normalized === null) {
                                    $fail('صيغة رقم الجوال غير صحيحة. مثال صحيح: 0538381925.');
                                    return;
                                }
                                $exists = User::query()
                                    ->where('mobile', $normalized)
                                    ->when($record, fn ($q) => $q->where('id', '!=', $record->getKey()))
                                    ->exists();
                                if ($exists) {
                                    $fail('رقم الجوال مسجل لمستخدم آخر بالفعل.');
                                }
                            };
                        })
                        ->dehydrateStateUsing(fn (?string $state): ?string => self::normalizeSaudiMobile($state)),

                    F\TextInput::make('job_title')
                        ->label('المسمى الوظيفي')
                        ->maxLength(255)
                        ->nullable()
                        ->placeholder('مثال: مدير عمليات'),
                ]),

            Section::make('الصلاحيات والدور')
                ->description('الدور يحدد ما يستطيع المستخدم رؤيته داخل لوحة الإدارة.')
                ->columns(2)
                ->schema([
                    F\Select::make('spatie_role')
                        ->label('الدور')
                        ->options(fn () => Role::all()->mapWithKeys(fn (Role $role) => [$role->name => RoleResource::displayName($role->name)])->toArray())
                        ->required()
                        ->searchable()
                        ->dehydrated(false)
                        ->afterStateHydrated(fn (F\Select $c, ?User $r) => $c->state($r?->roles->first()?->name)),

                    F\Select::make('department_id')
                        ->label('القسم')
                        ->options(fn () => Department::query()->active()->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->helperText('اختياري — يربط المستخدم بفريق/قسم محدد.'),
                ]),

            Section::make('الأمان والحالة')
                ->description('كلمة المرور وتفعيل الحساب وتوثيق البريد.')
                ->columns(2)
                ->schema([
                    F\TextInput::make('password')
                        ->label('كلمة المرور')
                        ->password()
                        ->revealable()
                        ->helperText('اتركها فارغة عند التعديل إذا لا تريد تغيير كلمة المرور الحالية.')
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->columnSpanFull(),

                    F\Toggle::make('is_active')
                        ->label('الحساب مفعّل')
                        ->default(true)
                        ->helperText('عند إيقاف الحساب يُمنع المستخدم من تسجيل الدخول.'),

                    F\Toggle::make('email_verified')
                        ->label('البريد موثّق')
                        ->default(false)
                        ->helperText('يمنح المستخدم وصولاً كاملاً لمزايا التحقق.')
                        ->afterStateHydrated(fn (F\Toggle $c, ?User $r) => $c->state((bool)($r?->email_verified_at)))
                        ->dehydrated(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('avatar')->label('')->disk('public')->circular()->defaultImageUrl(fn (User $r) => 'https://ui-avatars.com/api/?name='.urlencode($r->name).'&background=1FA7A2&color=fff&size=64')->width(40)->height(40),
            Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->sortable()->description(fn (User $r) => $r->job_title ?? ''),
            Tables\Columns\TextColumn::make('email')->label('البريد')->searchable()->copyable()->icon('heroicon-m-envelope'),
            Tables\Columns\TextColumn::make('roles.name')->label('الدور')->badge()->formatStateUsing(fn (?string $state) => RoleResource::displayName($state))->color(fn (User $r) => match($r->roles->first()?->name) { 'super_admin'=>'danger','manager'=>'warning','employee'=>'success','support'=>'info','accountant'=>'primary',default=>'gray' })->placeholder('—'),
            Tables\Columns\TextColumn::make('department.name')->label('القسم')->badge()->color('primary')->placeholder('-'),
            Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
            Tables\Columns\IconColumn::make('email_verified_at')->label('موثّق')->state(fn (User $r) => filled($r->email_verified_at))->boolean(),
        ])->filters([
            Tables\Filters\SelectFilter::make('role')->label('الدور')->relationship('roles','name')->getOptionLabelFromRecordUsing(fn (Role $record) => RoleResource::displayName($record->name)),
            Tables\Filters\TernaryFilter::make('is_active')->label('نشط؟'),
        ])->actions([
            ActionGroup::make([
                Action::make('edit')->label('تعديل')->icon('heroicon-m-pencil-square')->color('gray')->url(fn (User $r) => static::getUrl('edit',['record'=>$r])),
                Action::make('toggleActive')->label(fn (User $r) => $r->is_active?'إيقاف':'تفعيل')->icon(fn (User $r) => $r->is_active?'heroicon-o-no-symbol':'heroicon-o-check-circle')->color(fn (User $r) => $r->is_active?'danger':'success')->requiresConfirmation()->action(function (User $r) { $r->is_active=!$r->is_active; $r->suspended_at=!$r->is_active?now():null; $r->save(); Notification::make()->title('تم')->success()->send(); }),
                Action::make('verifyEmail')->label('توثيق البريد')->icon('heroicon-o-check-badge')->color('success')->visible(fn (User $r) => blank($r->email_verified_at))->requiresConfirmation()->action(fn (User $r) => $r->forceFill(['email_verified_at'=>now()])->save()),
                Action::make('delete')->label('حذف')->icon('heroicon-m-trash')->color('danger')->requiresConfirmation()->action(fn (User $r) => $r->delete()),
            ])->tooltip('الإجراءات')->icon('heroicon-m-ellipsis-vertical'),
        ])
        ->emptyStateHeading('لا يوجد مستخدمون بعد')
        ->emptyStateDescription('ابدأ بإضافة مستخدمين لفريقك من زر "إنشاء" أعلى الصفحة.');
    }

    public static function getRelations(): array { return [CompaniesRelationManager::class,AssignedTicketsRelationManager::class,AssignedTasksRelationManager::class,UploadedAttachmentsRelationManager::class]; }

    public static function getPages(): array { return ['index'=>Pages\ListUsers::route('/'),'create'=>Pages\CreateUser::route('/create'),'edit'=>Pages\EditUser::route('/{record}/edit')]; }
}
