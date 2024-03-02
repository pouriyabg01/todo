<?php

namespace App\Filament\Resources\TodayTasksResource\Pages;

use App\Filament\Resources\TodayTasksResource;
use Closure;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTodayTasks extends ManageRecords
{
    protected static string $resource = TodayTasksResource::class;

}
