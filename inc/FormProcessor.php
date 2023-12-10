<?php
require_once('MessageDisplay.php');
require_once('SanitiseProcessor.php');

class FormProcessor
{
    private string $cleanedID = '';

    // Getter and Setter Starts
    public function __construct()
    {
        $this->messageDisplay = new MessageDisplay();
        $this->sanitiseProcessor = new SanitiseProcessor();
    }

    public function getCleanedID(): string
    {
        return $this->cleanedID;
    }

    public function setProcessForm(): void
    {
        $this->processForm();
    }
    // Getter and Setter Ends

    private function processForm(): void
    {
        $current_user = wp_get_current_user();

        if (wp_verify_nonce($_POST['pokeNonce'], 'verifyPokemonID') && current_user_can('manage_options')) {

            // Clean the supplied ID string 
            $this->sanitiseProcessor->setPokemonIDs($_POST['pokemon_ids']);
            $cleaned_id = $this->sanitiseProcessor->getNumericValues();

            $this->cleanedID = $cleaned_id;

            if (!empty($this->cleanedID)) {
                $this->messageDisplay->showMessage(
                    'Congratulations ',
                    $current_user->display_name,
                    'your search was successful!',
                    'success',
                    $this->sanitiseProcessor->nonNumericalValues
                );
            }
        } else {
            $this->messageDisplay->showMessage(
                'Sorry',
                $current_user->display_name,
                ' but you are not authorized to carry out this action',
                'danger',
                []
            );
        }
    }
}
