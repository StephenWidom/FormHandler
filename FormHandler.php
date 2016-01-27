<?php

class FormHandler
{
	public $testing = false; 	// If true, don't actually send any emails
	public $show_message = false;	// Output all information received from form, good for debugging
	public $to; 			// Email address which will receive the form submission
	public $subject = "Form Submission Received"; // Subject of the generated email
	public $cc;			// Email addresses to carbon copy
	public $bcc; 			// Email addresses to blind carbon copy
	public $required = array();	// Array of required input names
	public $from_name;		// Name the generated email will come from
	public $from_email;		// Email the generated email will come from
	public $validate_emails = true; // Check whether email addresses submitted through form are valid
	public $filter_spam = false;	// Check for a $_POST['timer'] value
	public $html_email = true;	// Send an HTML email
	public $include_reload_link = false;	// Include a link to reload the page (to try again on failure)
	private $reload_link;		// Message containing reload link to append to error messages
	private $this_page;		// Used for including a reload link
	public $success_message = "Thanks for contacting us!";	// Displayed on successful form submission
	public $spam_error = "There was an error with your attempt to contact us."; // Displayed on suspicious form submission
	public $redirect_url; 		// URL to redirect to on succesful submission

	function __construct($to = '', $subject = '')
	{
		if (!empty($to)) $this->to = $to;
		if (!empty($subject)) $this->subject = $subject;
		$this->this_page = (empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['REQUEST_URI'] : $_SERVER['HTTP_REFERER'];
		$this->reload_link = ' <a href="' . $this->this_page . '"> Click here to try again.</a>';
	}

	private function displayMessageQuit($message, $success = false)
	{
		echo $message, (!$success && $this->include_reload_link) ? $this->reload_link : '';
		return $success;
	}

	private function implodeArray($array)
	{
		if (!empty($array) && is_array($array))
			$array = implode(',', $array);

		return $array;
	}
		

	public function handle()
	{

		// Make sure essential info has been provided
		if (!$this->testing && empty($this->to))
			return $this->displayMessageQuit("A 'to' email address hasn't been specified.");

		// If the submitter doesn't have an IP address, let's assume it's spam
		if (empty($_SERVER['REMOTE_ADDR'])){
			$this->displayMessageQuit($spam_error);	
			return false;
		}

		// Further spam prevention - works well with github.com/StephenWidom/validatr
		if ($this->filter_spam){
			if (empty($_POST['timer']) || !is_numeric($_POST['timer']) || $_POST['timer'] < 3 || $_POST['timer'] > 9999)
				return $this->displayMessageQuit($spam_error);
		}
		unset($_POST['timer']);
		
		// Let's loop through and sanitize each value submitted
		foreach ($_POST as $key => &$value){
			
			$value = nl2br(strip_tags(trim($value)));
				
			// Check if a required value is empty
			if (empty($value) && in_array($key, $this->required))
				return $this->displayMessageQuit('Please complete all required fields.');
				
			// Validate any email addresses submitted through the form
			if ($this->validate_emails){
				if (strpos(strtolower($key), 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL))
					return $this->displayMessageQuit("Please provide a valid email address.");
			}	

			// Prevent overwriting last value
			unset($value);
	
		}
		
		// No errors encountered so far...
		extract($_POST);

		$this->to = $this->implodeArray($this->to);
		$this->cc = $this->implodeArray($this->cc);
		$this->bcc = $this->implodeArray($this->bcc);

		$headers = "From: $this->from_name <$this->from_email>\r\n";
		$headers .= "Reply-to: $this->from_email\r\n";
		if (!empty($this->cc))
			$headers .= "Cc: $this->cc\r\n";
		if (!empty($this->bcc))
			$headers .= "Bcc: $this->bcc\r\n";
		if ($this->html_email){
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		}

		$message = "<html><body><div id='formhandler_output'>";

		foreach ($_POST as $key => $value){
			$key = str_replace('_', ' ', ucfirst($key));
			if ($key == $value)
				$value = '';
			$message .= "<strong>$key:</strong> $value\r\n\r\n<br><br>";
		}

		$message .= "<hr>";
		$message .= "<em>I.P. Address: " . $_SERVER['REMOTE_ADDR'] . "</em>\r\n<br>";
		$message .= "<em>Referral URL: $this->this_page</em>\r\n<br>";
		$message .= "</div></body></html>";

		if (!$this->html_email)
			$message = strip_tags($message);
		
		if (!$this->testing){
			if (!mail($this->to, $this->subject, $message, $headers))
				return $this->displayMessageQuit("We've encountered an error.");
		}

		// If we've made it this far, everything went smoothly!

		// Redirect to success url, if value has been set
		if (isset($this->redirect_url)){
			header('Location: ' . $this->redirect_url);
			exit;
		}

		// If no value for redirect_url has been set, display success message
		$this->displayMessageQuit($this->success_message, true);
		if ($this->show_message)
			echo $message;
		return true;

	}

}
