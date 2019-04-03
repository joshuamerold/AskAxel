<?php

namespace Drupal\questions_answers\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\questions_answers\Entity\Answer;

/**
 * Form for reporting an answer about an entity question.
 */
class ReportAnswerForm extends FormBase {

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
   * Answer being reported.
   *
   * @var Drupal\questions_answers\Entity\Answer
   */
  protected $answer;

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
   * Sets the answer being used to build this form.
   *
   * @param Drupal\questions_answers\Entity\Answer $answer
   *   The answer we are building the form for.
   */
  public function setAnswer(Answer $answer) {
    $this->answer = $answer;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    if (!isset($this->answer)) {
      throw new \Exception('Answer not found. setAnswer() should be called before form instantiation.');
    }
    return 'questions_answers_report_answer_form_' . $this->answer->id();
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
    $form['#prefix'] = '<div id="report_answer_button_' . $this->answer->id() . '">';
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
        '#children' => $this->t('You have successfully reported this answer.'),
      ];

      // Add the report.
      if ($this->currentUser->isAnonymous()) {
        // Store a session ID for anonymous.
        $this->answer->addReport($this->sessionManager->getId());
      }
      else {
        $this->answer->addReport($this->currentUser->id());
      }
    }
    else {
      // Get the current reports.
      $reports = $this->answer->getReports();

      // Check if this form was just reported.
      if ($this->currentUser->isAnonymous() && in_array($this->sessionManager->getId(), $reports) || in_array($this->currentUser->id(), $reports)) {
        $form['report'] = [
          '#markup' => $this->t('You have reported this answer'),
        ];
      }
      else {
        $form['report'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Report this answer'),
          '#return_value' => 'report',
          '#ajax' => [
            'callback' => [$this, 'markReported'],
            'wrapper' => 'report_answer_button_' . $this->answer->id(),
            'event' => 'change',
          ],
        ];
      }
    }

    return $form;
  }

  /**
   * Ajax callback function to flag an answer as reported.
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
