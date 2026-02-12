<?php
/**
 * Understrap ACF 
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


//case acf functions

function ur_law_url_maker($url, $text){
	if($url != ''){
		return "<a href='{$url}'>{$text}</a>";
	} else {
		return $text;
	}
}

function ur_law_basics_table(){
	$docket = get_field('docket_id');
	
	$op_below = get_field('op_below');
	$op_below_url = get_field('op_below_url');
	$op = ur_law_url_maker($op_below_url, $op_below);
	
	$argument = get_field('argument');
	$argument_url = get_field('argument_url');
	$arg = ur_law_url_maker($argument_url, $argument);
	if(get_field('audio_recording')){
		$arg = "<a href='#oral-argument'>{$arg}</a>";
	}
	$opinion = get_field('opinion');
	$opinion_url = get_field('opinion_url');
	$opine = ur_law_url_maker($opinion_url, $opinion);

	$author = get_field('author');
	
	$term_obj = get_field('term');
	$term_name = preg_replace('/^term-/', '', $term_obj->name);
	$term_url = get_term_link($term_obj);
	$term = ur_law_url_maker($term_url, $term_name);
	//preg_replace('/^term-/', '', $term->slug)

	return "
		<table class='case-details'>
			<thead>
				<tr>
					<th>Docket No.</th>
					<th>Op. Below</th>
					<th>Argument</th>
					<th>Opinion</th>
					<th>Author</th>
					<th>Term</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td data-label='Record No.'>{$docket}</td>
					<td data-label='Op. Below'>{$op}</td>
					<td data-label='Argument'>{$arg}</td>
					<td data-label='Opinion'>{$opine}</td>
					<td data-label='Author'>{$author}</td>
					<td data-label='Term'>{$term}</td>
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

function ur_law_holding($obj,$h_level){
	$status = get_field('status')->name;
	$basic_label = "Holding";
	if($status == "Pending"){
	    	$basic_label = "Issue";
	    } 
	if(isset(get_field_object($obj)['value'])){
		$basic_obj = get_field_object($obj);
	    $basic = $basic_obj['value'];   
	    //puts p tags for text fields
	    if(substr($basic,0,3) != "<p>"){
	    	$basic = "<p>{$basic}</p>";
	    } 
	    
	    
	    $slug = sanitize_title($basic_label);
	    return "<div class='section' id='{$slug}'>
	    		<{$h_level} id='{$slug}-label'>{$basic_label}</{$h_level}>
	            {$basic}
	            </div>";   
	}	
}


function url_law_case_citation(){
	$title = get_the_title();
	$citation = get_field("case_citation");
	$url = get_field("case_url");
	$term = substr(get_field('term')->name, -4);
	if($url == ''){
		return "
		<div id='citation'>
			<p>Citations: {$citation}</p>
		</div>";
	} else {
		return "
			<div id='citation'>
				<p>Citation: <a href='{$url}'> {$citation}</a></p>
			</div>
		";
	}

}

function ur_law_briefs_repeater(){
	$html = '';
	if( have_rows('briefs') ):
		// Use the ACF field label for the heading when available so the heading
		// can be changed in ACF UI. Fall back to the original text otherwise.
		$field_obj = get_field_object('briefs');
		$heading = (isset($field_obj['label']) && $field_obj['label']) ? $field_obj['label'] : 'Briefs and Records';
		$html = "<div class='section' id='briefs'><h2>" . esc_html($heading) . "</h2><ul>";
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
		// Use the ACF field label for the heading when available so the heading
		// can be changed in ACF UI. Fall back to the original text otherwise.
		$field_obj = get_field_object('case_coverage');
		$heading = (isset($field_obj['label']) && $field_obj['label']) ? $field_obj['label'] : 'Case Coverage';
		$html = "<div class='section' id='coverage'><h2>" . esc_html($heading) . "</h2><ul>";
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
	        			{$summary}
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
	$argument_date = get_field("argument");
	if($audio){
		$html = "
			<h2 id='oral-argument'>Oral Argument</h2>
			<p>Argument Date: {$argument_date}</p>
			<figure>
			  <figcaption>Listen to the Supreme Court Oral Argument</figcaption>
			  <audio controls src='{$audio}'></audio>
			  <div><a href='{$audio}'> Download audio </a></div>
			  <div class='caution'>The Supreme Court of Virginia does not provide transcripts of oral arguments.  
			  Files included in Virginia Verdicts come from the publicly-available official court website.</div>
			</figure>";
	return $html;
	}	
}

//show verification messages on single case pages
function ur_law_verification(){
	$verified_text = get_field('reviewed_content', 'option');
	$unverified_text = get_field('unreviewed_content', 'option');
	$verification = get_field("verification_status");
	if($verification == "Verified"){
		return "<div class='verification verified alert alert-secondary'>{$verified_text}</div>";
	} elseif ($verification == "Unverified" || $verification === null){
		return "<div class='verification unverified alert alert-warning'>{$unverified_text}</div>";
	} else {
		return "";
	}
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

