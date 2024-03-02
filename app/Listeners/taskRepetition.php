<?php

namespace App\Listeners;

use App\Events\saveTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class taskRepetition
{

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     * @var User $user
     */
    public $user;
    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Handle the event.
     */
    public function handle(saveTask $event): void
    {
        /**
         * Calculates the next repeat datetime based on the task's repeat schedule.
         *
         * @param \App\Models\Task $task
         * @return \Carbon\Carbon|null
         */
        $task = $event->task;
        $today = Carbon::now()->startOfDay();


        if (is_null($task->repeat) && is_null($task->last_repeat)) {
            return;
        } elseif (is_null($task->repeat) && !is_null($task->last_repeat)) {
            $task->update(['last_repeat' => null]);
            $this->cleanupTodayTask($task);
            return;
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

    /**
     * Calculates the next repeat datetime based on the task's repeat schedule.
     *
     * @param \App\Models\Task $task
     * @return \Carbon\Carbon|null
     */
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

    /**
     * Processes the task repeat logic: updates last_repeat and creates a todayTask record.
     *
     * @param \App\Models\Task $task
     * @param \Carbon\Carbon $today
     * @return void
     */
    protected function processTaskRepeat($task)
    {
        if (!$this->user->todayTasks()->where('task_id' , $task->id)->exists())
            $this->user->todayTasks()->attach($task->id);

    }

    /**
     * Cleans up any existing todayTask records if the task is not scheduled for today.
     *
     * @param \App\Models\Task $task
     * @return void
     */
    protected function cleanupTodayTask($task)
    {

        if ($this->user->todayTasks()->where('task_id' , $task->id)->exists())
            $this->user->todayTasks()->detach($task->id);
    }


}
