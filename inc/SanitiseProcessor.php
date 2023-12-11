<?php
require_once('LogsProcessor.php');

class SanitiseProcessor
{
    private $numericValues;
    public $nonNumericalValues;

    public function __construct()
    {
        $this->logsProcessor = new LogsProcessor(); // Initialize LogsProcessor
    }

    // Returns mixed type to accommodate both numeric and non-numeric values
    public function getNumericValues(): mixed
    {
        return $this->numericValues;
    }

    // Accepts a string of Pokemon IDs, sanitizes, and sets the numeric values
    public function setPokemonIDs(string $ids): void
    {
        $this->cleanPokemanID($ids);
    }

    private function cleanPokemanID(string $ids = ""): void
    {
        // Remove white space and ensure string integrity
        $input_ids = str_replace(' ', '', sanitize_text_field($ids));
        $input_ids = str_replace('.', '', sanitize_text_field($input_ids));

        // Check if the string starts or ends with a comma and remove it
        if (substr($input_ids, 0, 1) === ',')  $input_ids = substr($input_ids, 1);
        if (substr($input_ids, -1) === ',')  $input_ids = substr($input_ids, 0, -1);

        // Remove all non numberic values
        $exploded_string = explode(',', $input_ids);

        // Remove duplicates
        $unique_values = array_unique($exploded_string);

        // Retrieve numeric values that are not present in the logs file
        $log_numbers = file($this->logsProcessor->getLogFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $non_logged_number = array_filter($unique_values, function ($values) use ($log_numbers) {
            return !in_array($values, $log_numbers);
        });

        // Separating numeric and non-numeric values
        $this->numericValues = array_filter($non_logged_number, function ($value) {
            return is_numeric($value);
        });
        $this->nonNumericalValues = array_diff($non_logged_number, $this->numericValues);

        // Convert the numeric values back to a string for storage or further processing
        $this->numericValues = implode(',', $this->numericValues);
        // $this->numericValues = $clean_ids;
    }
}
