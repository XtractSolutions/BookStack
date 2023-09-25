<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Carbon\Carbon;

/**
 * Logging configuration options.
 *
 * Changes to these config files are not supported by BookStack and may break upon updates.
 * Configuration should be altered via the `.env` file or environment variables.
 * Do not edit this file unless you're happy to maintain any changes yourself.
 */

return [

    // Default Log Channel
    // This option defines the default log channel that gets used when writing
    // messages to the logs. The name specified in this option should match
    // one of the channels defined in the "channels" configuration array.
    'default' => env('LOG_CHANNEL', 'single'),

    // Deprecations Log Channel
    // This option controls the log channel that should be used to log warnings
    // regarding deprecated PHP and library features. This allows you to get
    // your application ready for upcoming major versions of dependencies.
    'deprecations' => [
        'channel' => 'null',
        'trace' => false,
    ],

    // Log Channels
    // Here you may configure the log channels for your application. Out of
    // the box, Laravel uses the Monolog PHP logging library. This gives
    // you a variety of powerful log handlers / formatters to utilize.
    // Available Drivers: "single", "daily", "slack", "syslog",
    //                    "errorlog", "monolog",
    //                    "custom", "stack"
    'channels' => [
        'stack' => [
            'driver'            => 'stack',
            'channels'          => ['daily'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => 'debug',
            'days'   => 14,
        ],

        'daily' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => 'debug',
            'days'   => 7,
        ],

        'stderr' => [
            'driver'  => 'monolog',
            'level'   => 'debug',
            'handler' => StreamHandler::class,
            'with'    => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level'  => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level'  => 'debug',
        ],

        // Custom errorlog implementation that logs out a plain,
        // non-formatted message intended for the webserver log.
        'errorlog_plain_webserver' => [
            'driver'         => 'monolog',
            'level'          => 'debug',
            'handler'        => ErrorLogHandler::class,
            'handler_with'   => [4],
            'formatter'      => LineFormatter::class,
            'formatter_with' => [
                'format' => '%message%',
            ],
        ],

        'null' => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

        // Testing channel
        // Uses a shared testing instance during tests
        // so that logs can be checked against.
        'testing' => [
            'driver' => 'testing',
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'cloudwatch' => [
            'driver' => 'custom',
            'via' => \BookStack\Logging\CloudWatchLoggerFactory::class,
            'sdk' => [
              'region' => env('AWS_REGION', 'us-west-2'),
              'version' => 'latest',
              'credentials' => [
                'key' => env('AWS_KEY'),
                'secret' => env('AWS_SECRET'),
              ]
            ],
            'include_stack_traces' => env('INCLUDE_STACK_TRACES', 'false'),
            'retention' => 14,
            'level' => 'info',
            'group' => '/api/' . env('DB_DATABASE'),
            'stream' => Carbon::now()->format('Ymd'),
            'batch_size' => 1
        ]
    ],

    // Failed Login Message
    // Allows a configurable message to be logged when a login request fails.
    'failed_login' => [
        'message' => env('LOG_FAILED_LOGIN_MESSAGE', null),
        'channel' => env('LOG_FAILED_LOGIN_CHANNEL', 'errorlog_plain_webserver'),
    ],

];
