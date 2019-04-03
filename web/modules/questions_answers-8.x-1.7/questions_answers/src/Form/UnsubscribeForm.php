<?php

namespace Drupal\questions_answers\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\questions_answers\Entity\Question;

/**
 * Form for subscribing to a question.
 */
class UnsubscribeForm extends ConfirmFormBase {

  /**
   * Drupal current user service container.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal mail manager service container.
   *
   * @var Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Drupal entity query service container.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountProxyInterface $current_user, ConfigFactory $config_factory, QueryFactory $query_factory) {
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
    $this->entityQuery = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('config.factory'),
      $container->get('entity.query')
    );
  }

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   */
  public function access(AccountInterface $account) {
    // Check permissions.
    return AccessResult::allowedIf(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the user email if they are not logged in.
    if ($this->currentUser->isAnonymous()) {
      $form['message'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['message'],
        ],
        '#children' => $this->t('This form will unsubscribe you from all Questions and Answers notifications.'),
      ];
      $form['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Please confirm your email address'),
      ];
      $form['button'] = [
        '#type' => 'submit',
        '#value' => $this->t('Unsubscribe'),
      ];
      return $form;
    }
    else {
      return parent::buildForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'questions_answers_unsubscribe_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('<front>');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to unsubscribe from all @sitename Questions and Answers notifications?', [
      '@sitename' => $this->configFactory->get('system.site')->get('name'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Unsubscribe');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Unbsubscribing will remove you from all email notifications for questions and answers on this site. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Load the questions.
    $question_ids = $this->entityQuery->get('questions_answers_question')
      ->execute();
    if (count($question_ids) > 0) {
      foreach ($question_ids as $question_id) {
        // Load the question.
        $question = Question::load($question_id);
        // Unsubscribe either the user ID or email depending on login state.
        $question->removeSubscription(empty($form_state->getValue('email')) ? $this->currentUser->id() : $form_state->getValue('email'));
      }
    }

    // Show message.
    $this->messenger()->addStatus($this->t('You have been successfully unsubscribed from all Questions and Answers notifications for @sitename.', [
      '@sitename' => $this->configFactory->get('system.site')->get('name'),
    ]));

    $form_state->setRedirect('<front>');
  }

}
