<?php

require_once('LogsProcessor.php');
require_once('MessageDisplay.php');
require_once('SanitiseProcessor.php');

class ApiProcessor
{
    private $msgDisplay;
    private $apiResultArray;

    public function __construct()
    {
        // Initializing required classes
        $this->msgDisplay = new MessageDisplay();
        $this->sanitiseProcessor = new SanitiseProcessor();
        $this->logsProcessor = new LogsProcessor();
    }

    // Getter and Setter methods

    public function setSecureAPI(string $url): void
    {
        // Method to securely store the API URL
        $this->secureAPI($url);
    }

    public function getPokemonApiCall(): array
    {
        // Getter method to fetch Pokemon API call result
        return $this->apiResultArray;
    }

    public function setPokemonApiCall(string $ids): void
    {
        // Method to initiate a call to the Pokemon API
        $this->pokemonApiCall($ids);
    }

    // Private methods

    /**
     * Storing the API URL securely
     * @param string $url
     * @return void
     */

    private function secureAPI(string $url): void
    {
        // Verifying the nonce and user role
        if (wp_verify_nonce($_POST['pokeAPINonce'], 'verifyAPIURL') && current_user_can('manage_options')) {

            // Escaping and updating the API URL option
            $api_data = esc_url($url);
            update_option('secure_api_data', $api_data);

            $this->msgDisplay->showMessage('', '', 'API Url stored successfully!', 'warning',  []);
        } else {
            $this->msgDisplay->showMessage('Sorry', '', ' but you are not authorized to carry out this action', 'danger', []);
        }
    }

    /**
     * Calling the Pokemon API based on provided IDs
     * @param string $ids
     * @return void
     */
    private function pokemonApiCall(string $ids = ""): void
    {
        // Splitting the enterd IDs
        $clean_ids = explode(',', $ids);

        if (count($clean_ids) >= 1) {
            // API call function for each ID
            $api = function ($id) {

                // Making a GET request to the Pokemon API
                $api_url = get_option('secure_api_data');
                $request_url = $api_url . $id;
                $response = wp_remote_get($request_url);
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);

                // Extracting required data from the API response
                $name = $data['name'] ?? '';

                $abilities = array_column($data['abilities'] ?? [], 'ability');
                $abilityName =  array_column($abilities, 'name');

                $moves = array_column($data['moves'] ?? [], 'move');
                $movesName = array_column($moves, 'name');

                // Logging if name is empty
                if (empty($name)) {
                    $this->logsProcessor->writeToLog($id);
                }

                return array(
                    'name' => $name, 'abilityName' => $abilityName,
                    'movesName' => $movesName,
                );
            };

            // Mapping API call function to each ID and storing results
            $result = array_map($api, $clean_ids);
            $this->apiResultArray = $result;
        }
    }
}
