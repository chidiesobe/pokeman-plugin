<?php

/*
Plugin Name: Pokemon API Search
Description: A plugin that searches the PokemonAPI website and return results based on user inputed pokeman ID
version: 1.0
Author: Chidi E. Egwu
Author URI: https://github.com/chidiesobe
*/

if (!defined('ABSPATH')) exit; // Exit if access directly

class PokemonSearch
{
    protected string $getCleanedID;

    function __construct()
    {
        add_action('admin_menu', array($this, 'pokeAdminMenu'));
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
            'ID List',
            'manage_options',
            'pokemon-filter',
            array($this, 'pokemonFilterPage'),
            1
        );

        add_submenu_page(
            'pokemon-filter',
            'Pokemon Option',
            'Options',
            'manage_options',
            'pokemon-options',
            array($this, 'pokemonOptionPage'),
            2
        );
        //--- Submenu section ends

        // Adding Boostrap
        add_action("load-{$mainPage}", array($this, 'styleAccess'));
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

    function cleanPokemanID(string $ids = ""): string
    {
        // remove white space and ensure string integrity
        $input_ids = str_replace(' ', '', sanitize_text_field($ids));
        $input_ids = str_replace('.', '', sanitize_text_field($input_ids));

        // Check if the string starts or ends with a comma and remove it
        if (substr($input_ids, 0, 1) === ',')  $input_ids = substr($input_ids, 1);
        if (substr($input_ids, -1) === ',')  $input_ids = substr($input_ids, 0, -1);

        // Remove all non numberic values
        $exploded_string = explode(',', $input_ids);

        // Remove duplicates
        $unique_values = array_unique($exploded_string);

        $numeric_values = array_filter($unique_values, function ($value) {
            return is_numeric($value);
        });
        $clean_ids = implode(',', $numeric_values);
        return $clean_ids;
    }

    function pokemonApiCall(string $ids = ""): array
    {
        $clean_ids = explode(',', $ids);

        if (count($clean_ids) >= 1) {
            // call the Pokemon API and pass required IDs
            $api = function ($id) {
                $response = wp_remote_get("https://pokeapi.co/api/v2/pokemon/{$id}");
                $body = wp_remote_retrieve_body($response);

                $data = json_decode($body, true);

                // Extract the 'id', 'name', 'abilities', 'moves' and 'speicies' from the response
                $name = $data['name'] ?? '';

                $abilities = array_column($data['abilities'] ?? [], 'ability');
                $abilityName =  array_column($abilities, 'name');

                $moves = array_column($data['moves'] ?? [], 'move');
                $movesName = array_column($moves, 'name');

                return array(
                    'name' => $name, 'abilityName' => $abilityName,
                    'movesName' => $movesName,
                );
            };
            $result = array_map($api, $clean_ids);

            // return the extracted result from the api
            return $result;
        }
    }

    // Process the ID's form after submission
    function processForm(): void
    {
        $current_user = wp_get_current_user();

        if (wp_verify_nonce($_POST['pokeNonce'], 'verifyPokemonID') && current_user_can('manage_options')) {

            // Clean the supplied ID string
            $clean_ids = $this->cleanPokemanID($_POST['pokemon_ids']);
            $this->getCleanedID = $clean_ids;

            if (!empty($this->getCleanedID)) { ?>

                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Congratulations <?php echo $current_user->display_name . ', ' ?></strong>Your search was successful!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php
            }
        } else { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Sorry <?php echo $current_user->display_name . ', ' ?></strong>But your search was invalid!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php
        }
    }

    // Pokemon ID for Filtering
    function pokemonFilterPage(): void
    { ?>
        <div class="container mt-5">
            <div class="text-primary">
                <h3 class="text-primary">Pokemon Filter</h3>
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
                                    <li class="list-group-item">You are entitled to six IDs search by default.</li>
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
                $response = $this->pokemonApiCall($this->getCleanedID);
                echo '<div class="row">';
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
                echo '</div>';
            } else {
                echo 'No search yet';
            }

            ?>
        </div>

    <?php
    }

    // Pokemon search criteria
    function pokemonOptionPage(): void
    { ?>
        <div class="wrap">
            Options page
        </div>
<?php
    }
}

$pokemonSearch = new PokemonSearch();
