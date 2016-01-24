## FormHandler
Basic PHP form handler class.

## USE:
```php
$form = new FormHandler();
$form->to = "your@email.com";
$form->subject = "Contact Form Submission Received!";
$form->handle();
```

## OPTIONS (and default values):
### testing = false (bool)
If true, won't actually send an email. Use with `show_message` for testing/debugging

### show_message = false (bool)
Great for debugging. Displays all received form data

### to (string, array)
Email address(es) which will receive form submissions

### subject = "Form Submission Received"
Subject of generated email

### cc (string, array)
Email address(es) to CC

### bcc (string, array)
Email address(es) to BCC

### required (array)
Array of required input names

### from_name (string)
Name the generated email will come from

### from_email (string)
Email address the generated email will come from

### validate_emails = true (bool)
Check whether email addresses submitted through form are valid

### filter_spam = false (bool)
Help prevent spam by checking for a $_POST['timer'] value - works well with [validatr](https://github.com/stephenwidom/validatr)

### html_email = true (bool)
Sends an html email as opposed to a plain text email

### include_reload_link = false (bool)
Includes a link to reload page on failed form submission

### success_message = "Thanks for contacting us!" (string)
Displayed on successful form submission

### spam_error = "There was an error with your attempt to contact us." (string)
Displayed after a suspicious form submission

## NOTES:
Be sure to include the FormHandler.php file: `include('FormHandler.php');`

It only makes sense to use FormHandler after a form submission, so wrap it in a conditional:
```php
if ($_SERVER['REQUEST_METHOD'] == "POST"){
	include('FormHandler.php');
        $form = new FormHandler();
	// And so on...
}
```
[See it in action](http://stephenwidom.com/projects/FormHandler/)

*Developed by Stephen Widom - http://stephenwidom.com*
