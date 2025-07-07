<?php
/**
 * Plugin Name: Custom Callout Box Block
 */

function callout_block_register() {
    register_block_type(__DIR__);
}
add_action('init', 'callout_block_register');
