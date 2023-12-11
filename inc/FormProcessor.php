<?php
require_once('MessageDisplay.php');
require_once('SanitiseProcessor.php');

// FormProcessor.php
class FormProcessor
{

    public  $getCleanedID; // Variable to store the cleaned ID

    public function __construct()
    {
        // Initializing required classes
        $this->sanitiseProcessor = new SanitiseProcessor();
        $this->msgDisplay = new MessageDisplay();
    }

    public function processForm(): void
    {
        $current_user = wp_get_current_user(); // Fetching the current user

        // Verify the validity of the submitted Nonce
        if (wp_verify_nonce($_POST['pokeNonce'], 'verifyPokemonID') && current_user_can('manage_options')) {

            // Retrieving only numeric values from the cleaned ID
            $this->sanitiseProcessor->setPokemonIDs($_POST['pokemon_ids']);

            $cleaned_id = $this->sanitiseProcessor->getNumericValues(); // Storing the cleaned ID for later use

            $this->getCleanedID = $cleaned_id;

            if (!empty($this->getCleanedID)) {
                $this->msgDisplay->showMessage(
                    'Congratulations ',
                    $current_user->display_name,
                    'your search was successful!',
                    'success',
                    $this->sanitiseProcessor->nonNumericalValues
                );
            }
        } else {
            $this->msgDisplay->showMessage(
                'Sorry',
                $current_user->display_name,
                ' but you are not authorized to carry out this action',
                'danger',
                []
            );
        }
    }
}
