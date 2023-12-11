<?php
/*
Plugin Name: Pokemon API Search
Description: A plugin that searches the PokemonAPI website and return results based on user inputed pokeman ID
version: 1.0
Author: Chidi E. Egwu
Author URI: https://github.com/chidiesobe
*/

if (!defined('ABSPATH')) exit; // Exit if access directly

// Required files
require_once(plugin_dir_path(__FILE__) . 'inc/ApiProcessor.php');
require_once(plugin_dir_path(__FILE__) . 'inc/LogsProcessor.php');
require_once(plugin_dir_path(__FILE__) . 'inc/FormProcessor.php');
require_once(plugin_dir_path(__FILE__) . 'inc/MessageDisplay.php');
require_once(plugin_dir_path(__FILE__) . 'inc/SanitiseProcessor.php');


class PokemonSearch
{

    function __construct()
    {
        // Add action to create the admin menu
        add_action('admin_menu', array($this, 'pokeAdminMenu'));

        // Instantiate necessary classes
        $this->apiProcessor = new ApiProcessor();
        $this->msgDisplay = new MessageDisplay();
        $this->logsProcessor = new LogsProcessor();
        $this->formProcessor = new FormProcessor();
        $this->apiPreprocessor = new ApiProcessor();
        $this->sanitiseProcessor = new SanitiseProcessor();
    }

    // Create admin menu and submenus
    function pokeAdminMenu(): void
    {
        // Main menu
        $mainPage = add_menu_page(
            'Pokemon Search by ID',
            'Pokemon Filter',
            'manage_options',
            'pokemon-filter',
            array($this, 'pokemonFilterPage'),
            'dashicons-superhero',
            100
        );

        // Submenus
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


        // Adding Boostrap To Pages
        add_action("load-{$mainPage}", array($this, 'styleAccess'));
        add_action("load-{$logPage}", array($this, 'styleAccess'));
        add_action("load-{$apiPage}", array($this, 'styleAccess'));
    }

    // Enqueue Bootstrap styles and scripts
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

    // Display Pokemon filtering page
    function pokemonFilterPage(): void
    {
        include(plugin_dir_path(__FILE__) . 'pages/pokemon_filter.php');
    }

    // Display Pokemon log page
    function pokemonLogsPage(): void
    {
        include(plugin_dir_path(__FILE__) . 'pages/pokemon_log.php');
    }

    // Display API URL page
    function apiUrlPage(): void
    {
        include(plugin_dir_path(__FILE__) . 'pages/api_url.php');
    }
}

// Instantiate the PokemonSearch class to initialize the plugin
$pokemonSearch = new PokemonSearch();
