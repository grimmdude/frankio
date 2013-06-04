<?php
function __construct() {
	$this->DB = new DB;
	$this->name = 'Frank';
	$this->system_questions = array("/What'?s your name.?$/i" 								=> 'My name is '.$this->name.'.',
									'/Where are you.?$/i'									=> "I'm in my computer.",
									"/Who (created|made|wrote) you.?$/i"					=> "Garrett Grimm created me.",
									"/^(Hey|Hi|What'?s up)( ".$this->name.")?.?$/i"			=> "What's up?",
									"/(What is today|what's the date|^date|^today)\??$/i"	=> "The date is ".date('m/d/Y').".",
									"/^time$/i"												=> "It's ".date('g:i A T'),
									"/(Thanks|Thank you)( ".$this->name.")?.?$/i"			=> "No problem!",
									"/\b[hae]{3,}\b$/i"										=> "heheh",
									"/^(ok|alright|k|okay)$\b/i"							=> "OK"
									);
	$this->default_responses = array(
									'*input* what?',
	 								'What about *input*?',
									"I'm not sure how to answer your question.  You can teach me though.  See my instructions below.",
									"LOL",
									"You would say that.",
									"Uh Oh...",
									"Deez nutz!",
									"Yep yep yep"
									);
	$this->bad_words = array('fuck','nigger','cunt','pussy','cock','cack','anal','shit','raghead');
	$this->add_puncuation_words = array(
									"whats"		=> 	"what's",
									"thats"		=> 	"that's",
									"heres"		=> 	"here's",
									"wheres"	=> 	"where's",
									"theres"	=>	"there's",
									"hes"		=>	"he's",
									"shes"		=>	"she's",
									"its"		=>	"it's",
									"im"		=>	"I'm",
									"freind"	=>	"friend",
									"thier"		=>	"their"
									);
}

public static function add_commands($commands = false) {
	if ($commands !== false) {
		if (is_array($commands)) {
			self::$commands = array_merge(self::$commands, $commands);
		}
		else {
			self::$commands[] = $commands;
		}
	}
}

/**
 * The function that links it all together and outputs a response array
 */
public function get_response($question) {
	
	// Log unaltered input
	$this->log_input($question);
	
	// Build a regex
	$regex = $this->build_regex($question);
	
	// Setup return array
	$return_array = array(
						'question'	=> $question,
						'regex'		=> $regex
						);
	
	// Remove any double quotes
	$question = $this->remove_double_quotes($question);

	// Add puncuation to the question if needed
	$question = $this->add_punctuation($question);
	
	// First check for naughty words
	if ($this->has_bad_words($question)) {
		$return_array['response'] = "Woah turbo, easy on the language.";
		$return_array['type'] = 'naughty';
		return $return_array;
	}
	
	// Now check to see if this question has a system response		
	if ($system_answer = $this->get_system_answer($question)) {
		$return_array['response'] = $system_answer;
		$return_array['type'] = 'system';
		return $return_array;
	}
	
	//$random_learn = $this->random_learn($question);
	//if ($random_learn) {
	//	$return_array['response'] = $random_learn['response'];
	//}
	
	/*
	$parts_of_speech = $this->learn_parts_of_speech($question);
	$return_array['options'] = $parts_of_speech['options'];
	$return_array['type'] = 'speech';
	$return_array['response'] = $parts_of_speech['response'];
	
	return $return_array;
	*/
	
	// Then check if they're looking for a map
	if ($map = $this->get_map($question)) {
		$return_array['response'] = $map;
		$return_array['type'] = 'map';
		return $return_array;
	}
	
	// Maybe it's a wiki entry
	if ($wiki = $this->wiki_lookup($question)) {
		$return_array['response'] = $wiki;
		$return_array['type'] = 'wiki';
		return $return_array;
	}
	
	// Then check to see if they're trying to teach us a phrase		
	if ($learn = $this->learn_response($question)) {
		$return_array['response'] = $learn;
		$return_array['type'] = 'learn';
		return $return_array;
	}
	
	// Otherwise see if we have an answer in the db
	$query = "SELECT a.`answer` FROM `answers` a, `questions` q, `questions_answers` qa 
			WHERE q.`question` REGEXP '".mysql_real_escape_string($regex)."' 
			AND q.`question_id` = qa.`question_id` AND a.`answer_id` = qa.`answer_id` ORDER BY RAND() LIMIT 1";

	$result = $this->DB->query($query);

	if ($row = $this->DB->getRow($result)) {
		$return_array['response'] = ucfirst($row['answer']);
		$return_array['type'] = 'db';
		return $return_array;
	}

	// If nothing has returned yet then pull a default response
	$return_array['response'] = $this->get_default_response($question);
	$return_array['type'] = 'default';
	return $return_array;
}

// Register a new question/answer match
public function add_answer($question, $answer) {
	// First check for naughty words
	if ($this->has_bad_words($question) || $this->has_bad_words($answer)) {
		return "Woah turbo, easy on the language.\n";
	}
	
	// Then check if there's a url in there
	if ($this->has_url($question) || $this->has_url($answer)) {
		return "Sorry, I can't take urls at this time.";
	}
	
	// Then check if the question exists
	$query = "SELECT * FROM `questions` WHERE `question` = '".mysql_real_escape_string($question)."'";
	$result = $this->DB->query($query);
	$question_row = $this->DB->getRow($result);
	
	// Then check if this answer exists
	$query = "SELECT * FROM `answers` WHERE `answer` = '".mysql_real_escape_string($answer)."'";
	$result = $this->DB->query($query);
	$answer_row = $this->DB->getRow($result);
	
	// If this question doesn't already exist then add it
	if (!$question_row) {
		$query = "INSERT INTO `questions` (`question`, `date_added`) VALUES('".mysql_real_escape_string($question)."', NOW())";
		$this->DB->query($query);
		$question_id = mysql_insert_id();
	}
	else {
		$question_id = $question_row['question_id'];
	}
	
	// If this answer doesn't already exist then add it
	if (!$answer_row) {
		$query = "INSERT INTO `answers` (`answer`, `date_added`) VALUES('".mysql_real_escape_string($answer)."', NOW())";
		$this->DB->query($query);
		$answer_id = mysql_insert_id();
	}
	else {
		$answer_id = $answer_row['answer_id'];
	}
	
	// Now check if a link exists between this question/answer
	$query = "SELECT * FROM `questions_answers` WHERE `question_id` = ".$question_id." AND `answer_id` = ".$answer_id;
	$result = $this->DB->query($query);
	
	// If it's not linked up then make it so
	if ($this->DB->countRows($result) == 0) {
		$query = "INSERT INTO `questions_answers` (`question_id`, `answer_id`) VALUES(".$question_id.",".$answer_id.")";
		$result = $this->DB->query($query);
	}
	return "Thanks for the tip!";
}

/**
 * Checks if input is a 'learn' string
 */
private function learn_response($input) {
	$this->learn_regex = '/When I (say|ask) (.*) you (say|answer) (.*)/i';	
	preg_match_all($this->learn_regex,$input,$matches, PREG_PATTERN_ORDER);
	if (isset($matches[2][0]) && isset($matches[4][0])) {
		return $this->add_answer(trim($matches[2][0],"'"), trim($matches[4][0],"'"));
	}
	return false;
}

/**
 * The group of functions below initiate a learning sequence
 */

/**
 * This will randomly prompt the user with a question for frank to learn
 */
private function prompt_question() {
	// 1 in 5 chance
	if (rand(1,5)  == 1 ) {
		$_SESSION['prompt'] = true;
		return 'Would you like to help me learn some things?';
	}
	return false;
}

/**
 * Teach frank some synonyms! 2 step process that repeats until user types 'stop'
 */
private function learn_synonyms($input) {
	if ($_SESSION['mode'] == 'synonyms') {
		// Handle last response
		if ($_SESSION['step'] == 1) {
			// Handle answer from initial question
			
			$_SESSION['step'] == 2;
			// Present user with two words that we have stored as synonyms and ask the user if they are correct
			
			return 'Thanks, can you tell me if these two words are synonyms?';
		}
		elseif ($_SESSION['step'] == 2) {
			// Check for 'Yes' or 'No'
			// Reset to step 1
		}
	}
	else {
		// Otherwise we're just starting out
		$_SESSION['mode'] = 'synonyms';
		$_SESSION['step'] = 1;
		// Pull a word from the words table that has little or no synonyms and ask the user for a synonym
	}
}

/**
 * Teach frank some band names!
 */
private function learn_band_names() {
	
}

/**
 * Teach frank some science!
 */
private function learn_science() {
	
}

/**
 * Teach frank parts of speech!
 * 2 step process
 */
private function learn_parts_of_speech($input) {
	$input = ucfirst($input);
	
	$return_array['options'] = array('Noun','Pronoun','Adjective','Verb','Adverb','Interjection','Preposition','Conjunction');
	
	if ($_SESSION['mode'] == 'speech' && isset($_SESSION['speech']['word_id']) && in_array($input, $return_array['options'])) {
		
		// Update db with input
		$query = "UPDATE `words` SET `part_of_speech` = '".mysql_real_escape_string($input)."' WHERE `word_id` = ".mysql_real_escape_string($_SESSION['speech']['word_id']).";";
		$this->DB->query($query);
		
		// Get new word
		$_SESSION['speech'] = $this->get_word();
		
		$return_array['response'] = 'Awesome.  How about <strong> '.$_SESSION['speech']['word'].'</strong>? (or type skip)';
		
		return $return_array;			
	}
	else {
		// Start it off
		$_SESSION['mode'] = 'speech';
		
		// Pull word from the db with no assigned part of speech
		$_SESSION['speech'] = $this->get_word();
		$return_array['response'] = 'Can you help me?  What part of speech is <strong> '.$_SESSION['speech']['word'].'</strong>? (or type skip)';
		
		return $return_array;
	}
}

private function get_word($random = true, $needs_part_of_speech = true) {
	$query = "SELECT * FROM `words`";
	if ($needs_part_of_speech) {
		$query .= " WHERE `part_of_speech` IS NULL";
	}
	if ($random) {
		$query .= " ORDER BY RAND() LIMIT 1";
	}
	$result = $this->DB->query($query);
	$row = $this->DB->getRow($result);
	return $row;
}


/**
 * Checks for bad words
 */
private function has_bad_words($string) {
	foreach ($this->bad_words as $word) {
		if (preg_match('/\b'.$word.'\b/', $string)) {
			return true;
		}
	}
	return false;
}

private function has_url($input) {
	$url_regex = '/\b(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?\b/i';
	if (preg_match($url_regex, $input)) {
		return true;
	}
	return false;
}

private function has_email($input) {
	$email_regex = '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i';
	if (preg_match($email_regex, $input)) {
		return true;
	}
	return false;
}

/**
 * Gets system defined answer if found.
 */
private function get_system_answer($input) {		
	// See if this question matches any of our system default questions
	foreach ($this->system_questions as $question => $answer) {
		if (preg_match($question, $input)) {
			return $answer;
		}
	}
	return false;
}

private function get_default_response($input) {										
	$response = $this->default_responses[array_rand($this->default_responses,1)];
	return str_replace('*input*', $input, $response);
}

/**
 * Add punctuation to words that commonly gets none and fix other common issues
 */
private function add_punctuation($string) {										
	foreach ($this->add_puncuation_words as $word => $word_with_punc) {
		$string = preg_replace('/\b'.$word.'\b/', $word_with_punc, $string);
	}
	return $string;
}

/**
 * Removes double quotes from a string
 */
private function remove_double_quotes($input) {
	$return = str_replace('"','',$input);
	return $return;
}

/**
 * Adds a keyword -> url relationship
 */
private function learn_link($title, $url) {
	
}

/**
 * Strip ending punctuation
 */
private	function strip_end_punctuation($string) {
	$this->end_puncutation = array('.','!',',','?');
	
}

/**
 * Look up in wikipedia
 */
private function wiki_lookup($input) {
	preg_match_all("/^wiki (.*)$/i",$input,$matches, PREG_PATTERN_ORDER);
	if (isset($matches[1][0])) {
		$wiki_lookup = $matches[1][0];
		
		// Capitalize words
		$wiki_lookup = ucwords($wiki_lookup);

		$url = 'http://en.wikipedia.org/w/index.php?action=render&title='.urlencode($wiki_lookup); //gets html only with no api wrapper

		//$url = 'http://en.wikipedia.org/w/api.php?format=php&action=parse&redirects=&page='.urlencode($input); // API
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'User-Agent: FrankIO/1.0 (http://grimmdude.com/frankio; garrett@grimmdude.com)');
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//$res = unserialize(curl_exec($ch)); // API
		$res = curl_exec($ch);
		curl_close($ch);

		return $res;

		//$wiki_content = $res['parse']['text']['*'];

		//return $wiki_content;
	}
	return false;	
}

/**
 * Builds a regex from the input that can be used to query the db
 */
private function build_regex($input) {
	// Start regex
	$regex = $input;
	
	// Make ending punctuation optional
	if (in_array(substr($regex, -1), array('.','?','!'))) {
		//$regex = substr_replace($regex, '\\'.substr($regex,-1).'*',-1);
	}
	
	// Escape needed chars
	$regex_escape_chars = array('[',']','^','$','.','|','?','*','+','(',')','{','}','-');
	foreach ($regex_escape_chars as $char) {
		$regex = str_replace($char, '\\'.$char, $regex);
	}
	
	// Make ' and " optional
	$regex = str_replace("'","'?",$regex);
	$regex = str_replace('"','"?',$regex);
	
	// match to end
	$regex = $regex.'$';
	
	return $regex;
}

/**
 * Offers suggested answers to the question and asks for input on relevance
 */
private function offer_suggested_answers($input) {
	// First query for answers that contain phrasal matches, limit 3
	
	
	// Then output the answers with numbers for choosing
	
}

/**
 * Get a google map link if requested
 */
private function get_map($input) {
	preg_match_all("/where('?s| is) (.*)\??$/i",$input,$matches, PREG_PATTERN_ORDER);
	if (isset($matches[1][0])) {
		return 'https://maps.google.com/maps?hl=en&q='.urlencode($matches[2][0]);
	}
	return false;
}