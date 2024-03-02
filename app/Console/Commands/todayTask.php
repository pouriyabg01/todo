<?php

namespace App\Console\Commands;

use App\Events\saveTask;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class todayTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:today';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @param Task $task
     */



    public function handle(Task $task): void
    {
        /**
         * Calculates the next repeat datetime based on the task's repeat schedule.
         *
         * @param \App\Models\Task $task
         * @return \Carbon\Carbon|null
         */
        $tasks = $task->all();
        $today = Carbon::now()->startOfDay();

        foreach ($tasks as $task){
            if (is_null($task->repeat) && is_null($task->last_repeat)) {
                continue;
            } elseif (is_null($task->repeat) && !is_null($task->last_repeat)) {
                $task->update(['last_repeat' => null]);
                $this->cleanupTodayTask($task);
                continue;
            }

            $nextRepeat = $this->calculateNextRepeat($task);

            if (is_null($task->last_repeat) OR $today->gte($nextRepeat)){
                $task->update(['last_repeat' => $today]);
                $this->processTaskRepeat($task);
            } elseif ($today->equalTo(Carbon::parse($task->last_repeat)->startOfDay())) {
                $this->processTaskRepeat($task);
            } else {
                $this->cleanupTodayTask($task);
            }
        }

    }

    protected function calculateNextRepeat($task)
    {
        if (!is_null($task->repeat)) {
            return match ($task->repeat) {
                'daily' => Carbon::parse($task->last_repeat)->addDay()->startOfDay(),
                'weekly' => Carbon::parse($task->last_repeat)->addWeek()->startOfDay(),
                'monthly' => Carbon::parse($task->last_repeat)->addMonth()->startOfDay(),
                'yearly' => Carbon::parse($task->last_repeat)->addYear()->startOfDay(),
            };
        } else {
            return null;
        }
    }


    protected function processTaskRepeat($task)
    {
        foreach (User::all() as $user) {
            if (!$user->todayTasks()->where('task_id', $task->id)->exists() && $user->tasks()->where('tasks.id' , $task->id)->exists())
                $user->todayTasks()->attach($task->id);
        }
    }

    protected function cleanupTodayTask($task)
    {
        foreach (User::all() as $user) {
            if ($user->todayTasks()->where('task_id', $task->id)->exists() && $user->tasks()->where('tasks.id' , $task->id)->exists())
                $user->todayTasks()->detach($task->id);
        }
    }

}
