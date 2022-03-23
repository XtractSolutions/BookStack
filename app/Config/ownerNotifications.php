<?php

/**
 * Document owner notification options.
 *
 * Changes to these config files are not supported by BookStack and may break upon updates.
 * Configuration should be altered via the `.env` file or environment variables.
 * Do not edit this file unless you're happy to maintain any changes yourself.
 */

return [
    'ownerNotificationChannel' => env('OWNER_NOTIFICATION_CHANNEL',''),
    'staleDocumentThresholdDays' => env('OWNER_NOTIFICATION_THRESHOLD_DAYS', 180),
    'cron' => env('OWNER_NOTIFICATION_CRON', ''),
    'allowNullOwners' => env('OWNER_NOTIFICATION_ALLOW_NULL_OWNER', true)
];
