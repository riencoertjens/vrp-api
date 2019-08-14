<?php

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Submissions_Overview_List_Table extends WP_List_Table {

  function __construct(){
    global $status, $page;

    //Set parent defaults
    parent::__construct(array(
      'singular'  => 'registratie',     //singular name of the listed records
      'plural'    => 'registraties',    //plural name of the listed records
      'ajax'      => false              //does this table support ajax?
    ));
  }

  function get_columns(){
    $columns = array(
      // 'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
      'post_title' => 'Title',
      'count' => 'Registraties',
      'close_date' => 'Einde datum'
    );
    return $columns;
  }

  function get_sortable_columns() {
    $sortable_columns = array(
      'post_title' => array('title',false),     //true means it's already sorted
      // 'close_date' => array('close_date',false)
    );
    return $sortable_columns;
  }

  function column_default($item, $column_name){
    if ($column_name == 'count'){
      $args = array(
        'post_type' => 'registratie',
        'meta_query' => array(
          array(
            'key' => 'activity_id',
            'value' => $item->ID,
            )
          )
      );

      $query = new WP_Query($args);

      $label = $query->found_posts;
      $places = get_field('places', $item->ID);
      if ($places > 0){ $label .= '/'.$places;}
      return $label;
    } elseif ($column_name == 'close_date'){
      return get_field('close_date', $item->ID);
    } else {
      return '<a href="edit.php?post_type=activiteit&page=registraties&activity_id='.$item->ID.'">'.$item->$column_name.'</a>';
    }
  }

  function prepare_items() {

    $per_page = 10;

    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();

    $this->_column_headers = array($columns, $hidden, $sortable);

    $data = get_posts(array(
      'post_type' => 'activiteit',
      'meta_key'		=> 'hasform',
      'meta_value'	=> true,
      'orderby' => (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title', //If no sort, default to title
      'order' => (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc' //If no order, default to asc
    ));

    $current_page = $this->get_pagenum();
    $total_items = count($data);

    $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
    $this->items = $data;

    $this->set_pagination_args( array(
      'total_items' => $total_items,                  //WE have to calculate the total number of items
      'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
      'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
  ) );
  }
}
