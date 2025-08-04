<?php
/**
 * Custom functions for importing data
 *
 * 
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


add_shortcode( 'caseimport', 'ur_law_case_importer' );

function ur_law_case_importer(){
	// Open the CSV file in read mode
	$data_path = get_template_directory() . "/data/cases.csv";
	$file = fopen($data_path, "r");

	// Check if the file opened successfully
	if ($file !== FALSE) {
	    // Loop through each row of the file
	    // The fgetcsv() function reads a line from the file and parses it as CSV fields
	    while (($data = fgetcsv($file)) !== FALSE) {
	        
	        $title = $data[3];//case name full?
	        $record_number = $data[8];//cluser ID?
	        $date = $data[10];
	        $year = substr($date, 0, 4);
	        $case_term_id = ur_law_case_term("term-". $year, "case_terms");
	        $status = 'active';
	        $status_term_id = ur_law_case_term($status, "status");
	        $holding = $data[21];
	        echo "title {$title} = {$record_number} </br>";
	        //echo "record " . $record_number;
	        $args = array(
				'post_title'    => wp_strip_all_tags( $title ),
				'post_status'   => 'publish',
				'post_type'     => 'case'
				);
	        $post_id = wp_insert_post($args);
	        update_field("field_68409d59458b9", $record_number, $post_id);//record number
	        update_field("field_6813bd5df583f", $holding, $post_id);//Holding
	        update_field("field_6841b48acd6b8", $case_term_id, $post_id); //Case term
			update_field("field_67f81629abfab", $status_term_id, $post_id); //Case term	        

	        
	    }

	    // Close the file
	    fclose($file);
	} else {
	    // Handle the case where the file could not be opened
	    echo "Unable to open the file.";
	}
}


function ur_law_case_term($slug, $taxonomy){	
	if (!term_exists($slug, $taxonomy)) {
	    $term_id = wp_insert_term($slug, $taxonomy);
	} else {
		$term_id = get_term_by('slug', $slug , $taxonomy);
	}
	return $term_id;
}
