<?php

namespace App\Logging;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Maxbanton\Cwh\Handler\CloudWatch;
use Monolog\Logger;
use Monolog\Formatter\JsonFormatter;
use Carbon\Carbon;

class CloudWatchLoggerFactory
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $sdkParams = $config["sdk"];
        $tags = $config["tags"] ?? [ ];
        $name = $config["name"] ?? 'cloudwatch';
        $batchSize = $config['batch_size'] ?? 1000;
        $IncludeStackTraces = $config["include_stack_traces"] == true;

        // Instantiate AWS SDK CloudWatch Logs Client
        $client = new CloudWatchLogsClient($sdkParams);

        // Log group name, will be created if none
        $groupName = $config["group"];

        // Log stream name, will be created if none
        $streamName = Carbon::now()->format('Ymd');

        // Days to keep logs, 14 by default. Set to `null` to allow indefinite retention.
        $retentionDays = $config["retention"];

        // Instantiate handler (tags are optional)
        $handler = new CloudWatch($client, $groupName, $streamName, $retentionDays, $batchSize, $tags);
        
        // Optionally set the JsonFormatter to be able to access your log messages in a structured way
        $Formatter = new JsonFormatter();

        // enable extra stack traces
        $Formatter->includeStacktraces($IncludeStackTraces);
        
        $handler->setFormatter($Formatter);
        // Create a log channel
        $logger = new Logger($name);
        // Set handler
        $logger->pushHandler($handler);

        return $logger;
    }
}