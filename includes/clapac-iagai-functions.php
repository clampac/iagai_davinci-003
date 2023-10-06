<?php
// A function to return the next date/time for a publication date

function convertToWPBlocks ($html){

	// Sometimes OpenAI returns unoppened, unclosed or without <p> tags
	$sanitized_html = array();
	foreach (preg_split('/\r\n|\r|\n/',$html) as $paragraph) {
		if (!isset($paragraph) or trim($paragraph) ==''){
			continue;
		}
		if (  !preg_match( '/<p>.*<\/p>/i', $paragraph ) ) {

			if (preg_match('/(?!(<p>))^.*<\/p>/i', $paragraph)){
				$paragraph = preg_replace('/(?!(<p>))^.*<\/p>/i', '<p>'.$paragraph, $paragraph);
				continue;
			}
			if (preg_match('/<p>.*(?!(<\/p>))/i', $paragraph)){
				$paragraph = preg_replace('/<p>.*(?!(<\/p>))/i', $paragraph.'</p>', $paragraph);
				continue;
			}
			if (!preg_match( '/<p>.*<\/p>/i', $paragraph  )){
				$paragraph = '<p>'.$paragraph.'</p>';
			}
		}
		$sanitized_html[] = $paragraph;
	}

	$html = implode(PHP_EOL, $sanitized_html);

	$output = preg_replace('/(<h[1-4]>)\s*(.*?)\s*(<\/h[1-4]>)/', '<!-- wp:heading -->$1$2$3<!-- /wp:heading -->', $html);
	$output = preg_replace('/(<p>)\s*(.*?)\s*(<\/p>)/', '<!-- wp:paragraph -->$1$2$3<!-- /wp:paragraph -->', $output);
	$output = preg_replace('/(<ul>)\s*(.*?)\s*(<\/ul>)/', '<!-- wp:list -->$1$2$3<!-- /wp:list -->', $output);
	$output = preg_replace('/(<li>)\s*(.*?)\s*(<\/li>)/', '<!-- wp:list-item -->$1$2$3<!-- /wp:list-item -->', $output);

	return $output;
}


function get_next_date() {
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/AI_Config.php';

	// Retrieve the configuration constants
	$config = AI_Config::$PUBLICATION_DATE_CONFIG;

	// Get the start and end dates
	$start_date = $config['start_date'];
	$end_date = $config['end_date'];

	// Get the start and end times
	$start_time = $config['start_time'];
	$end_time = $config['end_time'];

	// Get the maximum number of articles per day
	$articles_day = $config['articles_day'];

	// Get the last date/time used
	$last_date_time = get_option('clapac_iagai_last_date_time');

	// Get the number of requests to a next date used for the day
	$num_requests = get_option('clapac_iagai_num_requests');

	// Calculate the maximum interval between dates (3 hours)
	$min_interval = 3 * 3600;

	// If there is no last date/time used, return a random time after the start time
	if (empty($last_date_time)) {
		$next_date_time = date('Y-m-d H:i:s', strtotime($start_date.' '.$start_time) + rand($min_interval, 2*$min_interval));
	} else {
		// Get the last date used
		$last_date = date('Y-m-d', strtotime($last_date_time));

		// Check if the maximum number of articles per day has been reached
		if ($num_requests >= $articles_day) {
			// Move to the next day
			$next_date = date('Y-m-d', strtotime('+1 day', strtotime($last_date)));

			// Reset the counter
			$num_requests = 0;
		} else {
			$next_date = $last_date;
		}

		// Get the last time used
		$last_time = date('H:i:s', strtotime($last_date_time));

		// Calculate the next time
		$next_time = date('H:i:s', strtotime($last_time) + rand($min_interval, 2*$min_interval));

		// Check if the next time is greater than the end time
		if (strtotime($next_time) > strtotime($end_time) or (strtotime($next_time < $start_time))) {
			// Move to the next day
			$next_date = date('Y-m-d', strtotime('+1 day', strtotime($last_date)));

			// Reset the counter
			$num_requests = 0;
			//echo ("start_time ".$start_time);
			// Use the start time
			$next_time = date('H:i:s', strtotime($start_time) + rand (0,3600));
		}

		// Check if the next date is greater than the end date
		if (strtotime($next_date) > strtotime($end_date)) {
			// Use the start date and time
			return false;
		} else {
			// Create the next date/time
			$next_date_time = date('Y-m-d H:i:s', strtotime($next_date.' '.$next_time));
		}
	}

	// Update the last date/time used
	update_option('clapac_iagai_last_date_time', $next_date_time);

	// Update the number of requests to a next date used for the day
	update_option('clapac_iagai_num_requests', $num_requests + 1);

	// Return the next date/time
	return $next_date_time;
}



function clapac_iagai_openAI_key_validation($input) {
    $input = trim($input);
    if (!empty($input)) {
        return $input;
    }
    return '';
}


function clapac_iagai_log($log_str) {
	$log_arr = get_option('clapac_iagai_log');
	if (empty($log_arr)) {
		$log_arr = array();
	}
	$log_arr[] = date('Y-m-d H:i:s ') . $log_str;
	update_option('clapac_iagai_log', $log_arr);
}

function sanitizeResultDictionary($result){
	$resultDictionary = [];
	$toReplace = array ('\r\n', '\r', '\n', '[', '´]');
	$result = str_replace($toReplace, '', $result);
	preg_match_all('/([{\"\'])(.*)([}\"\'])/', $result, $matches);
	foreach($matches[0] as $match)
	{
		$quotes = array('"',"'");
		$result = str_replace($quotes, '', $match);
		preg_match_all('/(\b\w*\b\s*:\s*).+?([,}]|$)/', $result, $matches);

		foreach($matches[0] as $match)
		{
			$line = explode(':', $match);
			$key = trim($line[0], ' ');
			$value = trim($line[1], ',} ');
			$resultDictionary[$key] = $value;
		}
	}
	return $resultDictionary;
}

function iagai_get_image_from_post($post_id){
	$thePost = get_post($post_id);
	// get the post content with images
		$postContent = $thePost->post_content;
		$result = '';

		// get a DOMDocument object
		$domDoc = new DOMDocument();
		libxml_use_internal_errors(true);
		// load a HTML string into the DOMDocument
		$domDoc->loadHTML('<html><body>'.$postContent.'</body></html>');
		libxml_clear_errors();

		// Get the images in the DOMDocument
		$images = $domDoc->getElementsByTagName('img');

		return $images;
}

if (!function_exists('write_log')) {

	function write_log($log) {
		if (true === WP_DEBUG) {
			if (is_array($log) || is_object($log)) {
				error_log(print_r($log, true));
			} else {
				error_log($log);
			}
		}
	}

}