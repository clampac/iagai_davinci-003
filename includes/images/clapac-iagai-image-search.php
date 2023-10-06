<?php
/** @package    Clapac_iagai
 * @since 0.8.0
 * @subpackage Clapac_iagai/includes
 * @author     Claudio M. Bittencourt Pacheco <claudio@meuscaminhos.com.br>
 */
require_once plugin_dir_path( dirname( __FILE__,2 ) ) . 'includes/clapac-iagai-functions.php';

function search_images( $post_ids ) {

	//Check if post ids are specified
	if ( empty( $post_ids ) ) {
		wp_die( 'No post IDs specified' );
	}

	foreach ( $post_ids as $post_id ) {

		$post = get_post( $post_id );
		echo( '<br><u><h3>Processing post ' . get_the_title( $post ) . '</h3></u><br>' );
		$keywords = IAGAI_search_images($post_id);
		foreach ($keywords as $key => $value) {
			if (str_contains($key, 'keyword')){
				search_pixabay($post_id, $value);
			}
		}
		ob_flush();
		flush();
	}
}


function IAGAI_search_images($post_id){
	if (!$post_id){ return null; }
	$url = get_post_embed_url($post_id);



	$post_title = get_the_title($post_id);
	echo '<br>Searching images for post: ' . $post_title;

	//verifica se tem a chave de api
	$openAI_key = get_option('clapac_iagai_openAI_key');
	if($openAI_key == ''){
		clapac_iagai_log('Erro ao gerar os atributos da imagem: openAI Key não configurada.');
		wp_die('Erro ao gerar os atributos da imagem. Chave não configurada.');
	}

	$prompt = ("For the given blog post ".$url.", what are the two best keywords to find a photo in Pixabay that ".
	           "best fits to the article?Do not use generic keywords. Keywords based on the post context are better.".
	           "Response in portuguese. Return in python dictionary format. ".
	           "Identify each keyword with a key in the format 'keyword'+number.");





	$engine = 'text-davinci-003';

	$request_body = [
		"prompt" => $prompt,
		"max_tokens" => 600,
		"temperature" => 0.85,
		"top_p" => 1,
		"presence_penalty" => 0.15,
		"frequency_penalty"=> 0.15,
		"best_of"=> 1,
		"stream" => false,
	];

	$postfields = json_encode($request_body);
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.openai.com/v1/engines/" . $engine . "/completions",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 40,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $postfields,
		CURLOPT_HTTPHEADER => [
			'Content-Type: application/json',
			'Authorization: ' . 'Bearer '.$openAI_key
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	if ($err) {
		echo "Error #:" . $err;
	} else {
		$data = json_decode($response);
		$result = ($data->{'choices'}[0]->{'text'});
		$data = sanitizeResultDictionary($result);
		return $data;
	}
}

function search_pixabay($post_id, $keyword)
{
	echo '<br>';	echo '<br>';
	echo ($keyword);
	echo '<br>';	echo '<br>';
	$API_KEY = get_option('clapac_iagai_pixabay_key');
	if (!current_user_can('manage_options')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	echo '<div class="wrap">';
	echo '<h2>My Plugin Options</h2>';

	$images = array();

	$API_URL = 'https://pixabay.com/api/';
	$PARAMS = array(
		'key' => $API_KEY,
		'q' => $keyword,
		'lang' => 'pt',
		'orientation' => 'horizontal',
		'min_width' => 1200,
		'safesearch' => 'true',
		'per_page' => 80,
		'image_type' => 'photo'
	);


	$r = wp_remote_get($API_URL.'?'.http_build_query($PARAMS,'','&'));
	$data = json_decode($r['body'], true);
	$totalImages = $data['total'];

	if($totalImages > 0) {
		$max = $totalImages - 1;
		if($max > 79) {
			$max = 79;
		}
		for($x = 0; $x <= 2; $x++) {
			$imageNumber = rand(0, $max);
			$imageData['imageURL'] = $data['hits'][$imageNumber]['largeImageURL'];
			$previewURL = str_replace('640', '180',$data['hits'][$imageNumber]['webformatURL']);
			$imageData['tags'] = $data['hits'][$imageNumber]['tags'];
			$imageData['user'] = $data['hits'][$imageNumber]['user'];

			global $wpdb;
			$table_name = $wpdb->prefix . 'clapac_iagai_images_found';
			$wpdb->insert(
				$table_name,
				array(
					'post_id' => $post_id,
					'preview_url' => $previewURL,
					'large_image_url' => $imageData['imageURL'],
					'tags' => $imageData['tags'],
					'author' => $imageData['user'],
					'keyword_searched' => $keyword
				));

			echo '<h3>Images Retrieved for keyword: '.$keyword.'</h3>';
			echo '<div class="iagai_photo_search_preview" style="float-left"><img width="150px" src="'.$previewURL.'"/></div>';
			echo '<div class="iagai_photo_search_description" style="float-right">';
			echo '<ul>';
				echo '<li>large_image_url: '.$imageData['imageURL'].'</li>';
				echo '<li>tags: '.$imageData['tags'].'</li>';
				echo '<li>author: '.$imageData['user'].'</li>';
				echo '<li>keyword_searched: '.$keyword.'</li>';
			echo '</ul>';
			echo '</div>';
			ob_flush();
			flush();
		}
	}
}

