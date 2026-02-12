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
	$data_path = get_template_directory() . "/data/ur-data-all.csv";
	//$data_path = get_template_directory() . "/data/cases.csv";
	$file = fopen($data_path, "r");

// Check if the file opened successfully
if ($file !== FALSE) {
   // Skip the header row
   fgetcsv($file);
   // Loop through each row of the file
   // The fgetcsv() function reads a line from the file and parses it as CSV fields
   $html = '<table>';
   $row = 0;
   while (($data = fgetcsv($file)) !== FALSE ) {
	        $row = $row + 1;
            $title = ur_law_title_extract($data);//short case name
            $holding = $data[22];//summary 
            $record_number = $data[29];//docket_id !!!!!!
            $docket_id = $data[27];//
            $author = ur_law_judge_extract($data[42]);//
            $date = $data[10];
            $listener_url = $data[76]; // url for opinion
            $year = substr($date, 0, 4);
            $citation = format_citations_line($title, $data[46], $year); //citations);
            $case_term_id = ur_law_case_term("term-". $year, "case_terms");
            $status = 'active';
            $status_term_id = ur_law_case_term($status, "status");
            $holding = $data[21];

            // Check for duplicates: same year and record number
            $duplicate_args = array(
                'post_type' => 'case',
                'post_status' => 'any',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'record_number',
                        'value' => $record_number,
                        'compare' => '='
                    )
                ),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'case_terms',
                        'field' => 'slug',
                        'terms' => 'term-' . $year
                    )
                )
            );
            $duplicate_check = new WP_Query($duplicate_args);

            if ($duplicate_check->have_posts()) {
                // Duplicate found - skip this record
                $html .= "<tr style='background-color: #ffcccc;'><td>{$title}</td><td>{$date}</td><td colspan='2'>SKIPPED - Duplicate (Year: {$year}, Record: {$record_number})</td></tr>";
                wp_reset_postdata();
                continue;
            }
            wp_reset_postdata();

	        $args = array(
				'post_title'    => wp_strip_all_tags( $title ),
				'post_status'   => 'draft',
				'post_type'     => 'case'
				);
	        $post_id = wp_insert_post($args);
	        update_field("field_68409d59458b9", $record_number, $post_id);//record number
            update_field("field_695a88d962921", $docket_id, $post_id);//docket id
	        update_field("field_6813bd5df583f", $holding, $post_id);//Holding
	        update_field("field_6841b48acd6b8", $case_term_id, $post_id); //Case term
			update_field("field_67f81629abfab", $status_term_id, $post_id); //status
			update_field("field_6813becaf5842", $citation, $post_id); //citation
			update_field("field_68409de8458bd", $author, $post_id); //author/judge
            update_field("field_68409dc6458bc", $date, $post_id); //opinion date
            update_field("field_684b0a1feee06", $listener_url, $post_id); //opinion url
            update_field("field_6813bee1f5843", $listener_url, $post_id); //access url
			wp_set_object_terms($post_id, 'Decided', 'status'); //set status term    
             $html .= "<tr><td>{$title}</td><td>{$date}</td><td>{$case_term_id}</td><td>{$listener_url}</td></tr>";
	        
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
	if($data[3]){
		return urlaw_title_clearner($data[3]);
	} elseif ($data[4]){
		return urlaw_title_clearner($data[4]);
	} else {
		return '';
	}
}


function ur_law_case_term($slug, $taxonomy){	
    if (!term_exists($slug, $taxonomy)) {
        $result = wp_insert_term($slug, $taxonomy);
        if (is_wp_error($result)) {
            return 0;
        }
        $term_id = isset($result['term_id']) ? (int) $result['term_id'] : 0;
    } else {
        $term = get_term_by('slug', $slug, $taxonomy);
        $term_id = $term ? (int) $term->term_id : 0;
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

function normalize_citations(string|array $input): array {
    if (is_array($input)) {
        $parts = $input;
    } else {
        $raw = trim($input);
        if ($raw !== '' && $raw[0] === '{' && substr($raw, -1) === '}') {
            $raw = substr($raw, 1, -1);
        }
        $parts = $raw === '' ? [] : explode(',', $raw);
    }

    // Trim whitespace + surrounding quotes; drop empties; keep unique while preserving order
    $seen = [];
    $out  = [];
    foreach ($parts as $p) {
        $c = trim(trim((string)$p), "\"' \t\n\r\0\x0B");
        if ($c !== '' && !isset($seen[mb_strtolower($c)])) {
            $seen[mb_strtolower($c)] = true;
            $out[] = $c;
        }
    }
    return $out;
}

function order_citations(array $citations): array {
    // Priority groups, in display order
    $patterns = [
        '/\b\d+\s+S\.E\.2d\s+\d+\b/u',                                                // S.E.2d
        '/\b\d+\s+S\.E\.\s+\d+\b/u',                                                  // S.E.
        '/\b\d+\s+Va\.\s+\d+\b/u',                                                    // modern Va.
        '/\b\d+\s+(?:Gratt\.|Grat\.|Call\.|Munf\.|Rand\.|Leigh\.|Rob\.|Hen\.\s*&\s*M\.|Pat\.\s*&\s*H\.)\s+\d+\b/u', // historic Va.
        '/\b(17|18|19|20)\d{2}\s+Va\.\s+LEXIS\s+\d+\b/u',                              // Va. LEXIS
    ];

    $bucketed = [];
    $used = array_fill_keys(array_keys($citations), false);

    // Collect by priority (stable)
    foreach ($patterns as $idx => $pat) {
        foreach ($citations as $i => $c) {
            if (!$used[$i] && preg_match($pat, $c)) {
                $bucketed[] = $c;
                $used[$i] = true;
            }
        }
    }
    // Append anything unmatched, preserving original order
    foreach ($citations as $i => $c) {
        if (!$used[$i]) {
            $bucketed[] = $c;
        }
    }
    return $bucketed;
}

function urlaw_title_clearner($title){
    //look for against and replace with v.
    $clean_title = str_ireplace("against", "v.", $title);
    return $clean_title;
}


/**
 * Format the case citation line as: Short case name, citation(s) (year).
 * Optionally, make the case name a link to the courtlistener_url if provided.
 *
 * @param string $case_name
 * @param string|array $citations
 * @param string|int $decision_year
 * @param string|null $courtlistener_url
 * @return string
 */
function format_citations_line($case_name, $citations, $decision_year): string {
    $list = order_citations(normalize_citations($citations));
    $citations_str = implode(', ', $list);
    $year = $decision_year;
    // Only keep 4 digits if year is longer
    if (preg_match('/\d{4}/', (string)$year, $matches)) {
        $year = $matches[0];
    }
    // Shorten case name if possible (remove extra whitespace)
    $short_name = trim(preg_replace('/\s+/', ' ', $case_name));
   
    $short_name = esc_html($short_name);
    
    $out = $short_name;
    if ($citations_str !== '') {
        $out .= ', ' . $citations_str;
    }
    if ($year !== '') {
        $out .= ' (' . $year . ')';
    }
    $out .= '.';
    return $out;
}


function delete_all_cases() {
    $args = array(
        'post_type' => 'case',
        'post_status' => 'any',
        'numberposts' => -1
    );
    $all_cases = get_posts($args);
    foreach ($all_cases as $case) {
        wp_delete_post($case->ID, true); // true for force delete
    }
}

//delete_all_cases();