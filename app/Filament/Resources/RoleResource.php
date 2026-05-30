<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components as F;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|UnitEnum|null $navigationGroup = 'الإدارة';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'الأدوار والصلاحيات';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermissionTo('roles.manage') ?? false;
    }

    /**
     * Arabic label for a role identified by Spatie name.
     * Spatie's default schema has no `display_name` column, so we map here.
     */
    public static function displayName(?string $name): string
    {
        return match ($name) {
            'super_admin' => 'مدير النظام',
            'manager'     => 'مدير قسم',
            'employee'    => 'موظف',
            'support'     => 'دعم فني',
            'customer'    => 'عميل',
            'accountant'  => 'محاسب',
            default       => (string) ($name ?? '—'),
        };
    }

    /**
     * Derive a logical group from the permission name (e.g. "subscriptions.view_all" → "subscriptions").
     * Spatie's default schema has no `group` column, so we infer from the dotted prefix.
     */
    public static function permissionGroup(string $permissionName): string
    {
        if (! str_contains($permissionName, '.')) {
            return 'other';
        }

        return strtolower(strstr($permissionName, '.', true));
    }

    /**
     * Arabic label for a permission identified by name (e.g. "customers.view" → "عرض العملاء").
     * Falls back to the raw name when no explicit mapping is defined.
     */
    public static function permissionLabel(string $permissionName): string
    {
        $map = [
            // employees
            'employees.view'           => 'عرض الموظفين',
            'employees.create'         => 'إنشاء موظف',
            'employees.edit'           => 'تعديل موظف',
            'employees.delete'         => 'حذف موظف',
            'employees.toggle_active'  => 'تفعيل/إيقاف موظف',
            // tickets
            'tickets.view_all'         => 'عرض كل التذاكر',
            'tickets.view_own'         => 'عرض تذاكره فقط',
            'tickets.reply'            => 'الرد على التذاكر',
            'tickets.assign'           => 'إسناد تذكرة',
            'tickets.close'            => 'إغلاق تذكرة',
            'tickets.delete'           => 'حذف تذكرة',
            'tickets.change_priority'  => 'تغيير الأولوية',
            'tickets.change_department'=> 'تغيير القسم',
            // tasks
            'tasks.view_all'           => 'عرض كل المهام',
            'tasks.view_own'           => 'عرض مهامه فقط',
            'tasks.create'             => 'إنشاء مهمة',
            'tasks.edit'               => 'تعديل مهمة',
            'tasks.delete'             => 'حذف مهمة',
            'tasks.assign'             => 'إسناد مهمة',
            'tasks.change_status'      => 'تغيير حالة المهمة',
            // customers
            'customers.view'           => 'عرض العملاء',
            'customers.create'         => 'إنشاء عميل',
            'customers.edit'           => 'تعديل عميل',
            'customers.delete'         => 'حذف عميل',
            // services
            'services.view'            => 'عرض الخدمات',
            'services.create'          => 'إنشاء خدمة',
            'services.edit'            => 'تعديل خدمة',
            'services.delete'          => 'حذف خدمة',
            // service_requests
            'service_requests.view_all'       => 'عرض كل طلبات الخدمات',
            'service_requests.view_own'       => 'عرض طلباته فقط',
            'service_requests.update_status'  => 'تحديث حالة الطلب',
            // posts/content
            'posts.view'               => 'عرض المقالات',
            'posts.create'             => 'إنشاء مقالة',
            'posts.edit'               => 'تعديل مقالة',
            'posts.delete'             => 'حذف مقالة',
            'posts.publish'            => 'نشر مقالة',
            // settings
            'settings.view'            => 'عرض الإعدادات',
            'settings.edit'            => 'تعديل الإعدادات',
            'departments.manage'       => 'إدارة الأقسام',
            'roles.manage'             => 'إدارة الأدوار والصلاحيات',
            // reports
            'reports.view'             => 'عرض التقارير',
            'reports.export'           => 'تصدير التقارير',
            // financial
            'financial.view_all'       => 'عرض كل القوائم المالية',
            'financial.view_own'       => 'عرض قوائمه فقط',
            'financial.manage'         => 'إدارة القوائم المالية',
            // Phase 9C-0 — packages/subscriptions/obligations/periods/tax_returns
            'package_features.manage'         => 'إدارة ميزات الباقات',
            'subscriptions.view_all'          => 'عرض الاشتراكات',
            'subscriptions.view_own'          => 'عرض اشتراكاته',
            'subscriptions.create'            => 'إنشاء اشتراك',
            'subscriptions.edit'              => 'تعديل اشتراك',
            'subscriptions.cancel'            => 'إلغاء اشتراك',
            'subscriptions.renew'             => 'تجديد اشتراك',
            'subscriptions.change_package'    => 'تغيير الباقة',
            'obligations.view_all'            => 'عرض الالتزامات',
            'obligations.create'              => 'إنشاء التزام',
            'obligations.edit'                => 'تعديل التزام',
            'obligations.pause'               => 'إيقاف التزام',
            'obligations.resume'              => 'استئناف التزام',
            'periods.view_all'                => 'عرض فترات الالتزام',
            'periods.open'                    => 'فتح فترة التزام',
            'periods.close'                   => 'إغلاق فترة التزام',
            'periods.link'                    => 'ربط فترة بطلب/إقرار',
            'tax_returns.view_all'            => 'عرض الإقرارات الضريبية',
            'tax_returns.create'              => 'إنشاء إقرار ضريبي',
            'tax_returns.review'              => 'مراجعة الإقرار',
            'tax_returns.request_client_approval' => 'طلب اعتماد العميل',
            'tax_returns.file'                => 'تقديم الإقرار',
            'tax_returns.cancel'              => 'إلغاء الإقرار',
        ];

        return $map[$permissionName] ?? $permissionName;
    }

    public static function form(Schema $schema): Schema
    {
        $groupedPermissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => static::permissionGroup($permission->name));

        $groupLabels = [
            'employees'         => '👤 الموظفون',
            'tickets'           => '🎫 التذاكر',
            'tasks'             => '✅ المهام',
            'customers'         => '🏢 العملاء',
            'services'          => '⚙️ الخدمات',
            'service_requests'  => '⚙️ طلبات الخدمات',
            'content'           => '📝 المحتوى',
            'posts'             => '📝 المقالات',
            'settings'          => '🔧 الإعدادات',
            'departments'       => '🏢 الأقسام',
            'reports'           => '📊 التقارير',
            'financial'         => '💰 القوائم المالية',
            'roles'             => '🛡️ الأدوار والصلاحيات',
            // Phase 9C-0 groups:
            'packages'          => '🎁 الباقات',
            'package_features'  => '🎁 ميزات الباقات',
            'subscriptions'     => '💳 اشتراكات العملاء',
            'obligations'       => '🛡️ الالتزامات',
            'periods'           => '📅 فترات الالتزام',
            'tax_returns'       => '📄 الإقرارات الضريبية',
            'other'             => '📦 أخرى',
        ];

        $permissionSections = $groupedPermissions
            ->map(function (Collection $permissions, string $group) use ($groupLabels) {
                return Section::make($groupLabels[$group] ?? $group)
                    ->schema([
                        F\CheckboxList::make("permission_groups.{$group}")
                            ->label('')
                            ->options(
                                $permissions->mapWithKeys(fn (Permission $permission) => [
                                    $permission->name => static::permissionLabel($permission->name),
                                ])->toArray()
                            )
                            ->columns(2)
                            ->bulkToggleable()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (F\CheckboxList $component, ?Role $record) use ($permissions): void {
                                if (! $record) {
                                    $component->state([]);
                                    return;
                                }

                                $currentPermissions = $record->permissions->pluck('name')->all();
                                $groupPermissionNames = $permissions->pluck('name')->all();

                                $component->state(
                                    array_values(array_intersect($currentPermissions, $groupPermissionNames))
                                );
                            }),
                    ])
                    ->collapsible()
                    ->collapsed(false);
            })
            ->values()
            ->all();

        return $schema->components([
            Section::make('بيانات الدور')
                ->schema([
                    F\TextInput::make('name')
                        ->label('اسم الدور (بالإنجليزية)')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('مثال: manager')
                        ->helperText('استخدم حروف إنجليزية صغيرة وشرطة سفلية فقط. الأسماء العربية تظهر تلقائياً من mapping داخل التطبيق.')
                        ->regex('/^[a-z_]+$/')
                        ->disabled(fn (?Role $record) => in_array($record?->name, ['super_admin', 'customer'], true)),
                ])
                ->columns(1),

            Section::make('الصلاحيات')
                ->schema($permissionSections)
                ->description('حدد الصلاحيات الممنوحة لهذا الدور'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الدور')
                    ->formatStateUsing(fn (?string $state) => static::displayName($state))
                    ->description(fn (Role $record) => $record->name)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('الصلاحيات')
                    ->counts('permissions')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('المستخدمون')
                    ->counts('users')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل'),

                DeleteAction::make()
                    ->label('حذف')
                    ->visible(fn (Role $record) => ! in_array($record->name, ['super_admin', 'customer'], true)),
            ])
            ->emptyStateHeading('لا توجد أدوار بعد')
            ->emptyStateDescription('ابدأ بإضافة أدوار جديدة لتنظيم صلاحيات فريقك.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit'   => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['guard_name'] = 'web';

        return static::preparePermissionsForSave($data);
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $data['guard_name'] = $data['guard_name'] ?? 'web';

        return static::preparePermissionsForSave($data);
    }

    protected static function preparePermissionsForSave(array $data): array
    {
        $permissionGroups = $data['permission_groups'] ?? [];

        $data['permissions'] = collect($permissionGroups)
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        unset($data['permission_groups']);

        return $data;
    }
}