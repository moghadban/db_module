<?php
// Define namespace
namespace Drupal\db_module\Form;

// Define Drupal 8 dependencies/libraries:
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormBase;
use Drupal\db_module\DbModuleRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use DependencySerializationTrait;
/**
 * Sample UI to update a record.
 *
 * @ingroup db_module
 */
class DbModuleUpdateForm implements FormInterface, ContainerInjectionInterface {
	use StringTranslationTrait;
	use MessengerTrait;

	/**
	* the database repository service.
	*
	* @var \Drupal\db_module\DbModuleRepository
	*/
	protected $repository;

	/**
	* The current user.
	*
	* Service is needed to check if the user is logged in.
	*
	* @var \Drupal\Core\Session\AccountProxyInterface
	*/
	protected $currentUser;

	/**
	* {@inheritdoc}
	*
	* ContainerInjectionInterface pattern is used to inject the
	* current user and to get the string_translation service.
	*/
	public static function create(ContainerInterface $container)
	{
		$form = new static(
		  $container->get('db_module.repository'),
		  $container->get('current_user')
		);
		// The StringTranslationTrait trait manages the string translation service
		// for us. We can inject the service here.
		$form->setStringTranslation($container->get('string_translation'));
		$form->setMessenger($container->get('messenger'));
		return $form;
	}

	/**
	* Construct the new form object.
	*/
	public function __construct(DbModuleRepository $repository, AccountProxyInterface $current_user)
	{
		$this->repository = $repository;
		$this->currentUser = $current_user;
	}
	/**
	* {@inheritdoc}
	* Define this for id as db_module_updateform
	*/
	public function getFormId()
	{
		return 'db_module_updateform';
	}
	/**
	* {@inheritdoc}
	* Define the main function that builds the form and pass the form as array along with form state, and the id
	* that's needs updating or deleting
	*/
	public function buildForm(array $form, FormStateInterface $form_state, $uid = '')
	{
		// Retrieve the current link in order to get the argument passed from dbModuleListForm.php.
		$currentLink =  \Drupal::request()->getRequestUri();
		$path_args = explode('=', $currentLink);

		// Get the uid from post URL passed from DbModuleListForm.php
		$uid = \Drupal::request()->query->get('uid');

		// if the state form 'uid' returns no employee id, grab it from get URL, or just grab it manually
		(empty($uid))?  $path_args[1] :  $_GET["uid"] ;
		
		// Define back to list URL
		$back_url =  'modules/db_module/list';

		// Define adding an entry URL
		$add_url = 'modules/db_module/add';

		// Define list form division with prefix to contain form elements,
		// closing suffix is below at the end of form function 
		$form['#prefix'] = '<div id="update-form" class="jumbotron">';

		// Connect to db_module database, and check if passed 'uid' or Employee Id is equal to the result retrieved from Database
		$database = \Drupal::database();

		// Select all fields for this operation, then check if passed uid exists in db_module
		$select = $database->select('db_module', 't');
		$query = $select->fields('t', [ 'uid','first_name','last_name','gender','street_address','city','state','zip'])->condition('@uid',$uid,'=')->execute();

		// Fetch associate array for this specific field
		$entries = $query->fetchAssoc();
	
 
  
		  // If no entries exist, prompt the user with form message and display button element that takes back to list
		if (empty($entries))
		{
			$form['no_values'] = [
			'#value' => $this->t('No entry exists in database!'),
			];

			$form['message'] = [
			'#markup' => '<h2 align="center" style="padding: 20px 5px;">No entries exist in the table db_module table.</h2>',
			];

			$form['back'] = [
			'#type' => 'button',
			'#value' => t('Back to list'),
			'#attributes' => array(
				  'onclick' => array('goBack()'),
				  'class' => array('btn','btn-primary','btn-lg'),
				  ),
			];
			 return $form;
		}
		else  // if an entry matches the passed URL uid or employee id & db_module returned positive results of valid entry
		{
			// Tell the user if there is nothing to display.
		  $headers = 	[
					['data' => $this->t('Employee Id')],
					['data' => $this->t('First Name')],
					['data' => $this->t('Last Name')],
					['data' => $this->t('Gender')],
					['data' => $this->t('Street Address')],
					['data' => $this->t('City')],
					['data' => $this->t('State')],
					['data' => $this->t('Zip Code')],
		  ];
			// Define empty array $keyed_entries to store all returned values into to be used in the form later
			$keyed_entries = [];

			// Define options array to store arrays which hold the value of the returned columns from db_module to
			// be used as single entry table
			  $options = [];

			 
	
		  // Store returned column in options array
		  $options[$uid] = [
			'@uid' => $entries['uid'],
			'@first_name' => $entries['first_name'],
			'@last_name' => $entries['last_name'],
			'@gender' => $entries['gender'],
			'@street_address' => $entries['street_address'],
			'@city' => $entries['city'],
			'@state' => $entries['state'],
			'@zip' => $entries['zip'],
		  ];

	// Grab the uid.
	$uid = $form_state->getValue('uid');
	// Use the uid to set the default entry for updating.

	// Save the entries into the $form_state. We do this so the AJAX callback
	// doesn't need to repeat the query.
	$form_state->setValue('entries', $keyed_entries);

		 // Define the redirection URL to be used in the element defined below it
		  $login_redirect = 'Drupal.url("user/login");';
		 /**
		 * Define $element that is used as markup in $form.
		 * The $element contains combination of description of the list page, and a modal. 
		 * Modal Usage:
		 *		In order to ensure proper use of user permissions, the edit/delete function or button will only operate only
		 *		for authenticated users. Thus, an exception is made that if a non-logged user attempts to mouseover or
		 *		hover over the edit/delete buttons, a popover will appear. Furthermore, if the non-logged in user clicks
		 *		on edit/delete, a bootstrap modal will launch prompting the user to either login, return to the list page,
		 *		or just stay within same page.
		 */
		  $element =
			'<div class="container"><h2 class="text-center"><strong>Edit or Update Entry from Database</strong></h2><h3>Title: Custom generated database API Module<br>Author: Mojahed Ghadban<br>Package: custom module with Drupal version 8.x-1.0 compatibility</br>Module Name: db_module<br>Module Version: 1.1<br><br></he3><h3><strong>Description:</strong></h3>
			<p>Update entry allows the user to update or delete an entry from db_module database. In order to ensure proper use of user permissions, the update and delete functions or buttons will only operate only for authenticated users. Thus, an exception is made that if a non-logged user attempts to mouseover or hover over the update or delete buttons, a popover will appear. Furthermore, if the non-logged in user clicks on update or delete, a bootstrap modal will launch prompting the user to either login, return to the list page, or just stay within same page.</p>

			<h3><strong>Implementations and Drupal 8 usage:</strong></h3>
			<p>
			- Drupal 8 database update operation with call back function<br>
			- Drupal 8 database delete operation with call back function<br>
			- Drupal 8 user permissions<br>
			- Drupal 8 forms<br>
			- Custom jQuery/JavaScript<br>
			- Bootstrap popover and modal<br>
			<p><br><br><input name="uid" id="uid" type="hidden" value="'.$uid.'" /><input class="form-control-lg" name="textcheck" id="textcheck" type="text" value="" hidden /><br></div>
			<div class="modal fade" id="loginModalCenter" tabindex="-1" role="dialog" aria-labelledby="loginModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="dialog-centered">
			<div class="modal-content">
					<div class="modal-header">
					<h2 class="modal-title" id="loginModalLongTitle">Please Login First!</h2>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
					</div>
					<div class="modal-body">This operation requires authenticated users to login, please choose from the below options</div>
					<div class="modal-footer btn-group" align="center">
					<button type="button" class="btn btn-secondary modal-btn" data-dismiss="modal">Close</button><a type="button" href="list" class="btn btn-primary modal-btn" >Back to List</a><button id="login-page" type="button" class="btn btn-primary modal-btn" >Login</button>
					</div>
			</div>
			</div>
			</div>';

		  // Assign $element to $form['myelement'] as an array with key markup
		  // Declare allowed tags to allow most of not all HTML elements
		  $form['myelement'] = [
			'#markup' => $element,
			'#allowed_tags' => ['!--...--','!DOCTYPE','a','abbr','acronym','address','applet','area','article','aside','audio','b','base','basefont','bdi','bdo','big','blockquote','body','br','button','canvas','caption','center','cite','code','col','colgroup','data','datalist','dd','del','details','dfn','dialog','dir','div','dl','dt','em','embed','fieldset','figcaption','figure','font','footer','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','header','hr','html','i','iframe','img','input','ins','kbd','label','legend','li','link','main','map','mark','meta','meter','nav','noframes','noscript','object','ol','optgroup','option','output','p','param','picture','pre','progress','q','rp','rt','ruby','s','samp','script','section','select','small','source','span','strike','strong','style','sub','summary','sup','svg','table','tbody','td','template','textarea','tfoot','th','thead','time','title','tr','track','tt','u','ul','var','video','wbr'],    ];

		  // Attache all js/css libraries to $form['myelement'] => for above $element
		  // JS Libraries: dbmodule.js dbmodule_update.js, bootstrap.min.js, bootstrap.bundle.min.js, bootstrap.bundle.js,
		  // and all.js (for fontawsome)
		  // CSS Libraries: dbmodule.css, and bootstrap.bundle.min.css
		  $form['myelement']['#attached']['library'][] = 'db_module/dbmodule'; 
		  $form['myelement']['#attached']['library'][] = 'db_module/dbmodule_update';
		  $form['myelement']['#attached']['library'][] = 'db_module/bootstrap';
		  $form['myelement']['#attached']['library'][] = 'db_module/bootstrap.min';
		  $form['myelement']['#attached']['library'][] = 'db_module/bootstrap.bundle';	
		  $form['myelement']['#attached']['library'][] = 'db_module/bootstrap.bundle.min';

		  // Define one column view table 
		  $form['myelement']['table'] = [
			'#type' => 'table',
			'#header' => $headers,
			'#rows' => $options,
			'#empty' => t('No Entries found'),
			'#attributes' => array(
				  'class' => array('table','table-lg','table-hover','table-bordered','table-custom'),
				  ),
		  ];
   
		  // Define element uid as field-set and assign options and attributes to it
		  $form['uid'] = [
		  '#type' => 'textfield',
		  '#default_value' => $entries['uid'],
		  '#title' => $this->t('Autmotically Generated Employee Id'),
		  '#disabled' => TRUE,
		  '#attributes' => array(
				  'class' => array('form-control'),
				  ),
		  ];

		  // Define element First Name as input field and assign options and attributes to it
		  $form['first_name'] = [
		  '#type' => 'textfield',
		  '#default_value' => $entries['first_name'],
		  '#title' => $this->t('First Name'),
		  '#required' => TRUE,
		  '#attributes' => array(
				  'class' => array('form-control'),
				  ),
		  ];

		  // Define element Last Name as input field and assign options and attributes to it
		  $form['last_name'] = [
		  '#type' => 'textfield',
		  '#default_value' => $entries['last_name'],
		  '#title' => $this->t('Last name'),
		  '#required' => TRUE,
		  '#attributes' => array(
				  'class' => array('form-control'),
				  ),
		  ];

		  // Define element Gender as list and assign options and attributes to it
		  $form['gender'] = [
		  '#type' => 'select',
		  '#title' => t('Gender'),
		  '#options' => [
		  'Female' => $this->t('Female'),
		  'Male' => $this->t('Male'),
		  ],
		  '#default_value' => $entries['gender'],
		  '#attributes' => array(
				  'class' => array('custom-select'),
		  ),
		  ];

		  // Define element Street Address as input field and assign options and attributes to it
		  $form['street_address'] = [
		  '#type' => 'textfield',
		  '#default_value' => $entries['street_address'],
		  '#title' => $this->t('Street Address'),
		  '#required' => TRUE,
		  '#attributes' => array(
				  'class' => array('form-control'),
				  ),
		  ];

		  // Define element City as input field and assign options and attributes to it
		  $form['city'] = [
		  '#type' => 'textfield',
		  '#default_value' => $entries['city'],
		  '#title' => $this->t('City'),
		  '#required' => TRUE,
		  '#attributes' => array(
				  'class' => array('form-control'),
				  ),
		  ];

		  // Define element State as list and assign options and attributes to it
		  // with regards that if user kept the default value ('Select State') validation
		  // occurs prompting proper selection of state
		  $form['state'] = [
		  '#type' => 'select',
		  '#title' => t('state'),
		  '#required' => TRUE,
		  '#options' => [
				  'Select State'=>'Select State',
				  'AL'=>'AL',
				  'AK'=>'AK',
				  'AS'=>'AS',
				  'AZ'=>'AZ',
				  'AR'=>'AR',
				  'CA'=>'CA',
				  'CO'=>'CO',
				  'CT'=>'CT',
				  'DE'=>'DE',
				  'DC'=>'DC',
				  'FM'=>'FM',
				  'FL'=>'FL',
				  'GA'=>'GA',
				  'GU'=>'GU',
				  'HI'=>'HI',
				  'ID'=>'ID',
				  'IL'=>'IL',
				  'IN'=>'IN',
				  'IA'=>'IA',
				  'KS'=>'KS',
				  'KY'=>'KY',
				  'LA'=>'LA',
				  'ME'=>'ME',
				  'MH'=>'MH',
				  'MD'=>'MD',
				  'MA'=>'MA',
				  'MI'=>'MI',
				  'MN'=>'MN',
				  'MS'=>'MS',
				  'MO'=>'MO',
				  'MT'=>'MT',
				  'NE'=>'NE',
				  'NV'=>'NV',
				  'NH'=>'NH',
				  'NJ'=>'NJ',
				  'NM'=>'NM',
				  'NY'=>'NY',
				  'NC'=>'NC',
				  'ND'=>'ND',
				  'MP'=>'MP',
				  'OH'=>'OH',
				  'OK'=>'OK',
				  'OR'=>'OR',
				  'PW'=>'PW',
				  'PA'=>'PA',
				  'PR'=>'PR',
				  'RI'=>'RI',
				  'SC'=>'SC',
				  'SD'=>'SD',
				  'TN'=>'TN',
				  'TX'=>'TX',
				  'UT'=>'UT',
				  'VT'=>'VT',
				  'VI'=>'VI',
				  'VA'=>'VA',
				  'WA'=>'WA',
				  'WV'=>'WV',
				  'WI'=>'WI',
				  'WY'=>'WY',
				  'AE'=>'AE',
				  'AA'=>'AA',
				  'AP'=>'AP',
		  ],
		  '#default_value' => $entries['state'],
		  '#attributes' => array(
				  'class' => array('custom-select'),
				  ),
		  ];

		  // Define element Zip code as input field and assign options and attributes to it
		  // with regards that the element value will be validated to be 5 digits in length
		  $form['zip'] = [
		  '#type' => 'textfield',
		  '#default_value' => $entries['zip'],
		  '#title' => $this->t('Zip Code'),
		  '#required' => TRUE,
		  '#attributes' => array(
				  'class' => array('form-control'),
				  ),
		  ];
		  // Get current url
		  $currentLink =  \Drupal::request()->getRequestUri();
		  // Replace the end of url ('/add') with ('/list') to achieve proper routing to going back to main list table
		  $back_url =  str_replace('add', 'list', $currentLink);

		  // Define element back as button and set $back_url to it to be redirected to list on click
		  // and assign options and attributes to it
		  $form['back'] = [
		  '#type' => 'button',
		  '#value' => t('Back to list'),
		  '#url' => $back_url,
		  '#attributes' => array(
				  'onclick' => array('goBack()'),
				  'class' => array('btn','btn-primary','btn-lg','btn-lg-custom'),
				  ),
		  ];
		  		 
		

		  
		 
		  // Define two variables that will prompt the user with
		  // 'To enable updating or editing an entry, please login first!!' (if update entry button clicked and user not logged in)
		  // or 'To enable deleting an entry, please login first!!' (if delete entry button clicked and user not logged in)
		  // which is used as bootstrap popover and modal content simultaneously
		  $dc_s = array('To enable updating or editing an entry, please login first!!');
		  $dc_d = array('To enable deleting an entry, please login first!!');

		  // If the user is NOT logged in
		  if ($this->currentUser->isAnonymous())
		  {
			// Define Update Entry button as read only, if the user hovers over it, popover will ask user to login
			// Further, if the user clicks on the button, a modal will appear to user asking to either
			// Login, go back to list, or cancel. Modal is defined in $form['myelement'] above at top of this function
			$form['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Update Entry'),
			'#name' => 'button_1',
			'#readonly' => true,
			'#attributes' => array(
					  'class' => array('btn','btn-succes','btn-lg','btn-lg-custom'),
					  'title' => array('Login Disclaimer'),
					  'data-toggle' => array('popover'),
					  'data-content' => $dc_s,
					  'data-container' => array('body'),
					  'data-placement' => array('top'),
					  'onclick' => array('LoginFirstModal();return false;'),
					  'data-target' => array('loginModalCenter'),
					  'id' => array('#edit-submit'),
					  ),
			];

			// Define Delete Entry button as read only, if the user hovers over it, popover will ask user to login
			// Further, if the user clicks on the button, a modal will appear to user asking to either
			// Login, go back to list, or cancel. Modal is defined in $form['myelement'] above at top of this function
			$form['button_2'] = [
			'#type' => 'submit',
			'#value' => $this->t('Delete Entry'),
			'#name' => 'button_2',
			'#submit' => array('::newSubmissionHandlerOneDelete'),
			'#readonly' => true,
			'#attributes' => array(
					  'class' => array('btn btn-danger btn-lg','btn-lg-custom'),
					  'id' => array('link-btn'),
					  'title' => array('Login Disclaimer'),
					  'data-toggle' => array('popover'),
					  'data-content' => $dc_d,
					  'data-container' => array('body'),
					  'data-placement' => array('top'),
					  'onclick' => array('LoginFirstModal();return false;'),
					  'data-target' => array('loginModalCenter'),
					  ),
			];
		  }
		  else  // If the user is logged in
		  {
		  	// Define Update Entry button as an active submit button, since user is logged in,
			// user can go ahead and add entry, thus modal and popover are disabled
			$form['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Update Entry'),
			'#name' => 'button_1',
			'#readonly' => FALSE,
			'#attributes' => array(
					  'class' => array('btn','btn-succes','btn-lg','btn-lg-custom'),
					  'data-toggle' => array(''),
					  'data-content' => array(''),
					  'data-container' => array(''),
					  'data-placement' => array(''),
					  'onclick' => array('document.getElementById("db-module-updateform").submit()'),
					  'data-target' => array(''),
					  'id' => array('#edit-submit'),
					  ),
			];
			
		  	// Define Delete Entry button as an active submit button, since user is logged in,
			// user can go ahead and add entry, thus modal and popover are disabled
			$form['button_2'] = [
			'#type' => 'submit',
			'#value' => $this->t('Delete Entry'),
			'#name' => 'button_2',
			'#submit' => array('::newSubmissionHandlerOneDelete'),
			'#readonly' => FALSE,
			'#attributes' => array(
					  'class' => array('btn btn-danger btn-lg','btn-lg-custom'),
					  'id' => array('link-btn'),
					  'data-toggle' => array(''),
					  'data-content' => array(''),
					  'data-container' => array(''),
					  'data-placement' => array(''),
					  'data-target' => array(''),
					  ),
			];
		
		  }

		  // Wrap the back button around bootstrap button group class and justify them center, close element
		  $form['back']['#prefix'] = '<div class="btn-group" id="btn-group-custom" align="center">';
		  $form['back']['#suffex'] = '</div>';

		  // Close the suffix division defined at top with id = db_module_updateform,
		  $form['#suffix'] = '</div>';

		  // Attach all js/css libraries to $form['submit_2'] => element button for deleting entry, $form['submit'] => element button for updating form,
		  // and $form['back'] => element button to redirect back to list
		  // JS Libraries: dbmodule.js dbmodule_update.js, bootstrap.min.js, bootstrap.bundle.min.js, bootstrap.bundle.js,
		  // and all.js (for fontawsome)
		  // CSS Libraries: dbmodule.css, and bootstrap.bundle.min.css
		  $form['back']['#attached']['library'][] = 'db_module/bootstrap';
		  $form['back']['#attached']['library'][] = 'db_module/bootstrap.min';
		  $form['submit']['#attached']['library'][] = 'db_module/bootstrap';
		  $form['submit']['#attached']['library'][] = 'db_module/bootstrap.min';	
		  $form['button_2']['#attached']['library'][] = 'db_module/bootstrap';
		  $form['button_2']['#attached']['library'][] = 'db_module/bootstrap.min';
		  $form['back']['#attached']['library'][] = 'db_module/dbmodule_update';
		  $form['back']['#attached']['library'][] = 'db_module/dbmodule';
		  $form['submit']['#attached']['library'][] = 'db_module/dbmodule';
		  $form['submit']['#attached']['library'][] = 'db_module/dbmodule_update';	
		  $form['button_2']['#attached']['library'][] = 'db_module/dbmodule';
		  $form['button_2']['#attached']['library'][] = 'db_module/dbmodule_update';
		  $form['back']['#attached']['library'][] = 'db_module/bootstrap.bundle';
		  $form['back']['#attached']['library'][] = 'db_module/bootstrap.bundle.min';	
		  $form['submit']['#attached']['library'][] = 'db_module/bootstrap.bundle';
		  $form['button_2']['#attached']['library'][] = 'db_module/bootstrap.bundle.min';	
		  $form['submit']['#attached']['library'][] = 'db_module/bootstrap.bundle.min';	
		  $form['button_2']['#attached']['library'][] = 'db_module/bootstrap.bundle';
	 
		  // Return the form
		  return $form;
      
		}// end of else statement that is if 
  
	}

	/**
	* AJAX callback handler for the uid selected for Updating Entry.
	*
	* When the uid changes, populates the defaults from the database in the form.
	*/
	public function updateCallback(array $form, FormStateInterface $form_state, $uid='')
	{
		// Retrieve the current link in order to get the argument passed from dbModuleListForm.php.
		$currentLink =  \Drupal::request()->getRequestUri();
		$path_args = explode('=', $currentLink);

		// Gather the DB results from $form_state.
		$entries = $form_state->getValue('entries');

		// Use the specific entry for this $form_state.
		$uid = $form_state->getValue('uid');

		// if the state form 'uid' returns no employee id, grab it from post URL, request URL, get URL, or just grab it manually
		(empty($uid))? \Drupal::request()->query->get('uid'):\Drupal::request()->request->get('uid');
		(empty($uid))?  $path_args[1] :  $_GET["uid"] ;

		// Setting the #value of items here not the #default_value which wouldn't return the changed updated value
		$entry = $entries[$uid];
		foreach (['uid','first_name', 'last_name', 'gender','street_address','city','state','zip'] as $item)
		{
		  $form[$item]['#value'] = $entry->$item;
		}

		return $form;
	}

  /**
	* AJAX callback handler for the uid selected for Deleting Entry.
	*
	* When the uid changes, populates the defaults from the database in the form.
	*/
	public function deleteCallback(array $form, FormStateInterface $form_state, $uid='')
	{
		// Retreive the current link in order to get the argument passed from dbModuleListForm.php.
		$currentLink =  \Drupal::request()->getRequestUri();
		$path_args = explode('=', $currentLink);

		// Gather the DB results from $form_state.
		$entries = $form_state->getValue('entries');

		// Use the specific entry for this $form_state.
		$uid = $form_state->getValue('uid');

		// if the state form 'uid' returns no employee id, grab it from post URL, request URL, get URL, or just grab it manually
		(empty($uid))? \Drupal::request()->query->get('uid'):\Drupal::request()->request->get('uid');
		(empty($uid))?  $path_args[1] :  $_GET["uid"] ;

		// Setting the #value of items here not the #default_value which wouldn't return the changed delete value
		$entry = $entries[$uid];
		foreach (['uid','first_name', 'last_name', 'gender','street_address','city','state','zip'] as $item)
		{
		  $form[$item]['#value'] = $entry->$item;
		}

		return $form;
	}
	/**
	* {@inheritdoc}
	* Handling form submission for the update function (Updating Entry)
	*/
	public function submitForm(array &$form, FormStateInterface $form_state, $uid='')
	{
		// Gather the current user so the new record has ownership.
		$account = \Drupal::currentUser();
	
		// Retrieve the current link in order to get the argument passed from dbModuleListForm.php.
		$currentLink =  \Drupal::request()->getRequestUri();
		$path_args = explode('=', $currentLink);

		// Gather the DB results from $form_state.
		$entries = $form_state->getValue('entries');

		// Use the specific entry for this $form_state.
		$uid = $form_state->getValue('uid');

		// if the state form 'uid' returns no employee id, grab it from post URL, request URL, get URL, or just grab it manually
		(empty($uid))? \Drupal::request()->query->get('uid'):\Drupal::request()->request->get('uid');
		(empty($uid))?  $path_args[1] :  $_GET["uid"] ;

		// Setting the #value of items here not the #default_value which wouldn't return the changed updated value
		$entry = $entries[$uid];
		$entry = [
			  'uid' => $form_state->getValue('uid'),
			  'first_name' => $form_state->getValue('first_name'),
			  'last_name' => $form_state->getValue('last_name'),
			  'gender' => $form_state->getValue('gender'),
			  'street_address' => $form_state->getValue('street_address'),
			  'city' => $form_state->getValue('city'),
			  'state' => $form_state->getValue('state'),
			  'zip' => $form_state->getValue('zip'),
		];

		// Call the function defined in DbModuleRepositories.php for updating data, if update is success, then
		// display the updated entry using Drupal messages while staying on the same update page
		$count = $this->repository->update($entry,$uid);
		$this->messenger()->addMessage($this->t('Updated entry @entry (@count row updated)', [
		  '@count' => $count,
		  '@entry' => print_r($entry, TRUE),
		]));
	}
	/**
   * {@inheritdoc}
   * Handling form submission for the update function (Updating Entry)
   */
	public function newSubmissionHandlerOneDelete(array &$form, FormStateInterface $form_state, $uid='')
	{
    // Gather the current user so the new record has ownership.
		$account = \Drupal::currentUser();
	
		// Retrieve the current link in order to get the argument passed from dbModuleListForm.php.
		$currentLink =  \Drupal::request()->getRequestUri();
		$path_args = explode('=', $currentLink);

		// Gather the DB results from $form_state.
		$entries = $form_state->getValue('entries');

		// Use the specific entry for this $form_state.
		$uid = $form_state->getValue('uid');

		// if the state form 'uid' returns no employee id, grab it from post URL, request URL, get URL, or just grab it manually
		(empty($uid))? \Drupal::request()->query->get('uid'):\Drupal::request()->request->get('uid');
		(empty($uid))?  $path_args[1] :  $_GET["uid"] ;

		// Setting the #value of uid item to be deleted
		$entry = $entries[$uid];
		$entry = [
			  'uid' => $form_state->getValue('uid'),
		];

		// Call the function defined in DbModuleRepositories.php for updating data, if update is success, then
		// display the deleted entry using Drupal messages after redirecting to main list table
		$count = $this->repository->delete($entry,$uid);
		$this->messenger()->addMessage($this->t('Deleted entry @entry (@count row updated)', [
		  '@count' => $count,
		  '@entry' => print_r($entry, TRUE),
		]));
		// Redirect form after deletion
		$form_state->setRedirect('db_module.list');

	}

	/**
	* {@inheritdoc}
	* This function validates:
	* - If state selection is the not the default value (select State)
	* - Requires user to enter zip code that's 5 digits only
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
	  // Validate that zip code is 5 digits long
	  if (strlen($form_state->getValue('zip')) != 5)
	  {
		$form_state->setErrorByName('zip', $this->t('Zip code must be 5 characters long!'));
	  }

	  // Verify that that the state selected is not the default value (Select State).
	  if (($form_state->getValue('state')=='Select State'))
	  {
		$form_state->setErrorByName('state', $this->t('Please select state!'));
	  }
	}

}
