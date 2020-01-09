<?php
// Define namespace
namespace Drupal\db_module\Form;

// Define Drupal 8 dependencies/libraries:
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\db_module\DbModuleRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;




/**
 * Form to add a database entry, with all the interesting fields.
 *
 * @ingroup db_module
 */
class DbModuleAddForm implements FormInterface, ContainerInjectionInterface {

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
		  // Service is injected below.
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
	* Define this for id as db_module_addform
	*/
	public function getFormId()
	{
	  return 'db_module_addform';
	}

	/**
	* {@inheritdoc}
	* Define the main function that builds the form and pass the form as array along with form state
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
		  $form = [];
		  // Define list form division with prefix to contain form elements,
		  //closing suffix is below at the end of form function 
		  $form['#prefix'] = '<div id="update-form" class="jumbotron">';


		  // Generate random Employee Id 'uid' nu
		  $id_num = rand(10000000, 999999999);



   
   		/**
		 * Define $element that is used as markup in $form.
		 * The $element contains combination of description of the list page, and a modal. 
		 * Modal Usage:
		 *		In order to ensure proper use of user permissions, the add function or button will only operate only
		 *		for authenticated users. Thus, an exception is made that if a non-logged user attempts to mouseover or
		 *		hover over the add button, a popover will appear. Furthermore, if the non-logged in user clicks
		 *		on add, a bootstrap modal will launch prompting the user to either login, return to the list page,
		 *		or just stay within same page.
		 */
		  $element = $element = '<div class="container"><h2 class="text-center"><strong>Add Entry to Database</strong></h2><h3>Title: Custom generated database API Module<br>Author: Mojahed Ghadban<br>Package: custom module with Drupal version 8.x-1.0 compatibility</br>Module Name: db_module<br>Module Version: 1.1<br><br></h3><h3><strong>Description: </strong></h3>
		  <p>Add entry allows the user to add an entry from db_module database. In order to ensure proper use of user permissions, the add function or button will only operate only for authenticated users. Thus, an exception is made that if a non-logged user attempts to mouseover or hover over the add button, a popover will appear. Furthermore, if the non-logged in user clicks on add, a bootstrap modal will launch prompting the user to either login, return to the list page, or just stay within same page.</p>
		  <h3><strong>Implementations and Drupal 8 usage:</strong></h3>
		  <p>
		  Drupal 8 database add operation with call back function<br>
		  Drupal 8 user permissions<br>
		  Drupal 8 forms<br>
		  Custom jQuery/JavaScript<br>
		  Bootstrap popover and modal<br>
		  </p><br><br><input name="uid" id="uid" type="hidden" value="" /><input class="form-control-lg" name="textcheck" id="textcheck" type="text" value="" hidden /><br></div>
		  <div class="modal fade" id="loginModalCenter" tabindex="-1" role="dialog" aria-labelledby="loginModalCenterTitle" aria-hidden="true">
		  <div class="modal-dialog modal-dialog-centered" role="dialog-centered">
		  <div class="modal-content">
			<div class="modal-header">
			<h2 class="modal-title" id="loginModalLongTitle">Please Login First!</h2>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				  <span aria-hidden="true">&times;</span>
			</button>
			</div>
			<div class="modal-body">This operation requirs authenticated users to login, please choose from the below options</div>
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

		  // Attach all js/css libraries to $form['myelement'] => for above $element, $form['add'] => element button to add entry,
		  // and $form['back'] => element button to redirect to main list table
		  // JS Libraries: dbmodule.js dbmodule_update.js, bootstrap.min.js, bootstrap.bundle.min.js, bootstrap.bundle.js,
		  // and all.js (for fontawsome)
		  // CSS Libraries: dbmodule.css, and bootstrap.bundle.min.css
		  $form['myelement']['#attached']['library'][] = 'db_module/dbmodule'; 
		  $form['myelement']['#attached']['library'][] = 'db_module/dbmodule_update';
		  $form['myelement']['#attached']['library'][] = 'db_module/bootstrap';
		  $form['myelement']['#attached']['library'][] = 'db_module/bootstrap.min';
		  $form['myelement']['#attached']['library'][] = 'db_module/bootstrap.bundle';	
		  $form['myelement']['#attached']['library'][] = 'db_module/bootstrap.bundle.min';
		  $form['add']['#attached']['library'][] = 'db_module/dbmodule'; 
		  $form['add']['#attached']['library'][] = 'db_module/dbmodule_update';
		  $form['add']['#attached']['library'][] = 'db_module/bootstrap';
		  $form['add']['#attached']['library'][] = 'db_module/bootstrap.min';
		  $form['add']['back']['#attached']['library'][] = 'db_module/bootstrap.bundle';	
		  $form['add']['back']['#attached']['library'][] = 'db_module/bootstrap.bundle.min';
		  $form['add']['back']['#attached']['library'][] = 'db_module/dbmodule'; 
		  $form['add']['back']['#attached']['library'][] = 'db_module/dbmodule_update';
		  $form['add']['back']['#attached']['library'][] = 'db_module/bootstrap';
		  $form['add']['back']['#attached']['library'][] = 'db_module/bootstrap.min';
		  $form['add']['back']['#attached']['library'][] = 'db_module/bootstrap.bundle';	
		  $form['add']['back']['#attached']['library'][] = 'db_module/bootstrap.bundle.min';

		  // Define element add as field set and assign options and attributes to it
		  $form['add'] = [
			'#type' => 'fieldset',
			'#title' => $this->t('Add a employee entry'),
			'#attributes' => array(
				  'class' => array('form-group jumbotron'),
				  'data-role' => array('controlgroup'),
				  'data-type' => array('horizontal'),
				),
		  ];

		  // Define element uid as field set and assign options and attributes to it
		  $form['add']['uid'] = [
			'#type' => 'textfield',
			'#value' => $id_num,
			'#title' => $this->t('Autmotically Generated Employee Id'),
			'#disabled' => TRUE,
			'#attributes' => array(
				  'class' => array('form-control'),
				 ),
		  ];

		  // Define element First Name as input field and assign options and attributes to it
		  $form['add']['first_name'] = [
			'#type' => 'textfield',
			'#title' => $this->t('First Name'),
			'#required' => TRUE,
			'#attributes' => array(
				  'class' => array('form-control'),
				 ),
		  ];

		  // Define element Last Name as input field and assign options and attributes to it
		  $form['add']['last_name'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Last name'),
			'#required' => TRUE,
			'#attributes' => array(
				  'class' => array('form-control'),
				 ),
		  ];

		  // Define element Gender as list and assign options and attributes to it
		  $form['add']['gender'] = [
			'#type' => 'select',
			'#title' => t('Gender'),
			'#options' => [
			'Female' => $this->t('Female'),
			'Male' => $this->t('Male'),
			],
			'#default_value' => 'Female',
			'#attributes' => array(
				  'class' => array('custom-select'),
			  ),
		  ];

		  // Define element Street Address as input field and assign options and attributes to it
		  $form['add']['street_address'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Street Address'),
			'#required' => TRUE,
			'#attributes' => array(
				  'class' => array('form-control'),
				 ),
		  ];

		  // Define element City as input field and assign options and attributes to it
		  $form['add']['city'] = [
			'#type' => 'textfield',
			'#title' => $this->t('City'),
			'#required' => TRUE,
			'#attributes' => array(
				  'class' => array('form-control'),
				 ),
		  ];

		  // Define element State as list and assign options and attributes to it
		  // with regards that if user kept the default value ('Select State') validation
		  // occurs prompting proper selection of state
		  $form['add']['state'] = [
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
		  '#default_value' => 'Select State',
		  '#attributes' => array(
				  'class' => array('custom-select'),
				 ),
		  ];

		  // Define element Zip code as input field and assign options and attributes to it
		  $form['add']['zip'] = [
			'#type' => 'textfield',
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
		  $form['add']['back'] = [
		  '#type' => 'button',
		  '#value' => t('Back to list'),
		  '#url' => $back_url,
		  '#attributes' => array(
					'onclick' => array('window.location.href="'.$back_url.'";return false;'),
					'class' => array('btn','btn-primary','btn-lg','btn-lg-custom'),
				  ),
		  ];

		  // Define variable that will be used to prompt the user with
		  //"To enable adding an entry, please login first!!"
		  // which is used as bootstrap popover and modal content simultaneously
		  $dc_a = array('To enable adding an entry, please login first!!');

		  // If the user is NOT logged in
		  if ($this->currentUser->isAnonymous())
		  {
			// Define Add Entry button as read only, if the user hovers over it, popover will ask user to login
			// Further, if the user clicks on the button, a modal will appear to user asking to either
			// Login, go back to list, or cancel. Modal is defined in $form['myelement'] above at top of this function
			$form['add']['submit'] = [
				'#type' => 'submit',
				'#value' => $this->t('Add Entry'),
				'#name' => 'button_1',
				'#readonly' => true,
				'#attributes' => array(
						'class' => array('btn','btn-succes','btn-lg','btn-lg-custom'),
						'title' => array('Login Disclaimer'),
						'data-toggle' => array('popover'),
						'data-content' => $dc_a,
						'data-container' => array('body'),
						'data-placement' => array('top'),
						'onclick' => array('LoginFirstModal();return false;'),
						'data-target' => array('loginModalCenter'),
						'id' => array('#edit-submit'),
						),
			];
		  }
		  else  // If the user is logged in
		  {
		  	// Define Add Entry button as an active submit button, since user is logged in,
			// user can go ahead and add entry, thus modal and popover are disabled
			$form['add']['submit'] = [
				'#type' => 'submit',
				'#value' => $this->t('Add Entry'),
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
		  }

		  // Wrap the back button around bootstrap button group class and justify them center, close element 
		  $form['add']['back']['#prefix'] = '<div class="btn-group" id="btn-group-custom" align="center">';
		  $form['add']['back']['#suffex'] = '</div>';

		  // Close the suffix division defined at top with id = db_module_addform,
		  $form['#suffex'] = '</div>';

		  // Return the form
		  return $form;
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
		$form_state->setErrorByName('zip', $this->t('Zip Code must be 5 characters long!'));
	  }
	  // Verify that that the state selected is not the default value (Select State).
	  if (($form_state->getValue('state')=='Select State'))
	  {
		$form_state->setErrorByName('state', $this->t('Please select state!'));
	  }
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state)
	{
		// Gather the current user so the new record has ownership.
		$account = $this->currentUser;
		// Save the submitted entry.
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

		// Call the function defined in DbModuleRepositories.php for inserting data, if insert is success, then
		// display the entry using Drupal messages as added after redirection main table list
		$return = $this->repository->insert($entry);
		if ($return)
		{
			$this->messenger()->addMessage($this->t('Created entry @entry', ['@entry' => print_r($entry, TRUE)]));
		}
		// Redirect form after adding entry to main list table
		$form_state->setRedirect('db_module.list');
	}

}
