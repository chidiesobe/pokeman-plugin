<?php

require_once('MessageDisplay.php');
require_once('SanitiseProcessor.php');

class ApiProcessor
{
    private $msgDisplay;
    private $apiResultArray;

    public function __construct()
    {
        $this->msgDisplay = new MessageDisplay();
        $this->sanitiseProcessor = new SanitiseProcessor();
    }

    // Getter and Setter Starts
    public function setSecureAPI(string $url): void
    {
        $this->secureAPI($url);
    }

    public function getPokemonApiCall(): array
    {
        return $this->apiResultArray;
    }

    public function setPokemonApiCall(string $ids): void
    {
        $this->pokemonApiCall($ids);
    }

    // Getter and Setter Ends



    /**
     * Private methods
     * Storing the API URL 
     * Calling the  Pokemon API
     * @param string $url
     * @return void
     */

    private function secureAPI(string $url): void
    {
        if (wp_verify_nonce($_POST['pokeAPINonce'], 'verifyAPIURL') && current_user_can('manage_options')) {

            $api_data = esc_url($url);

            update_option('secure_api_data', $api_data);

            $this->msgDisplay->showMessage('', '', 'API Url stored successfully!', 'warning',  []);
        } else {
            $this->msgDisplay->showMessage('Sorry', '', ' but you are not authorized to carry out this action', 'danger', []);
        }
    }

    private function pokemonApiCall(string $ids = ""): void
    {
        $clean_ids = explode(',', $ids);

        if (count($clean_ids) >= 1) {
            // call the Pokemon API and pass required IDs
            $api = function ($id) {

                // Get the API URL from the options table and make GET request
                $api_url = get_option('secure_api_data');
                $request_url = $api_url . $id;
                $response = wp_remote_get($request_url);

                $body = wp_remote_retrieve_body($response);

                $data = json_decode($body, true);

                // Extract the 'id', 'name', 'abilities', 'moves' and 'speicies' from the response
                $name = $data['name'] ?? '';

                $abilities = array_column($data['abilities'] ?? [], 'ability');
                $abilityName =  array_column($abilities, 'name');

                $moves = array_column($data['moves'] ?? [], 'move');
                $movesName = array_column($moves, 'name');

                if (empty($name) && !empty($this->sanitiseProcessor->getNumericValues())) {
                    $this->logsProcessor->writeToLog($id);
                }

                return array(
                    'name' => $name, 'abilityName' => $abilityName,
                    'movesName' => $movesName,
                );
            };

            $result = array_map($api, $clean_ids);

            $this->apiResultArray = $result;
        }
    }
}
