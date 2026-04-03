<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Communication Frequency Thresholds
    |--------------------------------------------------------------------------
    | Maximum number of messages (Email + WA combined) sent to a single user
    | before they are flagged in the Frequency Monitor.
    */
    'max_comms_7d' => (int) env('MAX_COMMS_7D', 3),
    'max_comms_30d' => (int) env('MAX_COMMS_30D', 10),
];
