<?php
/*
Plugin Name: Pokemon API Search
Description: A plugin that searches the PokemonAPI website and return results based on user inputed pokeman ID
version: 1.0
Author: Chidi E. Egwu
Author URI: https://github.com/chidiesobe
*/

if (!defined('ABSPATH')) exit; // Exit if access directly

require_once(plugin_dir_path(__FILE__) . 'inc/MessageDisplay.php');

class PokemonSearch
{
    protected string $getCleanedID;
    protected array $numeric_values;
    protected array $nonNumericalValues;


    function __construct()
    {
        add_action('admin_menu', array($this, 'pokeAdminMenu'));

        // instatiating class 
        $this->msgDisplay = new MessageDisplay();
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
            array($this, 'pokemonOptionPage'),
            2
        );
        //--- Submenu section ends

        // Adding Boostrap To Pages
        add_action("load-{$mainPage}", array($this, 'styleAccess'));
        add_action("load-{$logPage}", array($this, 'styleAccess'));
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

    function getLogFile()
    {
        return plugin_dir_path(__FILE__) . 'logs/logs.txt';
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

        // Remove numbers that exist in the logs file
        $log_numbers = file($this->getLogFile(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $non_logged_number = array_filter($unique_values, function ($values) use ($log_numbers) {
            return !in_array($values, $log_numbers);
        });

        // Seperate numerical and non numerical values 
        $this->numeric_values = array_filter($non_logged_number, function ($value) {
            return is_numeric($value);
        });
        $this->nonNumericalValues = array_diff($non_logged_number, $this->numeric_values);

        $clean_ids = implode(',', $this->numeric_values);
        return $clean_ids;
    }

    function writeToLog(int $id): void
    {
        // Open or create file if does not exist
        $file = fopen($this->getLogFile(), 'a');

        if ($file) {
            // Write data to end of the file
            fwrite($file, $id . PHP_EOL);
            // Close the file
            fclose($file);
        } else {
            // Handle error if file couldn't be opened
            echo "Unable to open file!";
        }
    }

    function processLogs($logs)
    {
        $file_path = $this->getLogFile();

        file_put_contents($file_path, '');

        // format the string using regular express and append a new line
        $trimmed_string = preg_replace('/\h+/', ' ', $logs);
        $normalized_string = preg_replace('/(?<=\d)\s+(?=\d)/m', "\n", $trimmed_string);
        $final_string = ltrim($normalized_string) . "\n";

        file_put_contents($file_path, $final_string);
        $this->msgDisplay->showMessage('', '', 'Log file has been updated successfully!', 'warning',  []);
    }

    function pokemonApiCall(string $ids = "")
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

                if (empty($name) && !empty($this->numeric_values)) {
                    $this->writeToLog($id);
                }

                return array(
                    'name' => $name, 'abilityName' => $abilityName,
                    'movesName' => $movesName,
                );
            };
            // if (isset($api[0]["name"])) {
            $result = array_map($api, $clean_ids);

            // return the extracted result from the api
            return $result;
            // }
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

            if (!empty($this->getCleanedID)) {
                $this->msgDisplay->showMessage('Congratulations ', $current_user->display_name, 'your search was successful!', 'success',  $this->nonNumericalValues);
            }
        } else {
            $this->msgDisplay->showMessage('Sorry', $current_user->display_name, ' but you are not authorized to carry out this action', 'danger', []);
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
                if (!empty($this->numeric_values)) {
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
                    $this->processLogs($_POST['log_ids']);
                }
                ?>
                <br>
                <!-- Verify submited input  -->
                <input type="hidden" name="form_submitted" value="true">
                <?php wp_nonce_field('verifyPokemonID', 'pokeNonce') ?>

                <label for="log_ids" class="form-label">
                    <p class="mb-1 text-danger"><strong>All ID's must be entered on a newline:</strong></p>
                </label>
                <textarea class="form-control mb-2" name="log_ids" rows="15">
                    <?php
                    $file_content = file_get_contents($this->getLogFile());
                    echo  esc_textarea($file_content);
                    ?>
                </textarea>
                <input type="submit" name="submit" value="Update Log" class="btn btn-sm btn-secondary">
            </form>
        </div>
<?php
    }
}

$pokemonSearch = new PokemonSearch();
