<?php

return [
    'api_key' => env('BREVO_API_KEY'),
    'sender_email' => env('BREVO_SENDER_EMAIL', 'no-reply@novelya.id'),
    'sender_name' => env('BREVO_SENDER_NAME', 'Novelya'),
    'webhook_secret' => env('BREVO_WEBHOOK_SECRET'),
];
