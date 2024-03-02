<?php

namespace App\Models;

use App\Events\saveTask;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::created(function ($task){
            event(new saveTask($task));
        });

        static::updated(function ($task){
            event(new saveTask($task));
        });
    }

    public function getPriorityTextAttribute()
    {
        $priorityValues  = [
            '1' => 'Low',
            '2' => 'High',
            '3' => 'Urgent',
        ];

        return $priorityValues[$this->priority] ?? $this->priority;
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class , 'task_tag');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function today()
    {
        return $this->belongsToMany(Task::class , 'today_tasks');
    }

}
