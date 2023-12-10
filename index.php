<?php
/*
Plugin Name: Pokemon API Search
Description: A plugin that searches the PokemonAPI website and return results based on user inputed pokeman ID
version: 1.0
Author: Chidi E. Egwu
Author URI: https://github.com/chidiesobe
*/

if (!defined('ABSPATH')) exit; // Exit if access directly

require_once(plugin_dir_path(__FILE__) . 'inc/ApiProcessor.php');
require_once(plugin_dir_path(__FILE__) . 'inc/LogsProcessor.php');
require_once(plugin_dir_path(__FILE__) . 'inc/MessageDisplay.php');
require_once(plugin_dir_path(__FILE__) . 'inc/SanitiseProcessor.php');

class PokemonSearch
{
    protected string $getCleanedID;
    // protected array $numeric_values;
    // protected array $nonNumericalValues = [];


    function __construct()
    {
        add_action('admin_menu', array($this, 'pokeAdminMenu'));

        // instatiating class 
        $this->apiProcessor = new ApiProcessor();
        $this->msgDisplay = new MessageDisplay();
        $this->logsProcessor = new LogsProcessor();
        $this->apiPreprocessor = new ApiProcessor();
        $this->sanitiseProcessor = new SanitiseProcessor();
    }

    // Main menu
    function pokeAdminMenu(): void
    {
        $mainPage = add_menu_page(
            'Pokemon Search by ID',
            'Pokemon Filter',
            'manage_options',
            'pokemon-filter',
            array($this, 'pokemonFilterPage'),
            'dashicons-superhero',
            100
        );
        //--- Submenu section starts
        add_submenu_page(
            'pokemon-filter',
            'ID To Filter',
            'Pokemon',
            'manage_options',
            'pokemon-filter',
            array($this, 'pokemonFilterPage'),
            1
        );

        $logPage = add_submenu_page(
            'pokemon-filter',
            'Pokemon Option',
            'Logs',
            'manage_options',
            'pokemon-logs',
            array($this, 'pokemonLogsPage'),
            2
        );

        $apiPage = add_submenu_page(
            'pokemon-filter',
            'Api Option',
            'API Value',
            'manage_options',
            'api-url',
            array($this, 'apiUrlPage'),
            3
        );
        //--- Submenu section ends

        // Adding Boostrap To Pages
        add_action("load-{$mainPage}", array($this, 'styleAccess'));
        add_action("load-{$logPage}", array($this, 'styleAccess'));
        add_action("load-{$apiPage}", array($this, 'styleAccess'));
    }

    // Boostrap access
    function styleAccess(): void
    {
        wp_enqueue_style(
            'boostrapCSS',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
        );

        wp_enqueue_script(
            'bootstrapJS',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js',
        );
    }

    // Process the ID's form after submission
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

    // Pokemon ID for Filtering
    function pokemonFilterPage(): void
    { ?>
        <div class="container mt-3">
            <div class="text-primary">
                <h4 class="text-primary">Pokemon Filter</h4>
                <div class="accordion" id="accordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                <span class="text-danger">Kindly, make yourself familiar with the Pokemon Search Guide</span>
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordion">
                            <div class="accordion-body">
                                <ul class="list-group">
                                    <li class="list-group-item">Only enter whole numbers.</li>
                                    <li class="list-group-item">All values must be unique.</li>
                                    <li class="list-group-item">All Pokemon IDs must be numeric.</li>
                                    <li class="list-group-item">Do not end a search with a comma.</li>
                                    <li class="list-group-item">Do not start a search with a comma.</li>
                                    <li class="list-group-item">You do not need to leave white spaces between IDs for clearity.</li>

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <br>
            <form method="POST">
                <!-- if user submits the form -->
                <?php
                if (
                    $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['pokemon_ids']) && isset($_POST['pokemon_ids'])
                    && isset($_POST['form_submitted']) && $_POST['form_submitted'] == "true"
                ) {
                    $this->processForm();
                }
                ?>
                <br>
                <!-- Verify submited input  -->
                <input type="hidden" name="form_submitted" value="true">
                <?php wp_nonce_field('verifyPokemonID', 'pokeNonce') ?>

                <label for="pokemon_ids" class="form-label">
                    <p class="mb-1"><strong>Please Enter Your Preferred Comma-Separated Pokeman ID's:</strong></p>
                </label>
                <textarea class="form-control mb-2" name="pokemon_ids" placeholder="Samples IDs 3,30,2"></textarea>
                <input type="submit" name="submit" value="Search" class="btn btn-sm btn-secondary">
            </form>
            <br>
            <!-- Result of the search -->
            <?php

            if (isset($this->getCleanedID)) {

                $this->apiPreprocessor->setPokemonApiCall($this->getCleanedID);
                $response =  $this->apiPreprocessor->getPokemonApiCall();

                if (!empty($this->sanitiseProcessor->getNumericValues())) {
                    echo '<div class="row">';
                    // var_dump($response);
                    foreach ($response as $pokemon) {
            ?>
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Pokemon's Name:<?= ' ' . ucfirst($pokemon['name']) ?></h5>
                                    <p class="card-text"><?= ' ' . ucfirst($pokemon['name']) ?> is a Pokemon with <strong><?= count($pokemon['movesName']) ?></strong> moves, with the following abilities: <strong><?= ' ' . implode(',', $pokemon['abilityName']) . '.' ?></strong></p>
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#<?= $pokemon['name'] ?>">
                                        show all moves
                                    </button>
                                    <div class="modal fade" id="<?= $pokemon['name'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">All <?= ' ' . ucfirst($pokemon['name']) ?> Moves</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="text-center"><?= implode(',', $pokemon['movesName']) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
            <?php
                    }
                }
                echo '</div>';
            } else {
                echo 'No search yet';
            }

            ?>
        </div>

    <?php
    }

    // Pokemon search criteria
    function pokemonLogsPage(): void
    { ?>
        <div class="container mt-3">
            <div class="text-primary">
                <h4 class="text-primary">Pokemon Logs</h4>
            </div>

            <form method="POST">
                <!-- if user submits the form -->
                <?php
                if (
                    $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['log_ids']) && isset($_POST['log_ids'])
                    && isset($_POST['form_submitted']) && $_POST['form_submitted'] == "true"
                ) {
                    $this->logsProcessor->processLogs($_POST['log_ids']);
                }
                ?>
                <br>
                <!-- Verify submited input  -->
                <input type="hidden" name="form_submitted" value="true">
                <?php wp_nonce_field('verifyLogIDs', 'pokeLogNonce') ?>

                <label for="log_ids" class="form-label">
                    <p class="mb-1 text-danger"><strong>All ID's must be entered on a newline:</strong></p>
                </label>
                <textarea class="form-control mb-2" name="log_ids" rows="15">
                    <?php
                    $file_content = file_get_contents($this->logsProcessor->getLogFilePath);
                    echo  esc_textarea($file_content);
                    ?>
                </textarea>
                <input type="submit" name="submit" value="Update Log" class="btn btn-sm btn-secondary">
            </form>
        </div>
    <?php
    }


    function apiUrlPage(): void
    { ?>
        <div class="container mt-3">
            <div class="text-primary">
                <h4 class="text-primary">API URL</h4>
            </div>

            <form method="POST">
                <!-- if user submits the form -->
                <?php
                if (
                    $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['api_url']) && isset($_POST['api_url'])
                    && isset($_POST['form_submitted']) && $_POST['form_submitted'] == "true"
                ) {
                    $this->apiProcessor->setSecureAPI($_POST['api_url']);
                }
                ?>
                <br>
                <!-- Verify submited input  -->
                <input type="hidden" name="form_submitted" value="true">
                <?php wp_nonce_field('verifyAPIURL', 'pokeAPINonce') ?>

                <label for="api_url" class="form-label">
                    <p class="mb-1 text-danger"><strong>Store API URL</strong></p>
                </label>
                <input class="form-control mb-2" name="api_url" value=<?php echo get_option('secure_api_data') ?> />

                <input type="submit" name="submit" value="Store API URL" class="btn btn-sm btn-danger">
            </form>
        </div>
<?php
    }
}

$pokemonSearch = new PokemonSearch();
