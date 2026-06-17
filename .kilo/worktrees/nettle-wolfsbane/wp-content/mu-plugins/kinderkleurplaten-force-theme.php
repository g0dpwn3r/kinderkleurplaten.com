<?php
/**
 * Plugin Name: Kinderkleurplaten Force Theme
 * Description: Forces the working Kinderkleurplaten theme so the site uses it even before it is activated in the database.
 * Version: 1.0.0
 * Author: Kinderkleurplaten.nl
 */

if (!defined('ABSPATH')) {
	exit;
}

add_filter('template', function () {
	return 'kinderkleurplaten';
});

add_filter('stylesheet', function () {
	return 'kinderkleurplaten';
});
