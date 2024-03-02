<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TodayTasksResource\Pages;
use App\Filament\Resources\TodayTasksResource\RelationManagers;
use App\Models\TodayTask;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TodayTasksResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'today';

    protected static ?string $navigationLabel = 'today task';

    protected static ?string $label = 'things to do for today';



    public static function getEloquentQuery(): Builder
    {
        $today = TodayTask::all()->pluck('task_id')->toArray();

        return parent::getEloquentQuery()->whereIn('id' , $today)->where('user_id' , Auth::id());
    }

    /**
     *
     * @var Task $task
     */
    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        return $user->todayTasks()->count();
//        return parent::getEloquentQuery()->whereIn('id' , $today)->where('user_id' , Auth::id())->count();
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\IconColumn::make('completed')
                    ->boolean(),
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
                    ->color(fn (string $state): string => match ($state){
                        'low' => 'success',
                        'high' => 'warning',
                        'urgent' => 'danger'
                    })
//                Tables\Columns\IconColumn::make('priority')
//                    ->icon(fn (string $state): string => match ($state) {
//                        '1' => 'heroicon-o-chevron-up',
//                        '2' => 'heroicon-o-chevron-double-up',
//                        '3' => 'heroicon-o-clock',
//                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('reminder')
                    ->dateTime()
                    ->sortable(),

            ])
            ->recordUrl(fn(Task $record):string => route('filament.admin.resources.tasks.edit' , $record))

            ->filters([
                //
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
            'index' => Pages\ManageTodayTasks::route('/'),
            'create' => Pages\ManageTodayTasks::route('/create'),
            'edit' => Pages\ManageTodayTasks::route('/{record}/edit'),
        ];
    }
}
