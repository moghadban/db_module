<?php
// Define namespace
namespace Drupal\db_module\Form;

// Define Drupal 8 dependencies/libraries:
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\db_module\DbModuleRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Component\Render\FormattableMarkup;
/**
 * Sample UI to update a record.
 *
 * @ingroup db_module
 */
class DbModuleListForm extends FormBase {

	/**
	* Database repository service.
	*
	* @var \Drupal\db_module\DbModuleRepository
	*/
	protected $repository;

	/**
	* {@inheritdoc}
	* Define this for id as db_module_list_form
	*/
	public function getFormId() {
	return 'db_module_list_form';
	}

	/**
	* {@inheritdoc}
	*
	* ContainerInjectionInterface pattern is used to inject the
	* current user and to get the string_translation service.
	*/
	public static function create(ContainerInterface $container) {
	  $form = new static($container->get('db_module.repository'));
	  $form->setStringTranslation($container->get('string_translation'));
	  $form->setMessenger($container->get('messenger'));
	  return $form;
	}

	/**
	* Construct the new form object.
	*/
	public function __construct(DbModuleRepository $repository) {
	  $this->repository = $repository;
	}

	/**
	* Define the main function buildForm that creates the form and pass the form as array along with form state
	*/
	public function buildForm(array $form, FormStateInterface $form_state, $input='') {
		// Define variable $text and assign it to null, it will be used for color highlighting search results
		$text = '';

		// Define variable $input, which posts value of searched text and passes it as parameter in url
		// If value is not set then assign it to null
		$input =  isset($_POST['textcheck']) ? $_POST['textcheck'] : '' ;

		// Define variable $uid which posts value of 'uid' => 'Employee Id' number and redirects the user
		// to view, update, or delete single entry
		// If value is not set then assign it to null
		$uid =  isset($_POST['uid']) ? $_POST['uid'] : '' ;

		// Get current url
		$currentLink =  \Drupal::request()->getRequestUri();
		// Replace the end of url ('/list') with ('/add') to achieve proper routing of adding an entry
		$add_url =  str_replace('list', 'add', $currentLink);

		// Define list form division with prefix and suffix to contain form elements
		$form['#prefix'] = '<div id="list-form" class="form-group" >';
		$form['#suffix'] = '</div>';
 

		/**
		 * Define $element that is used as markup in $form.
		 * The $element contains combination of description of the list page, and other form groups or input groups
		 * The main form/input elements are:
		 * Input field textbox to write a search query
		 * Interactive JavaScript/jQuery label to assist user
		 * Search button that searches table list for desired query
		 * Clear button that clears table list form
		 * Add new entry button that once click, redirects user to add entry form
		 *
		 */
		$element = '<div class="container form-group"><h2 class="text-center"><strong>Custom Generated Databse List</strong></h2><h3>Title: Custom generated database API Module<br>Author: Mojahed Ghadban<br>Package: custom module with Drupal version 8.x-1.0 compatibility</br>Module Name: db_module<br>Module Version: 1.1<br><br></h3><h3><strong>Description:</strong></h3><p>The list below demonstrates connecting to db_module database and displaying them in table list with sort, pagers, and custom search. Each entry is click-able and routes user to the update page, which allows the user to edit or delete an entry. In addition, loading entries uses Drupal 8 jQuery/Ajax API.<br>The sort and pagers implementations are extended from Drupal core. The search however is custom coded, yet it provides accurate results that are highlighted. The user can perform search while keeping pagers intact and sort operational.</p>
		<h3><strong>Implementations and Drupal 8 usage:</strong></h3>
		<p>
		- Drupal 8 database connect and display in table form functions and methods<br>
		- Drupal 8 forms<br>
		- Drupal 8 jQuery/Ajax<br>
		- Drupal 8 Sort<br>
		- Drupal 8 Pagers<br>
		- Custom jQuery/JavaScript<br>
		</p>
		<br><br><label for="textcheck" id="checktext_label" class="small_txt_loading_dot" for="textcheck">Search List</label><div class="input-group"><input class="form-control-lg" name="textcheck" id="textcheck" type="text" value="'.$input.'" placeholder="Search..." size="40" aria-describedby="button-addon1" /><div class="input-group-append" id="button-addon1">
		<button id="search_text" data-href="'.$currentLink.'" class="search_text btn btn-info btn-lg"><span class="fa fa-search"></span> Search </button>
		<button id="clear_text" class="clear_text btn btn-dark btn-lg"><span class="fa fa-eraser"></span>  Clear </button>
		<a type="button" class="btn btn-primary btn-lg" href="'.$add_url.'" ><span class="fas fa-plus-square"></span>  Add New Entry </a>
		</div><input name="uid" id="uid" type="hidden" value="'.$uid.'" /></div></div><br><br><br>
		';

		// Assign $element to $form['myelement'] as an array with key markup
		// Declare allowed tags to allow most of not all HTML elements
		$form['myelement'] = [
		'#markup' => $element,
		'#allowed_tags' => ['!--...--','!DOCTYPE','a','abbr','acronym','address','applet','area','article','aside','audio','b','base','basefont','bdi','bdo','big','blockquote','body','br','button','canvas','caption','center','cite','code','col','colgroup','data','datalist','dd','del','details','dfn','dialog','dir','div','dl','dt','em','embed','fieldset','figcaption','figure','font','footer','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','header','hr','html','i','iframe','img','input','ins','kbd','label','legend','li','link','main','map','mark','meta','meter','nav','noframes','noscript','object','ol','optgroup','option','output','p','param','picture','pre','progress','q','rp','rt','ruby','s','samp','script','section','select','small','source','span','strike','strong','style','sub','summary','sup','svg','table','tbody','td','template','textarea','tfoot','th','thead','time','title','tr','track','tt','u','ul','var','video','wbr'],
		];

		// Attache all js/css libraries to $form['myelement']
		// JS Libraries: dbmodule.js dbmodule_update.js, bootstrap.min.js, bootstrap.bundle.min.js, bootstrap.bundle.js,
		// and all.js (for fontawsome)
		// CSS Libraries: dbmodule.css, and bootstrap.bundle.min.css
		$form['myelement']['#attached']['library'][] = 'db_module/dbmodule'; 
		$form['myelement']['#attached']['library'][] = 'db_module/all'; 
		$form['myelement']['#attached']['library'][] = 'db_module/bootstrap';
		$form['myelement']['#attached']['library'][] = 'db_module/bootstrap.min';
		$form['myelement']['#attached']['library'][] = 'db_module/bootstrap.bundle';	
		$form['myelement']['#attached']['library'][] = 'db_module/bootstrap.bundle.min';
		
		// if user didn't enter a search text and post url parameter is null
		if( empty($_POST["textcheck"]) ) 
		{
			// Load entries from DbModuleRepository.php
			$entries = $this->repository->load();

			// If there are no entries in db_module db
			if (empty($entries)) {
			  // Display message in table that "No entries exist in the table db_module table"
			  // and assign it to form markup, then return the form
			  $form['message'] = [
			  '#markup' => '<h2 align="center" style="padding: 20px 5px;">No entries exist in the table db_module table.</h2>',
			  ];
			return $form;
			}	
			else  // else If there are entries in db_module database
			{
			 // Begin processing form elements
			 // Define table headers, please notice that the title of these headers is the same as table name due to
			 // saving table headers and cells with rendered html element, which causes the url to use the html rendered headers
			 // For instance, the header $this->t('Employee Id') will cause the url to have space encoded as '%20'
			 // which leads to problem in url decoding especially when we are passing custom parameters as text check
			 // Handling the text has been fixed in client side JavaScript/jQuery in the file dbmodule.js, thus it will appear
			 // normally after using jQuery css injection. Please refer to dbmodule. js for more information.
	
	
			  $header = [
						  ['data' => $this->t('uid'), 'field' => 't.uid','sort' => ''],
						  ['data' => $this->t('first_name'), 'field' => 't.first_name','sort' => ''],
						  ['data' => $this->t('last_name'), 'field' => 't.last_name','sort' => ''],
						  ['data' => $this->t('gender'), 'field' => 't.gender','sort' => ''],
						  ['data' => $this->t('street_address'), 'field' => 't.street_address','sort' => ''],
						  ['data' => $this->t('city'), 'field' => 't.city','sort' => ''],
						  ['data' => $this->t('state'), 'field' => 't.state','sort' => ''],
						  ['data' => $this->t('zip'), 'field' => 't.zip','sort' => ''],
						];
  
			 
			  // In order to process the href link for each uid 'Employee Id' column, which shall direct
			  // the user to either edit or delete an entry, a link parsing is needed
			  // First get the current link
			  $currentLink =  \Drupal::request()->getRequestUri();
			  // Replace ('/list') with null (empty string)
			  $url =   str_replace('list', '', $currentLink);
			  // Use reg-ex to make sure any additional parameters are removed, so anything returned from url
			  // containing the char '?' and anything after hence fourth, remove it and replace it with empty string
			  $urlc = preg_replace('/\?[^?]*$/', '', $url);

			  // Define empty array $keyed_entries to store all returned values into to be used in the form later
			  $keyed_entries = [];
			  // Define rows to store arrays which hold the value of the returned rows from db_module to be stored
			  // later in the table list declared below
			  $rows = [];

			  // Loop through each entry returned from db_module
			  foreach ($entries as $entry)
			  {

				  // store the employee id or 'uid' in $uid so we can use it to redirect to update the entry
				  $uid=$entry->uid;

				  // Define $link which stores the parsed url and adds 'update?uid='.$uid; to it
				  $link =  $urlc.'update?uid='.$uid; 

				  // store each returned row in html formatted array, turning uid into linked button
				  // so the user can browse to each entry accordingly
				  $rows[] =
				  array(
					array('data' => new FormattableMarkup('<button type="button" class="alink btn btn-outline-dark btn-sm" data-href=":link" onclick="updateForm();return false;">@uid</button>', 
							[':link' => $link, 
		
							'@uid' => $uid])
							),
					array('data' => new FormattableMarkup('<span>@first_name</span>', 
							[ '@first_name' => $entry->first_name])
							),
					array('data' => new FormattableMarkup('<span>@last_name</span>', 
							['@last_name' => $entry->last_name])
							),
					array('data' => new FormattableMarkup('<span>@gender</span>', 
							['@gender' => $entry->gender])
							),
					array('data' => new FormattableMarkup('<span>@street_address</span>', 
							['@street_address' => $entry->street_address ])
							),
					array('data' => new FormattableMarkup('<span>@city</span>', 
							['@city' => $entry->city])
							),
					array('data' => new FormattableMarkup('<span>@state</span>', 
							['@state' => $entry->state])
							),
					array('data' => new FormattableMarkup('<span>@zip</span>', 
							['@zip' => $entry->zip])
							),
					);		 

			  }

			  // Grab the uid.
			  $uid = $form_state->getValue('uid');
			  // Use the uid to set the default entry for updating.
			  //$default_entry = $keyed_entries[$uid] ;

			  // Save the entries into the $form_state. We do this so the AJAX callback
			  // doesn't need to repeat the query.
			  $form_state->setValue('entries', $keyed_entries); 

			  // Display sortable table by adding $header and $rows, and defining other options
			   $form['tablesort_table']  = [
					'#type' => 'tableselect',
					'#header' => $header,
					'#options' => $rows,
					'#empty' => t('No Entries found'),
					'#attributes' => array(
					'class' => array('table','table-hover','table-bordered'),
					),
			  ];

			  // Display pagination at the bottom of the form and return the form
			  $form['pager'] = [
				  '#type' => 'pager',
			  ];
  
			  return $form;
			 // End of else statement that if there are entries in db_module
			} 
		// End of else statement that if search query is empty
		}
		else	// Else if search query is not empty
		{
			// Load entries from DbModuleRepository.php, pass the entered search query saved in $input and passed by post in url
			$entries = $this->repository->loadSearch($input);

			// If there are no entries in db_module db
			if (empty($entries)) {
			  // Display message in table that "No entries exist in the table db_module table"
			  // and assign it to form markup, then return the form
			  $form['message'] = [
			  '#markup' => '<h2 align="center" style="padding: 20px 5px;">No entries exist in the table db_module table.</h2>',
			  ];
			return $form;
			}
			else  // else If there are entries in db_module database
			{
			 // Begin processing form elements
			 // Define table headers, please notice that the title of these headers is the same as table name due to
			 // saving table headers and cells with rendered html element, which causes the url to use the html rendered headers
			 // For instance, the header $this->t('Employee Id') will cause the url to have space encoded as '%20'
			 // which leads to problem in url decoding especially when we are passing custom parameters as text check
			 // Handling the text has been fixed in client side JavaScript/jQuery in the file dbmodule.js, thus it will appear
			 // normally after using jQuery css injection. Please refere to dbmodule. js for more information.
	
	
			  $header = [
						  ['data' => $this->t('uid'), 'field' => 't.uid','sort' => ''],
						  ['data' => $this->t('first_name'), 'field' => 't.first_name','sort' => ''],
						  ['data' => $this->t('last_name'), 'field' => 't.last_name','sort' => ''],
						  ['data' => $this->t('gender'), 'field' => 't.gender','sort' => ''],
						  ['data' => $this->t('street_address'), 'field' => 't.street_address','sort' => ''],
						  ['data' => $this->t('city'), 'field' => 't.city','sort' => ''],
						  ['data' => $this->t('state'), 'field' => 't.state','sort' => ''],
						  ['data' => $this->t('zip'), 'field' => 't.zip','sort' => ''],
						];
  
			 
			  // In order to process the href link for each uid 'Employee Id' column, which shall direct
			  // the user to either edit or delete an entry, a link parsing is needed
			  // First get the current link
			  $currentLink =  \Drupal::request()->getRequestUri();
			  // Replace ('/list') with null (empty string)
			  $url =   str_replace('list', '', $currentLink);
			  // Use reg-ex to make sure any additional parameters are removed, so anything returned from url
			  // containing the char '?' and anything after hence fourth, remove it and replace it with empty string
			  $urlc = preg_replace('/\?[^?]*$/', '', $url);

			  // Define class highlight to be added to results returned from search
			  $class = 'highlight';

			  // Define empty array $keyed_entries to store all returned values into to be used in the form later
			  $keyed_entries = [];
			  // Define rows to store arrays which hold the value of the returned rows from db_module to be stored
			  // later in the table list declared below
			  $rows = [];

			  // Loop through each entry returned from db_module
			  foreach ($entries as $entry)
			  {

				  // store the employee id or 'uid' in $uid so we can use it to redirect to update the entry
				  $uid=$entry->uid;

				  // Define $link which stores the parsed url and adds 'update?uid='.$uid; to it
				  $link =  $urlc.'update?uid='.$uid; 

				  // store each returned row in html formatted array, turning uid into linked button
				  // so the user can browse to each entry accordingly
				  // Call function highlight in order to highlight returned search results
				  $rows[] = 		array(
	

					  array('data' => new FormattableMarkup('<button type="button" class="alink btn btn-outline-dark btn-sm" data-href=":link" onclick="updateForm();return false;">@uid</button>', 
					  [':link' => $link, 
		
					  '@uid' => $uid])
					  ),
	
					  array('data' => new FormattableMarkup($this->highlight($entry->first_name, $input, $class ), 
					  [ '@first_name' => $entry->first_name])
					  ),
					  array('data' => new FormattableMarkup($this->highlight($entry->last_name, $input, $class ), 
					  ['@last_name' => $entry->last_name])
					  ),
					  array('data' => new FormattableMarkup($this->highlight($entry->gender, $input, $class ), 
					  ['@gender' => $entry->gender])
					  ),
					  array('data' => new FormattableMarkup($this->highlight($entry->street_address, $input, $class ), 
					  ['@street_address' => $entry->street_address ])
					  ),
					  array('data' => new FormattableMarkup($this->highlight($entry->city, $input, $class ), 
					  ['@city' => $entry->city])
					  ),
					  array('data' => new FormattableMarkup($this->highlight($entry->state, $input, $class ), 
					  ['@state' => $entry->state])
					  ),
					  array('data' => new FormattableMarkup($this->highlight($entry->zip, $input, $class ), 
					  ['@zip' => $entry->zip])
					  ),

					  );		 

			  }

			  // Grab the uid.
			  $uid = $form_state->getValue('uid');

			  // Save the entries into the $form_state. We do this so the AJAX callback
			  // doesn't need to repeat the query.
			  $form_state->setValue('entries', $keyed_entries); 

			  // Display sortable table by adding $header and $rows, and defining other options
			   $form['tablesort_table']  = [
					'#type' => 'tableselect',
					'#header' => $header,
					'#options' => $rows,
					'#empty' => t('No Entries found'),
					'#attributes' => array(
					'class' => array('table','table-hover','table-bordered'),
					),
			  ];

			  // Display pagination at the bottom of the form and return the form
			  $form['pager'] = [
				  '#type' => 'pager',
			  ];
  
			  return $form;
			} // End of else statement that if there are entries in db_module
		
		}// End of else statement that if search query is empty
	
   
	}// End of buildForm function
	/**
   * This function returns matching search result highlighted to be used 
   * It inserts spanning with class highlight defined in buildForm function
   */
	public function highlight($text, $keyword, $class )
	{
	  if (preg_match('/^(["\']).*\1$/m', $keyword))
	  {
		$keyword = $this->stripQuotes($keyword);
	  }
	  $keywords = explode(' ',$keyword);
	  $t =  preg_replace('/'.implode('|', $keywords).'/i', '<span class="'.$class.'">\\0</span>', $text);
	
	  return $t;
		
	}

	/**
	* {@inheritdoc}
	* When form submits, redirect to same list page
	*/
	public function submitForm(array &$form, FormStateInterface $form_state)
	{
	  //$form_state->setRedirect('db_module.list');
	}

}
