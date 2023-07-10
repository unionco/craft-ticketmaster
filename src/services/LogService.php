<?php

namespace unionco\ticketmaster\services;

use Craft;
use DateTime;
use craft\base\Component;
use craft\helpers\FileHelper;
use Throwable;

class LogService extends Component
{
    protected string $logFile = '';

    public function info(string $message): void
    {
        $this->write($message);
    }

    public function error(string $message, Throwable $exception): void
    {
        $this->write("[ERROR] $message | {$exception->getMessage()} {$exception->getTraceAsString()}");
    }

    protected function write(string $message): void
    {
        $date = (new DateTime())->format('Y-m-d H:i:s');
        if (!$this->logFile) {
            $prefix = Craft::$app->getPath()->getLogPath();
            $file = 'ticketmaster-boplex.log';
            $this->logFile = "$prefix/$file";
        }
        $content = "[$date] $message\n";
        FileHelper::writeToFile($this->logFile, $content, ['append' => true]);
    }
}