<?php

namespace App\Console\Commands;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendToDo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:send-to-do';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $update = Telegram::getWebhookUpdate();
        $task = Task::find('1');
        $now = Carbon::now();

//        if ($now->lt(Carbon::parse($task->reminder))) {
        if (true) {
            Telegra::sendMessage([
                'chat_id' => '5381913319',
                'text' => 'its reminder for '.$task->title.'To-Do'
            ]);
        }
    }
}
