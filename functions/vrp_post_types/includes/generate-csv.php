<?php


if (!class_exists('Submissions_List_Table')) {
  require_once('includes/submissions-list-table-class.php');
}

$submissionsListTable = new Submissions_List_Table($activity->ID);
$submissionsListTable->prepare_items();

$submissionsListTable->export_csv();




// // Include WP files so our generator can actually work with WP

// $location = $_SERVER['DOCUMENT_ROOT'];

// include($location . '/wp-config.php');
// include($location . '/wp-load.php');
// include($location . '/wp-includes/pluggable.php');

// global $wpdb;

// function generatecsv()
// {
//   global $wpdb;

//   header("Content-type: text/csv");
//   header("Content-Disposition: attachment; filename=Daily-Recruiter-CSV.csv");
//   header("Pragma: no-cache");
//   header("Expires: 0");



//   //open stream  
//   $file = fopen('php://output', 'w');

//   // Add Headers                            
//   fputcsv($file, array(
//     'Date',
//     'How many recruiters I have',
//     'How many new recruiters joined today',
//     'How many searches the recruiters did',
//     'How many times recruiters contacted talent',
//     'How many anyone contacted talent',
//     'How many recruiters logged into the system'

//   ));

//   $data = array();

//   $offset = 30;

//   $counter = 0;
//   while ($counter < $offset) {
//     $counter++;

//     $profileRow = array(
//       date('d.m.Y', strtotime("-" . $counter . " days")),
//       how_many_recruiters_do_i_have(),
//       how_many_recruiters_joined_yesterday(date('Y-m-d', strtotime("-" . $counter . " days"))),
//       how_many_times_did_recruiters_search(),
//       how_many_times_recruiter_sent_email(),
//       how_many_times_anyone_sent_email(),
//       how_many_recruiters_logged_in_this_day(date('d.m.Y', strtotime("-" . $counter . " days")))



//     );
//     array_push($data, $profileRow);
//   }


//   foreach ($data as $row) {

//     fputcsv($file, $row);
//   }


//   exit();
// }

// generatecsv();
