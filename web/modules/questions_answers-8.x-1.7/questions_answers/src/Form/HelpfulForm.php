<?php

namespace Drupal\questions_answers\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\questions_answers\Entity\Answer;

/**
 * Form for flagging an answer as helpful or not.
 */
class HelpfulForm extends FormBase {

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
   * Answer generating a help form for.
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
    return 'questions_answers_helpful_form_' . $this->answer->id();
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
    $form['#prefix'] = '<div id="helpful_button_' . $this->answer->id() . '">';
    $form['#suffix'] = '</div>';

    // Prevent caching.
    $form['#cache'] = ['max-age' => 0];

    // If the user has submitted a value.
    if (!empty($form_state->getValue('helpful'))) {
      $form['message'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['messages messages--status helpful-message'],
        ],
        '#children' => $this->t('Thank you for your feedback!'),
      ];

      // Add the vote.
      if ($this->currentUser->isAnonymous()) {
        // Store a session ID for anonymous.
        $this->answer->addHelpfulVote($this->sessionManager->getId(), $form_state->getValue('helpful'));
      }
      else {
        $this->answer->addHelpfulVote($this->currentUser->id(), $form_state->getValue('helpful'));
      }
    }

    // Generate the helpful radios.
    $form['helpful'] = [
      '#type' => 'radios',
      '#multiple' => FALSE,
      '#title' => $this->t('Was this helpful?'),
      '#options' => [
        'yes' => $this->t('Yes (@count @users)', [
          '@count' => $this->answer->getHelpfulVoteCount('yes'),
          '@users' => ($this->answer->getHelpfulVoteCount('yes') == 1 ? $this->t('user') : $this->t('users')),
        ]),
        'no' => $this->t('No (@count @users)', [
          '@count' => $this->answer->getHelpfulVoteCount('no'),
          '@users' => ($this->answer->getHelpfulVoteCount('no') == 1 ? $this->t('user') : $this->t('users')),
        ]),
      ],
      '#default_value' => $this->answer->checkHelpfulVote($this->currentUser->isAnonymous() ? $this->sessionManager->getId() : $this->currentUser->id()),
      '#ajax' => [
        'callback' => [$this, 'markHelpful'],
        'wrapper' => 'helpful_button_' . $this->answer->id(),
        'event' => 'change',
      ],
    ];

    return $form;
  }

  /**
   * Ajax callback function to mark an answer as helpful.
   */
  public function markHelpful(array $form, FormStateInterface $form_state) {
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
