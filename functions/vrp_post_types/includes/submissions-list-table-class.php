<?php

if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
class Submissions_List_Table extends WP_List_Table
{

  private $columns;
  private $choices;
  private $activity;

  function __construct($activity_id)
  {
    global $status, $page;

    //Set parent defaults
    parent::__construct(array(
      'singular'  => 'registratie',     //singular name of the listed records
      'plural'    => 'registraties',    //plural name of the listed records
      'ajax'      => false              //does this table support ajax?
    ));

    $this->activity = get_post($activity_id);
    $forms = get_field('register_form', $this->activity);

    $_columns = array();
    $_choices = array();

    $_columns['post_date'] = 'Datum';

    foreach ($forms as $form) {
      $post_content = json_decode($form->post_content);


      foreach ($post_content->fields as $field) {
        $column_id = $post_content->id . '_' . $field->id;
        if ($field->type == "checkbox") {
          foreach ($field->choices as $key => $choice) {
            $_columns[$column_id . '_' . $key] = $choice->label;
          }
        } else {
          $_columns[$column_id] = $field->label;
          if (isset($field->choices)) {
            $_choices[$column_id] = $field->choices;
          }
        }
      }
    }

    $this->columns = $_columns;
    $this->choices = $_choices;
  }

  // function column_cb($item){
  //   return sprintf(
  //     '<input type="checkbox" name="%1$s" value="%2$s" />',
  //     /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
  //     /*$2%s*/ $item->ID                //The value of the checkbox should be the record's id
  //   );
  // }

  function get_columns()
  {
    return $this->columns;
  }

  function get_sortable_columns()
  {
    $sortable_columns = array(
      // 'post_title' => array('title',false),     //true means it's already sorted
      // 'post_date' => array('rating',false)
    );
    return $sortable_columns;
  }

  function column_default($item, $column_name)
  {
    
    if ($column_name === 'post_date') {
      return date("Y-m-d H:i:s", strtotime("-2h",strtotime(get_the_date( 'Y-m-d H:i:s', $item ))));
    } else {
      $post_content = json_decode($item->post_content, true);
      if(isset($post_content[$column_name])){ //check for value, in case form has been changed during registrations
        $value = $post_content[$column_name];
        $isCheckbox = substr_count($column_name, '_') == 2;
        if (!$isCheckbox && $value === null) {
          $label = "n/a";
        } elseif ($isCheckbox) {
          $label = ($post_content[$column_name] == 1 || $post_content[$column_name] == 'on') ? '✔' : '—';
        } elseif (isset($this->choices[$column_name])) {
          $value = $value + 1;
          $label = $this->choices[$column_name]->$value->label;
        } else {
          $label = $post_content[$column_name];
        }
      } else {
        $label = "—";
      }
    }


    return $label;
  }

  function prepare_items()
  {

    $per_page = 50;

    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $current_page = $this->get_pagenum();

    $this->_column_headers = array($columns, $hidden, $sortable);

    $args = array(
      'post_type' => 'registratie',
      'meta_query' => array(
        array(
          'key' => 'activity_id',
          'value' => $this->activity->ID,
        )
      ),
      'posts_per_page' => $per_page,
      'paged' => $current_page,
      'orderby' => 'post_date',
      'order' => 'asc'
    );

    $query = new WP_Query($args);
    $data = $query->posts;

    $this->items = $data;

    $this->set_pagination_args(array(
      'total_items' => $query->found_posts,  //WE have to calculate the total number of items
      'per_page'    => $per_page,            //WE have to determine how many items to show on a page
      'total_pages' => $query->max_num_pages //WE have to calculate the total number of pages
    ));
  }

  function export_csv()
  {
    ob_start();

    $columns = $this->get_columns();

    $args = array(
      'post_type' => 'registratie',
      'meta_query' => array(
        array(
          'key' => 'activity_id',
          'value' => $this->activity->ID,
        )
      ),
      'posts_per_page' => -1,
      'orderby' => 'post_date',
      'order' => 'asc'
    );

    $query = new WP_Query($args);
    $data = $query->posts;


    // if registrations
    $filename = $this->activity->post_name . ".csv";

    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-type: text/csv');
    header("Content-Disposition: attachment; filename={$filename}");
    header('Expires: 0');
    header('Pragma: public');

    //open stream  
    $file = fopen('php://output', 'w');
    // $file = fopen("../wp-content/exports/" . $this->activity->post_name . ".csv", "w");

    // Add Headers
    fputcsv($file, array_values($columns));

    foreach ($data as $item) {
      $row = [];
      foreach ($columns as $column_name => $column_label) {
        $row[] = $this->column_default($item, $column_name);
      }
      fputcsv($file, $row);
    }

    fclose($file);

    ob_end_flush();

    die();
  }
}
