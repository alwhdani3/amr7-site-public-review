<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Models\BankAccount;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Facades\Schema as DbSchema;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-credit-card';
    protected static \UnitEnum|string|null $navigationGroup = 'المالية';
    protected static ?int $navigationSort = 40;
    protected static ?string $navigationLabel = 'الحسابات البنكية';
    protected static ?string $modelLabel = 'حساب بنكي';
    protected static ?string $pluralModelLabel = 'الحسابات البنكية';

    public static function canViewAny(): bool
    {
        return DbSchema::hasTable('bank_accounts')
            && static::userHasAnyRole(auth()->user(), ['manager', 'accountant']);
    }

    public static function canCreate(): bool
    {
        return DbSchema::hasTable('bank_accounts')
            && static::userHasAnyRole(auth()->user(), ['manager', 'accountant']);
    }

    public static function canView($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager', 'accountant']);
    }

    public static function canEdit($record): bool
    {
        return static::userHasAnyRole(auth()->user(), ['manager', 'accountant']);
    }

    public static function canDelete($record): bool
    {
        return static::userIsAdmin(auth()->user());
    }

    public static function canDeleteAny(): bool
    {
        return static::userIsAdmin(auth()->user());
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

    protected static function userIsAdmin(?\App\Models\User $user): bool
    {
        return (bool) ($user?->is_admin);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات الحساب البنكي')
                ->schema([
                    Select::make('bank_name')
                        ->label('اختر البنك')
                        ->options([
                            'مصرف الراجحي' => 'مصرف الراجحي',
                            'البنك الأهلي السعودي' => 'البنك الأهلي السعودي',
                            'مصرف الإنماء' => 'مصرف الإنماء',
                            'بنك الرياض' => 'بنك الرياض',
                            'بنك البلاد' => 'بنك البلاد',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?string $state) {
                            $logos = [
                                'مصرف الراجحي' => 'rajhi.png',
                                'البنك الأهلي السعودي' => 'snb.png',
                                'مصرف الإنماء' => 'alinma.png',
                                'بنك الرياض' => 'riyad.png',
                                'بنك البلاد' => 'bilad.png',
                            ];

                            $set('bank_logo', $logos[$state] ?? 'generic-bank.png');
                        }),

                    Hidden::make('bank_logo'),

                    TextInput::make('account_name')
                        ->label('اسم صاحب الحساب')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('account_number')
                        ->label('رقم الحساب')
                        ->helperText('يُخزَّن مشفَّرًا داخل قاعدة البيانات ولا يظهر في السجلات.')
                        ->required()
                        ->maxLength(50),

                    TextInput::make('iban')
                        ->label('الآيبان (IBAN)')
                        ->helperText('24 خانة تبدأ بـ SA. يُخزَّن مشفَّرًا ويُعرض كاملًا لفريق المالية فقط.')
                        ->required()
                        ->maxLength(34),

                    Toggle::make('is_active')
                        ->label('الحساب نشط')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('البنك')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('account_name')
                    ->label('صاحب الحساب')
                    ->searchable(),

                Tables\Columns\TextColumn::make('iban')
                    ->label('الآيبان')
                    ->copyable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('حالة الحساب')
                    ->placeholder('الكل')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد حسابات بنكية بعد')
            ->emptyStateDescription('أضف حساب البنك الذي يستقبل التحويلات حتى يظهر للعملاء في صفحات الدفع وصفحة "حسابات الشركة" العامة.')
            ->emptyStateIcon('heroicon-o-credit-card');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }
}
