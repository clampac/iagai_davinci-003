<?php
require_once plugin_dir_path( dirname( __FILE__ ,2) ) . 'includes/AI_Config.php';
class AI_Generator {
//	private $langStyle = 'write in an informal, youthful, relaxed language.';
	private $cleanTitle = array(' ','.','!','?',':','-','_','"','\'',',',';');
	private $language = "LANGUAGE: brazilian portuguese.\n";
    private $paragraph_definition = "Write paragraphs with a minimum of 240 and a " .
                                    " maximum of 300 characters, always ending the paragraph." .
                                    "Always use p html tag for paragraphs.";

    public function get_clean_title(){
        return $this->cleanTitle;
    }
	// Create a topic list for the blog content

    private function writing_modes(){
	    $options = get_option('clapac_iagai_writing_settings');
        $mode_definitions = "";


        if (!isset($options)){
            return '';
        }

        if (isset($options['selected_styles'])){
            $styles = $options['selected_styles'];
            $mode_definitions .= "STYLE: ".implode(', ',$styles).".\n";
        }
	    if (isset($options['selected_moods'])){
		    $moods = $options['selected_moods'];
		    $mode_definitions .= "MOOD: ".implode(', ',$moods).".\n";
	    }
	    if (isset($options['selected_tones'])){
		    $tones = $options['selected_tones'];
		    $mode_definitions .= "TONE: ".implode(', ',$tones).".\n";
	    }

        return $mode_definitions;
    }

	public function generateArticleSubtitles($title, $keywords) {

		$min = 4;
		$max = 9;
		$subtitles = rand($min, $max);

		$language = "Writing in brazilian portuguese is mandatory";
//
//		$prompt = "Generate ".$subtitles." blog topics on: ".$title.". Article intended for the lay public.".
//		          "Don't use more than 12 words, if possible.Don't write the conclusion, it will be created".
//		          "in the future.".$language;

		$prompt = "I need a blog post about ".$title.".".
		          "Brainstorm an in-depth list of sections for this blog post. This list should".
		          " have 2 sublevels. Use html h1, h2 amd h3 tags.".
		          "It have to have a maximum of 12 words for each subitle.".
                  "The list should have at least 5 h2 sections and at least 5 h3 sections.".
                  "The section conclusion must be part of the list. Article intended for the lay public.\n".
		          "TITLE: ".$title."\n".
		          "TARGET AUDIENCE: parents.\n".
		          "TONE: casual, informal, creative.\n".
		          "MOOD: Enthusiastic.\n".
                  "LANGUAGE:".$language.
		          "KEYWORDS (comma separated values):".$keywords."\n\n"."<h1>".$title."</h1>\n";

        if (isset($keywords)){
            $prompt .= "Keywords (comma separeted values): ".$keywords.'.';
        }

		$response = $this->aiArticleWriter($prompt, AI_Config::$TOPIC_GENERATOR_CONFIG, null);

		return $response;
	}

	// writing the article introduction. It should be separated from the
	// other topics because it has different instructions.
	function writeArticleIntroduction($title, $headlines, $keywords) {

		$min = 2;
		$max = 3;
		$paragraphsNumber = rand($min, $max);
		$articleStructure = $this->article_structure($title, $headlines);

		$prompt = "For the blog post outline below, write an introduction with ".$paragraphsNumber." paragraphs ".
		          " into a detailed explanation. The blog is intended for the lay public.".
                  $this->paragraph_definition .
                  $this->language.
                  $this->writing_modes();

		if (isset($keywords)){
			$prompt .= "KEYWORDS (comma separeted values): ".$keywords.'.';
		}
		$prompt .= $articleStructure;

        write_log('------------------------');
        write_log($prompt);
		write_log('------------------------');


		$response = $this->aiArticleWriter($prompt, AI_Config::$POST_CONTENT_CONFIG, null);
		?>
        <script>
            jQuery("#<?php echo str_replace($this->cleanTitle, '',$title); ?> li:contains('Introduction')").append('<span style="margin-left: 16px;">&#10004;</span>');
        </script>
		<?php
		// because Nginx + php-tfp wasn't flushing it's buffers...
		while ( @ob_end_flush() ) {
		}
		flush();
		return $response;
	}


	// write the article conclusion. It should be separated from the
	// other topics because it has different instructions.
	function writeArticleConclusion($title, $headlines, $keywords) {

		$min = 3;
		$max = 6;
		$linkAmount = rand($min, $max);
        $paragraphs = rand (2,4);
		$articleStructure = $this->article_structure($title, $headlines);

//		$prompt = 'escreva em português do Brasil a conclusão de um artigo para blog chamado "'
//		          . $title . '", com 2 a 3 parágrafos com no máximo 5 linhas cada um, em uma '.
//		          'linguuagem descontraída, jovial e informal. Depois do texto inclua entre '.
//		          ' '.$linkAmount.' links de sites em português e não governamentais que sejam '.
//		          'confiáveis para referência.';
		$prompt = "For the blog post outline below, write an conclusion with ".$paragraphs." paragraphs ".
		          " into a detailed explanation. The blog is intended for the lay public.".
                  $this->paragraph_definition.
                  " After the text include ". $linkAmount ." links written in ".
                  " portuguese and that's non-governmental websites that are 'reliable for reference.'".
                  $this->language. $this->writing_modes();
		if (isset($keywords)){
			$prompt .= "KEYWORDS (comma separeted values): ".$keywords.'.';
		}

		$prompt .= $articleStructure;

		write_log('------------------------');
		write_log($prompt);
		write_log('------------------------');
		$response = $this->aiArticleWriter($prompt, AI_Config::$POST_CONTENT_CONFIG, null);

		?>
        <script>
            jQuery("#<?php echo str_replace($this->cleanTitle, '',$title); ?> li:contains('Conclusion')").append('<span style="margin-left: 16px;">&#10004;</span>');
        </script>

		<?php

		return $response;
	}

	// writing the content for each topic but introduction and conclusion
	function write_blog_topic_content($title, $headlines, $keywords){

		$articleStructure = $this->article_structure($title, $headlines);
		$response = '';
//
//		if (!empty($headlines)){
//			foreach ($headlines as $headline){
				$min = 2;
				$max = 5;
//				$paragraphs = "Write ".rand($min, $max)." paragraphs.";

				//$subtitle = ("<h2>".$headline."</h2>");

//				$prompt = ("For the blog post outline below, expand the blog sections into a detailed explanation. ".
//                           " Article intended for the lay public.Don't write any conclusion.Don't write the title line.".
//				           $paragraphs .
//                           $this->paragraph_definition .
//                           $this->language .
//                           $this->writing_modes());

				$prompt = ("Please write an article about '".$title."' intended for the lay public, with 2-5 paragraphs in each session.".
                           "Expand the sections of the blog post outline into a detailed explanation. Be sure to use the \<p\> html ".
                           "tag for paragraphs and to write a minimum length of 240 and maximum of 320 characters paragraphs, always completing them completely. \n".
                            "Make sure to follow a logical line of reasoning, presenting ideas in a clear and concise manner.");
                $prompt .= $this->language;
				if (isset($keywords)){
					$prompt .= "KEYWORDS (comma separeted values): ".$keywords.".\n";
				}
                $prompt .= $this->writing_modes()."\n\n";

				$prompt .= "ARTICLE OUTLINE:\n{$articleStructure}\n";

                write_log ($prompt);

				$response .= $this->aiArticleWriter($prompt, AI_Config::$POST_CONTENT_CONFIG, null);
		return $response;
	}


	private function article_structure ($title, $headlines){
		$articleStructure = "<h1>{$title}</h1>\n";
		foreach ($headlines as $headline){
			$articleStructure .= "<h{$headline->level}>{$headline->subtitle}</h{$headline->level}>\n";
		}

		return $articleStructure;
	}


	function write_article_permalink($title, $headlines, $keywordList) {

		$language = "Writing in brazilian portuguese is mandatory.";

		$prompt = "Write a permalink for a blog article called '".$title."'. The permalink words should ".
		          " be separated with a - sign, not underline. Use SEO techniques. the maximum permalink length is ".
		          " 60 characters. Use a logical sequence. Don't use characters that doesn't exist in english alphabet.".$language;
		if (isset($keywordList)){
			$prompt .= "KEYWORDS (comma separeted values): ".$keywordList.'.';
		}
		$response = $this->aiArticleWriter($prompt, AI_Config::$PERMALINK_CONFIG, null);

		return $response;
	}

	function write_suggested_titles($article_subject, $titlesAmount) {

		$language = "Writing in brazilian portuguese is mandatory.";

		$prompt = ("Create an article titles list about '".$article_subject."' for a blog. Write ".$titlesAmount." titles.".
		           " Break line after each title. The maximum title length is up to 60 characters. Write complete titles ".
                   " and words. Write using informal, jovial and relaxed language. The blog is intended for the lay ".
                    " public.");
		$prompt .= $language;


		$response = $this->aiArticleWriter($prompt, AI_Config::$TITLE_GENERATOR_CONFIG, $titlesAmount);

		return $response;
	}


	// Validate the OpenAI key, call the api, add usage
	private function aiArticleWriter($prompt, $config, $createdTitlesAmount) {

		//verifica se tem a chave de api
		$openAI_key = get_option( 'clapac_iagai_openAI_key' );
		if ( $openAI_key == '' ) {
			clapac_iagai_log( 'Error generating image attributes: openAI Key not configured.' );
			wp_die( 'Error generating image attributes. OpenAI key missing..' );
		}

		$response = $this->call_openAI_API($prompt, $config, $openAI_key);
		// Wait for the response from openAI. It prevents connection errors
		sleep(3);
		$json = json_decode( $response );

		if (isset ($json)){

			if (property_exists($json, "error")){
				return null;
			}

			if ( isset( $json->{'usage'} ) ) {
				$usage = (array) ( $json->{'usage'} );
			}
		}
        else {
            return null;
        }

		$this->update_api_stats( $usage, $createdTitlesAmount);

		$responseText = ( $json->{'choices'}[0]->{'text'} );

		return $responseText;
	}

	private function update_api_stats($usage, $createdTitlesAmount){
		$stats = (array)get_option('clapac_iagai_post_stats');

		$stats['prompt_tokens'] = $stats['prompt_tokens'] + $usage['prompt_tokens'];
		$stats['completion_tokens'] = $stats['completion_tokens'] + $usage['completion_tokens'];
		$stats['total_tokens'] = $stats['total_tokens'] + $usage['total_tokens'];
		if ($createdTitlesAmount or $createdTitlesAmount < 1)
			$createdTitlesAmount = 0;

		$stats['created_titles'] = $stats['created_titles'] + $createdTitlesAmount;

		update_option('clapac_iagai_post_stats', $stats);
	}

	function convertSubtitlesToArray($text){

		$re = '/<h([1-6])>(.*?)<\/h[1-6]>/i';
		$subst = '<h$1>'.strip_tags('$2').'</h$1>';
		$text = preg_replace($re, $subst, $text);
		preg_match_all('/<h([1-3])>(.*?)<\/h[1-3]>/', $text, $matches);

		$titles = array();
		foreach ($matches[1] as $i => $level) {
			$title = trim($matches[2][$i]);
			$titles[] = array('level' => $level, 'title' => $title);
		}
		return($titles);
	}

	// Call the openAI API and return the full response JSON
	private function call_openAI_API($prompt, $config, $openAI_key){

		$request_body = [
            "model"             => $config['engine'],
			"prompt"            => $prompt,
			"max_tokens"        => $config['max_tokens'],
			"temperature"       => $config['temperature'],
			"top_p"             => $config['top_p'],
			"presence_penalty"  => $config['presence_penalty'],
			"frequency_penalty" => $config['frequency_penalty'],
			"best_of"           => 1,
			"stream"            => false,
		];

		$postfields = json_encode( $request_body );
		$curl       = curl_init();
		curl_setopt_array( $curl, [
			CURLOPT_URL            => "https://api.openai.com/v1/completions", //https://api.openai.com/v1/engines/" . $config['engine'] . "/completions",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 100,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_POSTFIELDS     => $postfields,
			CURLOPT_HTTPHEADER     => [
				'Content-Type: application/json',
				'Authorization: ' . 'Bearer ' . $openAI_key
			],
		] );

		$response = curl_exec( $curl );
		$err      = curl_error( $curl );
		curl_close( $curl );

		if ( $err ) {
            if (str_contains($err, 'Connection refused')){
                echo "<h4 class='iagai_h4'>Connection error with the OpenAI website. Try later please.</h4>";
                return null;
            }
			echo "Error #:" . $err;
		} else {
			return $response;
		}
	}

}