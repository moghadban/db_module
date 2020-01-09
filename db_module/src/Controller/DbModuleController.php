<?php
// Define namespace
namespace Drupal\db_module\Controller;

// Define Drupal 8 dependencies/libraries:
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\db_module\DbModuleRepository;
use Drupal\db_module\Form\DbModuleListForm;



/**
 * Controller for DbModuleController.
 *
 * @ingroup db_module
 */
class DbModuleController extends ControllerBase {

	/**
	* the database repository service.
	*
	* @var \Drupal\db_module\DbModuleRepository
	*/
	protected $repository;

	/**
	* {@inheritdoc}
	*
	* ContainerInjectionInterface pattern is used to inject the
	* current user and to get the string_translation service.
	*/
	public static function create(ContainerInterface $container)
	{
		$controller = new static($container->get('db_module.repository'));
		$controller->setStringTranslation($container->get('string_translation'));
		return $controller;
	}

	  /**
	   * Construct a new controller.
	   *
	   * @param \Drupal\db_module\DbModuleRepository $repository
	   *   The repository service.
	   */
	  public function __construct(DbModuleRepository $repository)
	  {
		$this->repository = $repository;
	  }
	  /**
	   * Since this getDBModuleDescription() is main menu, call
	   * the below function entryList
	   */
	  public function getDBModuleDescription()
	  {

		return $this->entryList();
	  }
	  /**
	   * Then Redirect to the main table list
	   */
	  public function entryList()
	  {
			// Redirect to main list		
			return $this->redirect('db_module.list');
	  }
}
