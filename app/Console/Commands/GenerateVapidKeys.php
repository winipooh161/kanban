<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webpush:vapid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерация VAPID ключей для WebPush уведомлений';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Генерация VAPID ключей...');
        
        $keys = VAPID::createVapidKeys();
        
        $this->info('VAPID ключи успешно сгенерированы!');
        $this->line('');
        $this->line('Добавьте следующие строки в ваш .env файл:');
        $this->line('');
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->line('');
        $this->warn('ВАЖНО: Сохраните эти ключи в безопасном месте!');
        
        return 0;
    }
}
