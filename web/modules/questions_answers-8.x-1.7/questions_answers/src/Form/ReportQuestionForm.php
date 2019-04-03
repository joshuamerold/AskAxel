<?php

namespace Drupal\questions_answers\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\questions_answers\Entity\Question;

/**
 * Form for reporting a question about an entity.
 */
class ReportQuestionForm extends FormBase {

  /**
   * Drupal current user service container.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal session manager service container.
   *
   * @var Drupal\Core\Session\SessionManagerInterface
   */
  protected $sessionManager;

  /**
   * Question being reported.
   *
   * @var Drupal\questions_answers\Entity\Question
   */
  protected $question;

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountProxyInterface $current_user, SessionManagerInterface $session_manager) {
    $this->currentUser = $current_user;
    $this->sessionManager = $session_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('session_manager')
    );
  }

  /**
   * Sets the question being used to build this form.
   *
   * @param Drupal\questions_answers\Entity\Question $question
   *   The question we are building the form for.
   */
  public function setQuestion(Question $question) {
    $this->question = $question;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    if (!isset($this->question)) {
      throw new \Exception('Question not found. setQuestion() should be called before form instantiation.');
    }
    return 'questions_answers_report_question_form_' . $this->question->id();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Start the user session if we need to.
    if ($this->currentUser->isAnonymous() && !isset($_SESSION['session_started'])) {
      $_SESSION['session_started'] = TRUE;
      $this->sessionManager->start();
    }

    // Wrap the form for callback.
    $form['#prefix'] = '<div id="report_question_button_' . $this->question->id() . '">';
    $form['#suffix'] = '</div>';

    // Prevent caching.
    $form['#cache'] = ['max-age' => 0];

    // If the user has submitted a value.
    if (!empty($form_state->getValue('report'))) {
      $form['message'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['messages messages--status helpful-message'],
        ],
        '#children' => $this->t('You have successfully reported this question.'),
      ];

      // Add the report.
      if ($this->currentUser->isAnonymous()) {
        // Store a session ID for anonymous.
        $this->question->addReport($this->sessionManager->getId());
      }
      else {
        $this->question->addReport($this->currentUser->id());
      }
    }
    else {
      // Get the current reports.
      $reports = $this->question->getReports();

      // Check if this form was just reported.
      if ($this->currentUser->isAnonymous() && in_array($this->sessionManager->getId(), $reports) || in_array($this->currentUser->id(), $reports)) {
        $form['report'] = [
          '#markup' => $this->t('You have reported this question'),
        ];
      }
      else {
        $form['report'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Report this question'),
          '#return_value' => 'report',
          '#ajax' => [
            'callback' => [$this, 'markReported'],
            'wrapper' => 'report_question_button_' . $this->question->id(),
            'event' => 'change',
          ],
        ];
      }
    }

    return $form;
  }

  /**
   * Ajax callback function to flag a question as reported.
   */
  public function markReported(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
