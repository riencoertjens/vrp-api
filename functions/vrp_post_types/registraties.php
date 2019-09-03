<?php

// create custom post type for 'registratie'
function registratie_cpt()
{
  $args = array(
    'labels'  => array(
      'name' => 'registraties',
      'singular_name' => 'registratie',
    ),
    'public' => true,
    // 'show_in_menu' => false
  );
  register_post_type('registratie', $args);
}
add_action('init', 'registratie_cpt');


// create sub menu page for registraties in activity tab
function my_menu()
{
  add_submenu_page('edit.php?post_type=activiteit', 'Registraties', 'registraties', 'unfiltered_html', 'registraties', 'submissions_page_display');
}
add_action('admin_menu', 'my_menu');

// form submissions page
function submissions_page_display()
{
  // if activity is set, show submissions
  if (isset($_REQUEST['activity_id'])) {

    $activity = get_post($_REQUEST['activity_id']);

    if (!class_exists('Submissions_List_Table')) {
      require_once('includes/submissions-list-table-class.php');
    }

    $submissionsListTable = new Submissions_List_Table($activity->ID);
    $submissionsListTable->prepare_items();


    ?>
    <div class="wrap">
      <a href="edit.php?post_type=activiteit&page=registraties">terug naar overzicht</a><br />
      <h1>registraties: <a href="post.php?action=edit&post=<?= $activity->ID; ?>"><?= $activity->post_title; ?></a><br /><a href="<?php echo admin_url('admin.php?') ?>action=download_csv&_wpnonce=<?php echo wp_create_nonce('download_csv') ?>&activity_id=<?= $activity->ID; ?>" class="page-title-action">exporteer csv</a></h1>


      <!-- <a href="edit.php?post_type=activiteit&page=registraties&activity_id=<?= $activity->ID; ?>&export=1">exporteer csv</a> -->
      <?php $submissionsListTable->display(); ?>


    </div>
  <?php
      // if no activity is set, show all activities
    } else {

      if (!class_exists('Submissions_Overview_List_Table')) {
        require_once('includes/submissions-overview-list-table-class.php');
      }

      $submissionsOverviewListTable = new Submissions_Overview_List_Table();
      $submissionsOverviewListTable->prepare_items();

      ?>
    <div class="wrap">
      <h1>registraties</h1>
      <?php $submissionsOverviewListTable->display(); ?>
    </div>
<?php
  }
}

// add rest route to get registration count and limit
add_action('rest_api_init', function () {
  register_rest_route('vrp-api/v1', 'activity/submission-count/(?P<id>\d+)', array(
    'methods'  => WP_REST_Server::READABLE,
    'callback' => 'activity_submission_count'
  ));
});


// check+return registration count and limit
function activity_submission_count($data)
{
  $activity_id = $data['id'];

  // $hasform = get_field('hasform', $activity_id);

  $args = array(
    'post_type' => 'registratie',
    'meta_query' => array(
      array(
        'key' => 'activity_id',
        'value' => $activity_id,
      )
    )
  );

  $query = new WP_Query($args);
  $count = $query->found_posts;

  $places = get_field('places', $activity_id);

  $response = new WP_REST_Response(array(
    'count' => $count,
    'places' => $places > 0 ? $places : '-1'
  ));
  $response->set_status(200);

  return $response;
}


// Add action hook only if action=download_csv
if (isset($_GET['action']) && $_GET['action'] == 'download_csv') {
  // Handle CSV Export
  add_action('admin_init', 'csv_export');
}


function csv_export()
{
  // Check for current user privileges 
  if (!current_user_can('unfiltered_html')) {
    die('Security check error');
  }

  // Check if we are in WP-Admin
  if (!is_admin()) {
    die('Security check error');
  }

  // Nonce Check
  $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
  if (!wp_verify_nonce($nonce, 'download_csv')) {
    die('Security check error');
  }


  if (isset($_REQUEST['activity_id'])) {

    $activity = get_post($_REQUEST['activity_id']);

    if (!class_exists('Submissions_List_Table')) {
      require_once('includes/submissions-list-table-class.php');
    }

    $submissionsListTable = new Submissions_List_Table($activity->ID);
    $submissionsListTable->prepare_items();
    $submissionsListTable->export_csv();
  }




  ob_start();
  $domain = $_SERVER['SERVER_NAME'];
  $filename = 'users-' . $domain . '-' . time() . '.csv';

  $header_row = array(
    'Email',
    'Name'
  );
  $data_rows = array();
  global $wpdb;
  $sql = 'SELECT * FROM ' . $wpdb->users;
  $users = $wpdb->get_results($sql, 'ARRAY_A');
  foreach ($users as $user) {
    $row = array(
      $user['user_email'],
      $user['user_name']
    );
    $data_rows[] = $row;
  }
  $fh = @fopen('php://output', 'w');
  fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Content-Description: File Transfer');
  header('Content-type: text/csv');
  header("Content-Disposition: attachment; filename={$filename}");
  header('Expires: 0');
  header('Pragma: public');
  fputcsv($fh, $header_row);
  foreach ($data_rows as $data_row) {
    fputcsv($fh, $data_row);
  }
  fclose($fh);

  ob_end_flush();

  die();
}
