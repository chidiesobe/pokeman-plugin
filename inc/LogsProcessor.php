<?php
require_once('MessageDisplay.php');

class LogsProcessor
{
    private $msgDisplay;
    public $getLogFilePath;

    public function __construct()
    {
        $this->msgDisplay = new MessageDisplay();
        $this->getLogFilePath = $this->getLogFile();
    }

    private function getLogFile(): string
    {
        return  dirname(plugin_dir_path(__FILE__)) . '/logs/logs.txt';
    }

    private function writeToFile(string $content): bool
    {
        $file = fopen($this->getLogFilePath, 'a');
        if ($file) {
            fwrite($file, $content . PHP_EOL);
            fclose($file);
            return true;
        }
        return false;
    }

    public function writeToLog(string $id): void
    {
        $this->writeToFile($id);
    }


    public function processLogs(string $logs): bool
    {
        $currentUser = wp_get_current_user();

        if (wp_verify_nonce($_POST['pokeLogNonce'], 'verifyLogIDs') && current_user_can('manage_options')) {

            // Format Logs
            $formatLogs = preg_replace('/\h+/', ' ', $logs);
            $formatLogs = preg_replace('/(?<=\d)\s+(?=\d)/m', "\n", $formatLogs);
            $formattedLogs = ltrim($formatLogs) . "\n";

            if ($this->writeToFile($formattedLogs)) {
                // Clear log file before writing new content to it
                file_put_contents($this->getLogFilePath, '');
                file_put_contents($this->getLogFilePath, $formattedLogs);

                $this->msgDisplay->showMessage('', '', 'Log file has been updated successfully!', 'success',  []);
                return true;
            } else {
                $this->msgDisplay->showMessage('', '', 'Failed to format log file!', 'warning',  []);
                return false;
            }
        } else {
            $this->msgDisplay->showMessage('Sorry', $currentUser->display_name, ' but you are not authorized to carry out this action', 'danger', []);
            return false;
        }
    }
}
