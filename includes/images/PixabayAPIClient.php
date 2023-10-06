<?php

class PixabayAPIClient {
	const API_ROOT = 'https://pixabay.com/api/';
	private $apiClient;
	private $options = [];

	public function __construct(array $options)
	{
		$this->apiClient = new Client(['base_url' => self::API_ROOT]);
		if (empty($options['key'])) {
			throw new \Exception('You must specify "key" parameter in constructor options');
		}
		$this->parseOptions($options);
	}

	private function parseOptions(array $options)
	{
		foreach ($this->optionsList as $option) {
			if (isset($options[$option])) {
				$this->options[$option] = $options[$option];
			}
		}
	}



}