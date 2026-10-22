<?php

// Environment settings are shared by Apache and the scheduled CLI checker.
return [
    'daily_penalty' => max(0, round((float) (getenv('OVERDUE_DAILY_PENALTY') ?: 0), 2)),
    'sms_driver' => getenv('SMS_DRIVER') ?: 'log', // log = preview only; semaphore = live
    'semaphore_api_key' => getenv('SEMAPHORE_API_KEY') ?: '',
    'semaphore_sender_name' => getenv('SEMAPHORE_SENDER_NAME') ?: '',
];
