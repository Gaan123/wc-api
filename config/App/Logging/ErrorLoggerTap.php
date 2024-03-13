<?php

namespace App\Logging;
use Illuminate\Log\Logger;

use Illuminate\Support\Facades\Mail;
use App\Mail\ErrorNotification;
class ErrorLoggerTap
{
    public function __invoke(Logger $logger)
    {
//        dd($logger->getLogger());
        $logger->listen(function ($level) {

            if ($level->level === 'error') {
                Mail::to('your-email@example.com')->send(new ErrorNotification($level->message, $level->context));
            }
        });
    }
}
