<?php
/** @package    Clapac_iagai
 * @since 0.7.0
 * @subpackage Clapac_iagai/includes/figures
 * @author     Claudio M. Bittencourt Pacheco <claudio@meuscaminhos.com.br>
 */
require_once plugin_dir_path( dirname( __FILE__ ,2) ) . 'includes/clapac-iagai-functions.php';


function generate_attributes( $post_ids ) {

	//Check if post ids are specified
	if ( empty( $post_ids ) ) {
		wp_die( 'No post IDs specified' );
	}


	//Loop through each post ID
	foreach ( $post_ids as $post_id ) {

		$post = get_post( $post_id );
		echo( '<br><u><h3>Processing post ' . get_the_title( $post ) . '</h3></u><br>' );

		$message = iagai_generator( $post_id );
		echo ($message);
		ob_flush();
		flush();
	}
}

// Hook into wordpress ajax
//add_action('wp_ajax_update_results', 'update_results');

function iagai_generator($post_id){
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



    if(empty($images) or sizeof($images)<1){
        return 'Images not found for this post.';
    }

	// Loop through images adding / editing alt attribute
	foreach ($images as $image){

		$imageOriginalURL = $image->getAttribute('src');
		$attributes = aiGenerator($imageOriginalURL);

		if (empty($attributes)){
			clapac_iagai_log('Não foram encontrados atributos para a imagem '.$imageOriginalURL);
			return ('<h5 style="color: red">Não foram encontrados atributos para o arquivo '.$imageOriginalURL.'.<br>');
		}

		$imageData = sanitizeResultDictionary($attributes);

		$altText = $imageData['altText'];
		$imageTitle = $imageData['title'];
		$imageDescription = $imageData['description'];
		$newFileName = $imageData['fileName'];


		if ( !$altText or !$imageTitle or !$imageDescription) {
			return ("<h5 style='color: red'>No attributes was found for this file. Try again in a few minutes.</h5>");
		}

		$image->setAttribute('alt', $imageDescription);
		$image->setAttribute('title', $imageTitle);
		//$image->setAttribute('description', $imageDescription);

		echo ('<h4>Arquivo: '.$imageOriginalURL.'</h4>');
		echo ('<b>Title:</b> '.$imageTitle);
		echo ('<br><br><b>Alt:</b> '.$altText);
		echo ('<br><br><b>Description:</b> '.$imageDescription);
		echo ('<br><br><b>File name suggestion:</b> '.$newFileName.'<br>');
		ob_flush();
		flush();

		save_suggested_file_names($post_id, $imageOriginalURL, $newFileName );

		$thePost = saveModifiedContent($domDoc, $thePost, $postContent);
		clapac_iagai_log('Atributos da imagem '.$imageOriginalURL.' criados com sucesso.');
	}

	return $result;
}

// save the modified post content
function saveModifiedContent($domDoc, $post, $postContent){
	$postContent = trim(preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $domDoc->saveHTML()));
	$post->post_content = $postContent;

	remove_action( 'post_updated', 'wp_save_post_revision' );
	wp_update_post($post);
	add_action( 'post_updated', 'wp_save_post_revision' );
	return $post;
}

function save_suggested_file_names( $post_id, $originalName, $suggestedName ) {
	global $wpdb;
	if (!$post_id or !$originalName or !$suggestedName) {
		echo ("<h5 style='color: red'>Fails to save some data. Try again later. </h5>");
		return false;
	}
	$table_name = $wpdb->prefix . 'clapac_iagai_img_file_name';
	$sql = $wpdb->prepare("INSERT INTO ". $table_name ." (originalName, suggestedName, title_id) ".
	                      "VALUES (%s, %s, %d)", $originalName, $suggestedName, $post_id );
	$wpdb->query( $sql );
	if ( isset($wpdb->last_error) && str_contains($wpdb->last_error, 'Duplicate entry') ){
		echo "<h5 style='color: red'>This file was already proccessed. If you want to proccess it again, you have to delete it from the table on 'Image Name Update' menu first. </h5>";
	}
	else {
		echo ("<h5 style='color: blue'>Changes saved to the post. </h5>");
	}
	echo ('<hr>');
}


function aiGenerator($url){

	//verifica se tem a chave de api
	$openAI_key = get_option('clapac_iagai_openAI_key');
	if($openAI_key == ''){
		clapac_iagai_log('Erro ao gerar os atributos da imagem: openAI Key não configurada.');
		wp_die('Erro ao gerar os atributos da imagem. Chave não configurada.');
	}

	$prompt = '- Usando a imagem da URL abaixo, escreva (em português) o altText, description e title e um '.
	          'novo nome que seja diferente do atual e seja SEOfriendly (atributo "fileName"). Mostrar a extensão '.
			  ' do arquivo. Use formato JSON para resposta. Não mostre nada além do JSON. (coloque apostrofo em tudo).'.
	          ' Formato utf-8. URL = ' . $url;

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
	CURLOPT_TIMEOUT => 30,
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
		$imageData = json_decode($response);				
		$result = ($imageData->{'choices'}[0]->{'text'});
		$imageData = sanitizeResultDictionary($result);		
		return $result;		
	}
}