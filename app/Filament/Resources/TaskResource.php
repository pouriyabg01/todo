<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use App\Models\TodayTask;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PHPUnit\Metadata\Group;
use PHPUnit\Util\Filter;
use Filament\Forms\Contracts\HasForms;

class TaskResource extends Resource
{

    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'To-Do';

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        $tasks = $user->tasks()->getQuery();
        return  $tasks;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->columnSpanFull(),
                                Forms\Components\DateTimePicker::make('reminder'),
                                Forms\Components\DateTimePicker::make('due'),
                            ]),
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('repeat')
                                    ->options([
                                        'daily' => 'daily',
                                        'weekly' => 'weekly',
                                        'monthly' => 'monthly',
                                        'yearly' => 'yearly',
                                    ]),
                                Forms\Components\Select::make('priority')
                                    ->options([
                                        'Level' => [
                                            '1' => 'Low',
                                            '2' => 'High',
                                        ],
                                        'Urgency' => [
                                            '3' => 'Urgent'
                                        ]
                                    ]),
                                Forms\Components\Select::make('tags')
                                    ->multiple()
                                    ->relationship('tags' , 'title'),
                            ])
                    ])
           ] );
    }

    public static function table(Table $table): Table
    {
        return $table
//            ->defaultGroup('priority')
            ->defaultSort('priority' , 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('completed'),
                Tables\Columns\TextColumn::make('repeat'),
                Tables\Columns\TextColumn::make('priority')
                    ->state(function ($record){
                        switch ($record->priority) {
                            case ('1'):
                                return 'low';
                            case ('2'):
                                return 'high';
                            case ('3'):
                                return 'urgent';
                            default:
                                return '';
                        }
                    })
                    ->sortable(false)
                    ->color(fn (string $state): string => match ($state){
                        'low' => 'success',
                        'high' => 'warning',
                        'urgent' => 'danger'
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('reminder')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
