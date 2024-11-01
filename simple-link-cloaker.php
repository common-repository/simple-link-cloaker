<?php
/*
Plugin Name: Simple Link Cloaker
Plugin URI: http://theaffiliatemixx.com/
Description: Helps you convert long and ugly affiliate links into short and elegant ones for aesthetic and SEO purposes.
Version: 1.0
Author: The Affiliate Mixx
Author URI: http://theaffiliatemixx.com/
License: GPL2
*/

if(!class_exists('WPSC_DB')){
	require_once( 'inc/class-wpsc-db.php' );
}

if(!class_exists('WPSC_Table')){
    require_once( 'inc/class-wpsc-table.php' );
}

function wpsc_install() {
	$db = new WPSC_DB();
	$db->create_table();
}
register_activation_hook( __FILE__, 'wpsc_install' );

add_action( 'init', 'wpsc_permalinks' );
function wpsc_permalinks() {
    add_rewrite_rule( '^visit/([^/]+)/?', 'index.php?wpsc_key=$matches[1]', 'top' );
	add_rewrite_rule( '^go/([^/]+)/?', 'index.php?wpsc_key=$matches[1]', 'top' );
	add_rewrite_rule( '^out/([^/]+)/?', 'index.php?wpsc_key=$matches[1]', 'top' );
	if(!get_option('wpsc_flushed')){
		flush_rewrite_rules(true);
		update_option( 'wpsc_flushed', 1 );
	}
}

add_filter( 'query_vars', 'wpsc_query_vars' );
function wpsc_query_vars( $query_vars ) {
    $query_vars[] = 'wpsc_key';
	return $query_vars;
}

add_action('template_redirect', 'wpsc_redirect', 1);
function wpsc_redirect() {
	$slug 		= get_query_var('wpsc_key');
	if($slug){
		$db 	= new WPSC_DB();
		$link_item 	= $db->get_by_slug($slug);
		if($link_item)
			wp_redirect( $link_item['url'], $link_item['status'] );
		else
			wp_die("No link found");
	}
}

function wpsc_add_link_ajax(){
	$db = new WPSC_DB();
	$result = $db->add($_POST['name'], $_POST['slug'], $_POST['url'], intval($_POST['status']));
	die(json_encode($result));
}
add_action( 'wp_ajax_wpsc_add_link', 'wpsc_add_link_ajax' );

function wpsc_delete_link_ajax(){
	$db = new WPSC_DB();
	$result = $db->delete($_POST['id']);
	die(json_encode($result));
}
add_action( 'wp_ajax_wpsc_delete_link', 'wpsc_delete_link_ajax');

function wpsc_get_table_html_ajax(){
	$linksTable = new WPSC_Table();
	$linksTable->prepare_items();
	$linksTable->display();
	die();
}
add_action( 'wp_ajax_wpsc_get_table_html', 'wpsc_get_table_html_ajax');

//Links page
include('inc/links-page.php');