<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if(!class_exists('WPSC_DB')){
    require_once( 'class-wpsc-db.php' );
}

class WPSC_Table extends WP_List_Table {
    private $db;

    function __construct(){
        global $status, $page;
        $this->db = new WPSC_DB();
        parent::__construct( array(
            'singular'  => 'link',     //singular name of the listed records
            'plural'    => 'links',    //plural name of the listed records
            'ajax'      => false,        //does this table support ajax?
            'screen'    => $_REQUEST['page']
        ) );
    }
    
    function column_default($item, $column_name){
        return $item[$column_name];
    }

    function column_name($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&link=%s">Edit</a>', $_REQUEST['page'],'edit',$item['id']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&link[]=%s">Delete</a>%s', $_REQUEST['page'],'delete',$item['id'], sprintf('<input type="hidden" class="link-id" value="%s"/>', $item['id']))
        );
        
        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ $item['name'],
            /*$2%s*/ $this->row_actions($actions)
        );
    }

    function column_result($item){
        return sprintf('<a href="%s">%s</a>', get_site_url(null, "/visit/".$item['slug'].'/'), get_site_url(null, "/visit/".$item['slug'].'/'));
    }

    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }
    
    function get_columns(){
        $columns = array(
            'cb'    => '<input type="checkbox" />',
            'name'  => 'Name',
            'slug'  => 'Slug',
            'url'   => 'URL',
            'status'=> 'Type',
            'result'=> 'Result'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'time'     => array('time', true),     //true means it's already sorted
        );
        return $sortable_columns;
    }
    
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action() {
        global $wpdb;
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() && isset($_GET['link']) && is_array($_GET['link']) && count($_GET['link'])) {
            $this->db->delete_multiple($_GET['link']);
        }
    }
    
    function prepare_items() {
        $this->process_bulk_action();

        $per_page               = 20;
        $hidden                 = array();
        $orderby                = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'time';
        $order                  = (!empty($_GET['order'])) ? $_GET['order'] : 'desc';
        $columns                = $this->get_columns();
        $sortable               = $this->get_sortable_columns();
        $curr_page              = $this->get_pagenum();
        $total_items            = $this->db->get_total_count();
        $data                   = $this->db->get_all($curr_page, $per_page, $orderby, $order);
        $this->items            = $data;
        $this->_column_headers  = array($columns, $hidden, $sortable);
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}