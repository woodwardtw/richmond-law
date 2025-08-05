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
   // Skip the header row
   fgetcsv($file);
   
   // Loop through each row of the file
   // The fgetcsv() function reads a line from the file and parses it as CSV fields
   $html = '<table>';
   while (($data = fgetcsv($file)) !== FALSE) {
	        
	        $title = ur_law_title_extract($data);//full case name
			//$slug = $data[6];
			$holding = $data[22];
	        $record_number = $data[29];
			$author = ur_law_judge_extract($data[42]);//
	        $date = $data[10];
	        $year = substr($date, 0, 4);
			$citation = ur_law_citation_extract($data[46]);		
	        $case_term_id = ur_law_case_term("term-". $year, "case_terms");
	        $status = 'active';
	        $status_term_id = ur_law_case_term($status, "status");
	        $holding = $data[21];
	        //var_dump( "data[3] = {$title}, author ={$author} </br>");
	        // $args = array(
			// 	'post_title'    => wp_strip_all_tags( $title ),
			// 	'post_status'   => 'draft',
			// 	'post_type'     => 'case'
			// 	);
	        // $post_id = wp_insert_post($args);
	        // update_field("field_68409d59458b9", $record_number, $post_id);//record number
	        // update_field("field_6813bd5df583f", $holding, $post_id);//Holding
	        // update_field("field_6841b48acd6b8", $case_term_id, $post_id); //Case term
			// update_field("field_67f81629abfab", $status_term_id, $post_id); //status
			// update_field("field_67f81629abfab", $status_term_id, $post_id); //status     
			$html .= "<tr><td>{$title}</td><td>{$record_number}</td><td>{$author}</td><td>{$year}</td><td>{$citation}</td></tr>";
	        
	    }
		return $html . "</table>";
	    // Close the file
	    fclose($file);
	} else {
	    // Handle the case where the file could not be opened
	    echo "Unable to open the file.";
	}
}

function ur_law_title_extract($data){
	if($data[4]){
		return $data[4];
	} elseif ($data[3]){
		return $data[3];
	} else {
		return '';
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

function ur_law_judge_extract($text){
    if (preg_match('/<author[^>]*>([^<]+)<\/author>/', $text, $matches)) {
		$clean = ur_law_fix_name($matches[1]);
        return $clean; // This is the captured inner content
    }
    return "";
}


function ur_law_fix_name($name) {
    $name = trim($name);
    
    // Split on comma to handle "LAST, FIRST" format
    $parts = array_map('trim', explode(',', $name));
    
    // Normalize each part
    $normalized = array_map(function($part) {
        return ucwords(strtolower($part));
    }, $parts);
    
    return implode(', ', $normalized);
}

function ur_law_citation_extract($text){
	$array = explode(',', $text);
	return $text;
	//return $array[0];
}