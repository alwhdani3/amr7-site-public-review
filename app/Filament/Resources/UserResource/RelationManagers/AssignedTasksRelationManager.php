<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Department;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components as F;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AssignedTasksRelationManager extends RelationManager
{
    protected static string $relationship = 'assignedTasks';
    protected static ?string $title = 'المهام المسندة';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان المهمة')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($state) => $state)
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Task::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => Task::statusColors()[$state] ?? 'gray'),

                Tables\Columns\TextColumn::make('priority')
                    ->label('الأولوية')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Task::priorityOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => Task::priorityColors()[$state] ?? 'gray'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('الاستحقاق')
                    ->date('Y-m-d')
                    ->color(fn (Task $record) => $record->is_overdue ? 'danger' : 'gray')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('القسم')
                    ->badge()
                    ->color('primary')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(Task::statusOptions()),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options(Task::priorityOptions()),
            ])
            ->toolbarActions([
                Action::make('createTask')
                    ->label('إضافة مهمة')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->form([
                        F\TextInput::make('title')
                            ->label('عنوان المهمة')
                            ->required()
                            ->maxLength(255),

                        F\Textarea::make('description')
                            ->label('التفاصيل')
                            ->rows(3)
                            ->nullable(),

                        F\Select::make('priority')
                            ->label('الأولوية')
                            ->options(Task::priorityOptions())
                            ->default('normal')
                            ->required(),

                        F\Select::make('department_id')
                            ->label('القسم')
                            ->options(fn () => Department::query()->active()->pluck('name', 'id')->toArray())
                            ->searchable()
                            ->nullable(),

                        F\DatePicker::make('due_date')
                            ->label('تاريخ الاستحقاق')
                            ->nullable(),

                        F\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(2)
                            ->nullable(),
                    ])
                    ->action(function (array $data): void {
                        Task::create([
                            ...$data,
                            'assigned_to' => $this->getOwnerRecord()->id,
                            'created_by' => auth()->id(),
                            'status' => 'pending',
                        ]);
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('markDone')
                        ->label('تحديد كمكتملة')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Task $record) => ! in_array($record->status, ['done', 'cancelled'], true))
                        ->requiresConfirmation()
                        ->action(function (Task $record): void {
                            $record->update([
                                'status' => 'done',
                                'completed_at' => now(),
                            ]);
                        }),

                    Action::make('markInProgress')
                        ->label('تحويل لقيد التنفيذ')
                        ->icon('heroicon-o-play-circle')
                        ->color('info')
                        ->visible(fn (Task $record) => $record->status === 'pending')
                        ->action(fn (Task $record) => $record->update([
                            'status' => 'in_progress',
                        ])),

                    Action::make('editTask')
                        ->label('تعديل')
                        ->icon('heroicon-m-pencil-square')
                        ->color('gray')
                        ->form([
                            F\TextInput::make('title')
                                ->label('عنوان المهمة')
                                ->required()
                                ->maxLength(255),

                            F\Select::make('status')
                                ->label('الحالة')
                                ->options(Task::statusOptions())
                                ->required(),

                            F\Select::make('priority')
                                ->label('الأولوية')
                                ->options(Task::priorityOptions())
                                ->required(),

                            F\DatePicker::make('due_date')
                                ->label('تاريخ الاستحقاق')
                                ->nullable(),

                            F\Textarea::make('notes')
                                ->label('ملاحظات')
                                ->rows(2)
                                ->nullable(),
                        ])
                        ->fillForm(fn (Task $record): array => $record->only([
                            'title',
                            'status',
                            'priority',
                            'due_date',
                            'notes',
                        ]))
                        ->action(fn (Task $record, array $data) => $record->update($data)),

                    Action::make('deleteTask')
                        ->label('حذف')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Task $record) => $record->delete()),
                ])
                    ->tooltip('الإجراءات')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->emptyStateHeading('لا توجد مهام مسندة')
            ->emptyStateDescription('يمكنك إضافة مهمة جديدة لهذا الموظف من خلال زر "إضافة مهمة".')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }
}