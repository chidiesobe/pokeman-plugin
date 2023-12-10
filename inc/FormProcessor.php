<?php
require_once('MessageDisplay.php');
require_once('SanitiseProcessor.php');

// FormProcessor.php
class FormProcessor
{

    public  $getCleanedID;

    public function __construct()
    {
        $this->sanitiseProcessor = new SanitiseProcessor();
        $this->msgDisplay = new MessageDisplay();
    }

    function processForm(): void
    {
        $current_user = wp_get_current_user();

        if (wp_verify_nonce($_POST['pokeNonce'], 'verifyPokemonID') && current_user_can('manage_options')) {

            // Clean the supplied ID string 
            $this->sanitiseProcessor->setPokemonIDs($_POST['pokemon_ids']);

            $cleaned_id = $this->sanitiseProcessor->getNumericValues();

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
