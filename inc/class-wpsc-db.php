<?php

class WPSC_DB
{
	private $db;
	private $table_name = "wpsc_links";
	function __construct()
	{
		global $wpdb;
		$this->db = $wpdb;
	}

	function create_table(){
		if($this->db->get_var("SHOW TABLES LIKE '$this->table_name'") == $this->table_name){
			return;
		}

		$sql = "
			CREATE TABLE $this->table_name (
				id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
				name TINYTEXT NOT NULL,
				slug TINYTEXT NOT NULL,
				url TINYTEXT NOT NULL,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				status MEDIUMINT(9) NOT NULL,
				UNIQUE KEY id (id)
			);
		";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	function add($name, $slug = '', $url, $status = 302){
		if(empty($slug))
			$slug = $name;
		$slug = $this->slugify($slug);

		$count = $this->db->get_var( "SELECT COUNT(*) FROM $this->table_name WHERE slug='$slug'" );
		
		while($count){
			$slug = $this->increment_string($slug);
			$count = $this->db->get_var( "SELECT COUNT(*) FROM $this->table_name WHERE slug='$slug'" );
		}

		return $this->db->insert( $this->table_name, array('name'=>$name, 'slug'=>$slug, 'url'=>$url, 'time'=>current_time('mysql'), 'status'=>$status) );
	}

	function get($id){
		$sql = "SELECT * FROM $this->table_name WHERE id=$id";
		return $this->db->get_row($sql, ARRAY_A);
	}

	function get_by_slug($slug){
		$sql = "SELECT * FROM $this->table_name WHERE slug='$slug'";
		return $this->db->get_row($sql, ARRAY_A);
	}

	function update($id, $name, $slug = '', $url, $status = 302){
		if(empty($slug))
			$slug = $name;
		$slug = $this->slugify($slug);

		$count = $this->db->get_var( "SELECT COUNT(*) FROM $this->table_name WHERE slug='$slug' AND id != $id" );
		
		while($count){
			$slug = $this->increment_string($slug);
			$count = $this->db->get_var( "SELECT COUNT(*) FROM $this->table_name WHERE slug='$slug' AND id != $id" );
		}

		return $this->db->update( $this->table_name, array('name'=>$name, 'slug'=>$slug, 'url'=>$url, 'status'=>$status), array('id'=>$id) );
	}

	function get_all($curr_page, $per_page, $orderby, $order){
		$start = ($curr_page-1) * $per_page;
		$query = "SELECT * FROM $this->table_name ORDER BY $orderby $order LIMIT $start, $per_page";
		return $this->db->get_results( $query, ARRAY_A );
	}

	function delete($id){
		return $this->db->delete( $this->table_name, array('id'=>$id) );
	}

	function delete_multiple($ids){
		$id_string = join(',', $ids);
		$query = "DELETE FROM $this->table_name WHERE id IN ($id_string)";
		$this->db->query($query);
	}

	function get_total_count(){
		$count = $this->db->get_var("SELECT COUNT(*) FROM $this->table_name");
		return isset($count)?$count:0;
	}

	function slugify($text)
	{ 
		// replace non letter or digits by -
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);
		// trim
		$text = trim($text, '-');
		// lowercase
		$text = strtolower($text);
		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);
		if (empty($text))
		{
			return false;
		}
		return $text;
	}

	function increment_string($str, $separator = '-', $first = 1)
	{
	    preg_match('/(.+)'.$separator.'([0-9]+)$/', $str, $match);
	    return isset($match[2]) ? $match[1].$separator.($match[2] + 1) : $str.$separator.$first;
	}
}