<?php
class AI_Config {

	public static $MOOD_EMOTION;
	public static $TONE;
	public static $STYLE;


	public static function init(){
		self::$MOOD_EMOTION = array (
			'Nostalgic' => array('label' => __('Nostalgic', 'clapac_iagai'), 'description' => __('Evokes feelings of longing or reminiscence for the past.', 'clapac_iagai')),
			'Inspirational' => array('label' => __('Inspirational', 'clapac_iagai'), 'description' => __('Evokes feelings of motivation or encouragement.', 'clapac_iagai')),
			'Motivational' => array('label' => __('Motivational', 'clapac_iagai'), 'description' => __('Evokes feelings of inspiration or ambition.', 'clapac_iagai')),
			'Educational' => array('label' => __('Educational', 'clapac_iagai'), 'description' => __('Evokes feelings of learning or gaining knowledge.', 'clapac_iagai')),
			'Reflective' => array('label' => __('Reflective', 'clapac_iagai'), 'description' => __('Evokes feelings of contemplation or introspection.', 'clapac_iagai')),
			'Amusing' => array('label' => __('Amusing', 'clapac_iagai'), 'description' => __('Evokes feelings of humor or entertainment.', 'clapac_iagai')),
			'Exciting' => array('label' => __('Exciting', 'clapac_iagai'), 'description' => __('Evokes feelings of thrill or adventure.', 'clapac_iagai')),
			'Compassionate' => array('label' => __('Compassionate', 'clapac_iagai'), 'description' => __('Evokes feelings of empathy or understanding.', 'clapac_iagai')),
			'Romantic' => array('label' => __('Romantic', 'clapac_iagai'), 'description' => __('Evokes feelings of love or affection.', 'clapac_iagai')),
			'Thought-provoking' => array('label' => __('Thought-provoking', 'clapac_iagai'), 'description' => __('Evokes feelings of curiosity or contemplation.', 'clapac_iagai')),
			'Enthusiastic' => array('label' => __('Enthusiastic', 'clapac_iagai'), 'description' => __('Evokes feelings of excitement or positivity.', 'clapac_iagai')),
			'Sarcastic' => array('label' => __('Sarcastic', 'clapac_iagai'), 'description' => __('Evokes feelings of irony or mockery.', 'clapac_iagai')),
			'Witty' => array('label' => __('Witty', 'clapac_iagai'), 'description' => 'Evokes feelings of cleverness or humor.'));


		self::$TONE = array (
			'Formal' => array('label' => __('Formal', 'clapac_iagai'), 'description' => __('Uses precise and correct language, with a serious and academic tone.', 'clapac_iagai')),
			'Informal' => array('label' => __('Informal', 'clapac_iagai'), 'description' => __('Uses a more casual and colloquial language, with a friendly and personal tone.', 'clapac_iagai')),
			'Conversational' => array('label' => __('Conversational', 'clapac_iagai'), 'description' => __('Uses language similar to speech, with a relaxed and colloquial tone.', 'clapac_iagai')),
			'Persuasive' => array('label' => __('Persuasive', 'clapac_iagai'), 'description' => __('Uses language to convince or persuade the reader to take an action or opinion.', 'clapac_iagai')),
			'Humorous' => array('label' => __('Humorous', 'clapac_iagai'), 'description' => __('Uses language that is intended to be funny or amusing.', 'clapac_iagai')),
			'Sarcastic' => array('label' => __('Sarcastic', 'clapac_iagai'), 'description' => __('Uses language that is intended to mock or convey irony.', 'clapac_iagai')),
			'Ironical' => array('label' => __('Ironical', 'clapac_iagai'), 'description' => __('Uses language that is intended to convey a meaning opposite to its literal or usual sense.', 'clapac_iagai')),
			'Poetic' => array('label' => __('Poetic', 'clapac_iagai'), 'description' => __('Uses language that is intended to be evocative and imaginative.', 'clapac_iagai')),
			'Professional' => array('label' => __('Professional', 'clapac_iagai'), 'description' => __('Uses language that is intended to be clear, concise, and appropriate for professional or business setting.', 'clapac_iagai')),
			'Approachable' => array('label' => __('Approachable', 'clapac_iagai'), 'description' => __('Uses language that is intended to be easy to understand and relate to.', 'clapac_iagai')),
			'Thoughtful' => array('label' => __('Thoughtful', 'clapac_iagai'), 'description' => __('Uses language that is intended to be reflective and contemplative.', 'clapac_iagai')),
			'Passionate' => array('label' => __('Passionate', 'clapac_iagai'), 'description' => 'Uses language that is intended to express strong feelings or emotions.'));


		self::$STYLE = array (
			'Blog post' => array('label' => __('Blog post', 'clapac_iagai'), 'description' => __('Presents information, opinions or personal experiences in a casual and conversational tone. It can be informative, reflective, amusing or thought-provoking.', 'clapac_iagai')),
			'Narrative' => array('label' => __('Narrative', 'clapac_iagai'), 'description' => __('Tells a story or relates an event or series of events.', 'clapac_iagai')),
			'Descriptive' => array('label' => __('Descriptive', 'clapac_iagai'), 'description' => __('Describes a person, place, thing, or event.', 'clapac_iagai')),
			'Argumentative' => array('label' => __('Argumentative', 'clapac_iagai'), 'description' => __('Presents an argument or claims in support of a point of view.', 'clapac_iagai')),
			'Explanatory' => array('label' => __('Explanatory', 'clapac_iagai'), 'description' => __('Explains or clarifies something in a way that is easy to understand.', 'clapac_iagai')),
			'Instructional' => array('label' => __('Instructional', 'clapac_iagai'), 'description' => __('Provides instructions or guidance on how to do something.', 'clapac_iagai')),
			'News-based' => array('label' => __('News-based', 'clapac_iagai'), 'description' => __('Presents information about recent events or happenings.', 'clapac_iagai')),
			'Opinion-based' => array('label' => __('Opinion-based', 'clapac_iagai'), 'description' => __('Presents a personal view or opinion on a topic.', 'clapac_iagai')),
			'How-to' => array('label' => __('How-to', 'clapac_iagai'), 'description' => __('Provides step-by-step instructions on how to do something.', 'clapac_iagai')),
			'List-based' => array('label' => __('List-based', 'clapac_iagai'), 'description' => __('Presents information in a list format.', 'clapac_iagai')),
			'Interview-based' => array('label' => __('Interview-based', 'clapac_iagai'), 'description' => __('Presents information in the form of a question-and-answer format.', 'clapac_iagai')),
			'Review-based' => array('label' => __('Review-based', 'clapac_iagai'), 'description' => 'Presents an evaluation or critique of a product, service or experience.'));
	}


	public static $PUBLICATION_DATE_CONFIG = array(
		'start_date' => '2022-11-30',
		'end_date' => '2023-01-10',
		'articles_day' => '4',
		'start_time' => '08:00',
		'end_time' => '20:00'
	);

	public static $TITLE_GENERATOR_CONFIG = array(
		'engine' => "text-davinci-003",
		'temperature' => 0.78,
		'top_p' => 1,
		'frequency_penalty' => 0.45,
		'presence_penalty' => 0.45,
		'max_tokens' => 1000,
		'waiting_time' => 8
	);

	public static $TOPIC_GENERATOR_CONFIG = array(
		'engine' => "text-davinci-003",
		'temperature' => 0.90,
		'top_p' => 1,
		'frequency_penalty' => 0.15,
		'presence_penalty' => 0.35,
		'max_tokens' => 1800,
		'waiting_time' => 4
	);

	public static $POST_CONTENT_CONFIG = array(
		'engine' => "text-davinci-003",
		'temperature' => 1,
		'top_p' => 0.8,
		'frequency_penalty' => 0.35,
		'presence_penalty' => 0.35,
		'max_tokens' => 1200,
		'waiting_time' => 8
	);

	public static $PERMALINK_CONFIG = array(
		'engine' => "text-davinci-003",
		'temperature' => .7,
		'top_p' => 1,
		'frequency_penalty' => 0.35,
		'presence_penalty' => 0.35,
		'max_tokens' => 200,
		'waiting_time' => 3
	);
}
AI_Config::init();