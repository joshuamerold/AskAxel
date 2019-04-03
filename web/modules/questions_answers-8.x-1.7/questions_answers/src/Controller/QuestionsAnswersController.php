<?php

namespace Drupal\questions_answers\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\system\SystemManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creating the controller for Questions and Answers.
 */
class QuestionsAnswersController extends ControllerBase {

  /**
   * System Manager Service.
   *
   * @var \Drupal\system\SystemManager
   */
  protected $systemManager;

  /**
   * Constructs a new QuestionsAnswersController.
   *
   * @param \Drupal\system\SystemManager $systemManager
   *   System manager service.
   */
  public function __construct(SystemManager $systemManager) {
    $this->systemManager = $systemManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('system.manager')
    );
  }

  /**
   * Display the Questions and Answers administration menu.
   *
   * @return array
   *   Render array for this page content.
   */
  public function adminMenuBlockPage() {
    return $this->systemManager->getBlockContents();
  }

}
