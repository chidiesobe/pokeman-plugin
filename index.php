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
    function __construct()
    {
        add_action('admin_menu', array($this, 'pokeAdminMenu'));
    }

    // Main menu
    function pokeAdminMenu()
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
    function styleAccess()
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
    function processForm()
    {
        $current_user = wp_get_current_user();
        if (wp_verify_nonce($_POST['pokeNonce'], 'verifyPokemonID') && current_user_can('manage_options')) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Congratulations <?php echo $current_user->display_name . ', ' ?></strong>Your search was successful!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php
        } else { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Sorry <?php echo $current_user->display_name . ', ' ?></strong>But your search was invalid!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php
        }
    }

    // Pokemon ID for Filtering
    function pokemonFilterPage()
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
                    $_SERVER['REQUEST_METHOD'] == 'POST'
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
                <textarea class="form-control mb-2" name="pokemon_ids" id="pokemon_ids" placeholder="3,30,60,2"></textarea>
                <input type="submit" name="submit" value="Search" class="btn btn-sm btn-secondary">
            </form>

        </div>

    <?php
    }

    // Pokemon search criteria
    function pokemonOptionPage()
    { ?>
        <div class="wrap">
            Options page
        </div>
<?php
    }
}

$pokemonSearch = new PokemonSearch();
