<?php
// Define namespace
namespace Drupal\db_module;

// Define Drupal 8 dependencies/libraries:
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Repository for database-related helper methods for db_module.
 *
 * This repository is a service named 'db_module.repository', the service is defined in db_module/db_module.services.yml.
 *
 * For Specific database queries, 'repositories' can customize as a service to group these queries by connecting to
 * database and performing variety of tasks
 *
 * This repository demonstrates basic CRUD behaviors
 *
 * @ingroup db_module
 */
class DbModuleRepository {

	use MessengerTrait;
	use StringTranslationTrait;

	/**
	* The database connection.
	*
	* @var \Drupal\Core\Database\Connection
	*/
	protected $connection;

	/**
	* Construct a repository object.
	*
	* @param \Drupal\Core\Database\Connection $connection
	*   The database connection.
	* @param \Drupal\Core\StringTranslation\TranslationInterface $translation
	*   The translation service.
	* @param \Drupal\Core\Messenger\MessengerInterface $messenger
	*   The messenger service.
	*/
	public function __construct(Connection $connection, TranslationInterface $translation, MessengerInterface $messenger) {
    $this->connection = $connection;
    $this->setStringTranslation($translation);
    $this->setMessenger($messenger);
	}

	/**
	* Save an entry in the database.
	*
	*
	* @param array $entry
	*   An array containing all the fields of the database record.
	*
	* @return int
	*   The number of updated rows.
	*
	* @throws \Exception
	*   When the database insert fails.
	*/
	public function insert(array $entry) {
		try {
		  $return_value = $this->connection->insert('db_module')
			->fields($entry)
			->execute();
		}
		catch (\Exception $e) {
		  $this->messenger()->addMessage(t('Insert failed. Message = %message', [
			'%message' => $e->getMessage(),
		  ]), 'error');
		}
		return $return_value ?? NULL;
	}

	/**
	* Update an entry in the database.
	*
	* param string $uid
	*	variable passed from DbModuleListForm, which is used to view, edit, or update an entry
	*
	* @param array $entry
	*   An array containing all the fields of the item to be updated.
	*
	* @return int
	*   The number of updated rows.
	*/
	public function update(array $entry,$uid) {
		try {
		  $count = $this->connection->update('db_module')
			->fields($entry)
			->condition('uid', $uid)
			->execute();
		}
		catch (\Exception $e) {
		  $this->messenger()->addMessage(t('Update failed. Message = %message, query= %query', [
			'%message' => $e->getMessage(),
			'%query' => $e->query_string,
		  ]
		  ), 'error');
		}
		return $count ?? 0;
	}

	/**
	* Delete an entry from the database.
	*
	* param string $uid
	*	variable passed from DbModuleListForm, which is used to delete an entry
	*
	* @param array $entry
	*   An array containing at least the person identifier 'uid' element of the
	*   entry to delete.
	*
	* @see Drupal\Core\Database\Connection::delete()
	*/
	public function delete(array $entry, $uid) {
		$this->connection->delete('db_module')
		  ->condition('uid', $uid)
		  ->execute();
	}

	/**
	* Read from the database using a filter array.
	*
	* The standard function to perform reads for static queries is
	* Connection::query().
	*
	* Connection::query() uses an SQL query with placeholders and arguments as
	* parameters.
	*
	*
	* @param array $entry
	*   An array containing all the fields used to search the entries in the
	*   table.
	*
	* @return object
	*   An object containing the loaded entries if found.
	*
	* @see DBModuleListForm.php which calls this method upon viewing table list of db_module
	*
	* @see Drupal\Core\Database\Connection::select()
	*/
	public function load(array $entry = []){
		  $header =	[
					['data' => $this->t('uid'), 'field' => 't.uid','sort' => ''],
					['data' => $this->t('first_name'), 'field' => 't.first_name','sort' => ''],
					['data' => $this->t('last_name'), 'field' => 't.last_name','sort' => ''],
					['data' => $this->t('gender'), 'field' => 't.gender','sort' => ''],
					['data' => $this->t('street_address'), 'field' => 't.street_address','sort' => ''],
					['data' => $this->t('city'), 'field' => 't.city','sort' => ''],
					['data' => $this->t('state'), 'field' => 't.state','sort' => ''],
					['data' => $this->t('zip'), 'field' => 't.zip','sort' => ''],
					];

		  // Read all the fields from the db_module table.
		  $select = $this->connection->select('db_module', 't')->fields('t', [ 'uid','first_name','last_name','gender','street_address','city','state','zip']);
	
		  // Sort Table By ID, extend sort from core
		  $table_sort = $select->extend('Drupal\Core\Database\Query\TableSortExtender')->orderByHeader($header);
		  // Add Pagination and set it to 20, extend pager from core
		  $pager = $table_sort->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
		  // Fetch Associated Column
		  return $pager->execute();
    }

	/**
	* Read from the database using a filter array, and accept search parameter from url
	*
	* The standard function to perform reads for static queries is
	* Connection::query().
	*
	* Connection::query() uses an SQL query with placeholders and arguments as
	* parameters.
	* param string $text
	*	String passed from DbModuleListForm, which is used to perform custom db search operation
	*
	* @param array $entry
	*   An array containing all the fields used to search the entries in the
	*   table.
	*
	* @return object
	*   An object containing the loaded entries while checking against each file
	*	the passed search word parameter $text, if object is found that is
	*
	* @see DBModuleListForm.php which calls this method upon searching query
	*
	* @see Drupal\Core\Database\Connection::select()
	*/
	public function loadSearch($text){
 
		  $header =	[
					['data' => $this->t('uid'), 'field' => 't.uid','sort' => ''],
					['data' => $this->t('first_name'), 'field' => 't.first_name','sort' => ''],
					['data' => $this->t('last_name'), 'field' => 't.last_name','sort' => ''],
					['data' => $this->t('gender'), 'field' => 't.gender','sort' => ''],
					['data' => $this->t('street_address'), 'field' => 't.street_address','sort' => ''],
					['data' => $this->t('city'), 'field' => 't.city','sort' => ''],
					['data' => $this->t('state'), 'field' => 't.state','sort' => ''],
					['data' => $this->t('zip'), 'field' => 't.zip','sort' => ''],
					];
		  $database = \Drupal::database();
			// Read all the fields from the db_module table and perform custom search with
			// Checking each field with LIKE expression.
		  $select = $this->connection->select('db_module', 't')
		->fields('t', [ 'uid','first_name','last_name','gender','street_address','city','state','zip']);
		  $orGroup = $select->orConditionGroup()->condition('pid','%'.$database->escapeLike($text).'%','LIKE')
		  ->condition('uid','%'.$database->escapeLike($text).'%','LIKE')
		  ->condition('first_name','%'.$database->escapeLike($text).'%','LIKE')
		  ->condition('last_name','%'.$database->escapeLike($text).'%','LIKE')
		  ->condition('gender','%'.$database->escapeLike($text).'%','LIKE')
		  ->condition('street_address','%'.$database->escapeLike($text).'%','LIKE')
		  ->condition('city','%'.$database->escapeLike($text).'%','LIKE')
		  ->condition('state','%'.$database->escapeLike($text).'%','LIKE')
		  ->condition('zip','%'.$database->escapeLike($text).'%','LIKE');
		  $select->condition($orGroup);
		  $table_sort = $select->extend('Drupal\Core\Database\Query\TableSortExtender')->orderByHeader($header);
		  // Add Pagination and set it to 20
		  $pager = $table_sort->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
		  return $pager->execute()->fetchAll();	
	}
}
