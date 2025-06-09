<?php
/**
 * Understrap ACF 
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


//case acf functions

function ur_law_basics_table(){
	$record = get_field('record_number');
	$op_below = get_field('op_below');
	$argument = get_field('argument');
	$opinion = get_field('opinion');
	$author = get_field('author');
	$term = get_field('term');

	return "
		<table class='case-details'>
			<thead>
				<tr>
					<th>Record No.</th>
					<th>Op. Below</th>
					<th>Argument</th>
					<th>Opinion</th>
					<th>Author</th>
					<th>Term</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>{$record}</td>
					<td>{$op_below}</td>
					<td>{$argument}</td>
					<td>{$opinion}</td>
					<td>{$author}</td>
					<td>{$term}</td>
				</tr>
			</tbody>
		</table>
	";
}

function ur_law_basic_html($obj,$h_level){
	if(isset(get_field_object($obj)['value'])){
		$basic_obj = get_field_object($obj);
	    $basic = $basic_obj['value'];   
	    //puts p tags for text fields
	    if(substr($basic,0,3) != "<p>"){
	    	$basic = "<p>{$basic}</p>";
	    } 
	    $basic_label = $basic_obj['label'];
	    //remove s from postures if no ;
	    // if($obj == "procedural_postures" && !str_contains($basic,";")){

	    // }
	    $slug = sanitize_title($basic_label);
	    return "<div class='section' id='{$slug}'>
	    		<{$h_level} id='{$slug}-label'>{$basic_label}</{$h_level}>
	            {$basic}
	            </div>";   
	}	
}


function url_law_case_citation(){
	$citation = get_field("case_citation");
	$url = get_field("case_url");
	return "
		<div id='citation'>
			<p>Citation: <a href='{$url}'>{$citation}</a></p>
		</div>
	";

}

function ur_law_briefs_repeater(){
	$html = '';
	if( have_rows('briefs') ):
		$html = "<div class='section' id='briefs'><h2>Briefs and Records</h2><ul>";
	    // Loop through rows.
	    while( have_rows('briefs') ) : the_row();

	        // Load sub field value.
	        $title = get_sub_field('title');
	        $url = get_sub_field('url');
	        // Create li with links
	        if($url !=''){
	        	$title = "<a class='brief' href='{$url}'>{$title}</a>";
	        }
	        $html .= "<li>{$title}</li>";
	    // End loop.
	    endwhile;
	    return $html . "</ul></div>";
		// No value.
		else :
		    // Do something...
		endif;
	}


function ur_law_coverage_repeater(){
	$html = '';
	if( have_rows('case_coverage') ):
		$html = "<div class='section' id='coverage'><h2>Case Coverage</h2><ul>";
	    // Loop through rows.
	    while( have_rows('case_coverage') ) : the_row();

	        // Load sub field value.
	        $title = get_sub_field('title');
	        $url = get_sub_field('url');
	        $summary = get_sub_field('summary');
	        // Create li with links
	        if($url !=''){
	        	$title = "<a class='coverage commentary' href='{$url}'>{$title}</a>";
	        }
	        $html .= "<li>
	        			{$title}
	        			<p>{$summary}</p>
	        			</li>";
	    // End loop.
	    endwhile;
	    return $html . "</ul></div>";
		// No value.
		else :
		    // Do something...
		endif;
	}

function ur_law_audio(){
	$audio = get_field("audio_recording");
	$html = "<figure>
			  <figcaption>Listen to the case:</figcaption>
			  <audio controls src='{$audio}'></audio>
			  <a href='{$audio}'> Download audio </a>
			</figure>";
	return $html;
}



//save acf json
add_filter('acf/settings/save_json', 'ur_law_json_save_point');
 
function ur_law_json_save_point( $path ) {
    
    // update path
    $path = get_stylesheet_directory() . '/acf-json'; 
    
    // return
    return $path;
    
}


// load acf json
add_filter('acf/settings/load_json', 'ur_law_json_load_point');

function ur_law_json_load_point( $paths ) {
    
    // remove original path (optional)
    unset($paths[0]);
    
    
    // append path
    $paths[] = get_stylesheet_directory()  . '/acf-json';
    

    // return
    return $paths;
    
}