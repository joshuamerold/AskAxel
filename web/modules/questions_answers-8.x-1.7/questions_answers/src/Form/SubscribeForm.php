<?php

namespace Drupal\questions_answers\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\questions_answers\Entity\Question;

/**
 * Form for subscribing to a question.
 */
class SubscribeForm extends FormBase {

  /**
   * Drupal current user service container.
   *
   * @var Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Question being reported.
   *
   * @var Drupal\questions_answers\Entity\Question
   */
  protected $question;

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
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
    return 'questions_answers_subscribe_form_' . $this->question->id();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Wrap the form for callback.
    $form['#prefix'] = '<div id="subscribe_button_' . $this->question->id() . '">';
    $form['#suffix'] = '</div>';

    // Prevent caching.
    $form['#cache'] = ['max-age' => 0];

    // If the user is submitting an email address (anonymous).
    if (!empty($form_state->getValue('email'))) {
      $form['message'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['messages messages--status helpful-message'],
        ],
        '#children' => $this->t('Thank you! You will be notified when this question is answered.'),
      ];

      // Subscribe.
      $this->question->addSubscription($form_state->getValue('email'));
    }
    // If the user has subscribed.
    if (!empty($form_state->getValue('subscribe'))) {

      // Have to gather the email if this is an anonymous user.
      if ($this->currentUser->isAnonymous()) {
        $form['email'] = [
          '#type' => 'email',
          '#title' => $this->t('What is your email address?'),
        ];
        $form['button'] = [
          '#type' => 'button',
          '#value' => $this->t('Submit'),
          '#ajax' => [
            'callback' => [$this, 'markSubscribed'],
            'wrapper' => 'subscribe_button_' . $this->question->id(),
            'event' => 'click',
          ],
        ];
        return $form;
      }
      else {
        $form['message'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['messages messages--status helpful-message'],
          ],
          '#children' => $this->t('Thank you! You will be notified when this question is answered.'),
        ];
        // Subscribe.
        $this->question->addSubscription($this->currentUser->id());
      }
    }
    elseif (!empty($form_state->getValue('unsubscribe'))) {
      $form['message'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['messages messages--status helpful-message'],
        ],
        '#children' => $this->t('You have been successfully unsubscribed from this question.'),
      ];

      // Unsubscribe.
      $this->question->removeSubscription($this->currentUser->id());
    }

    // Get the current subscribed.
    $subscribed = $this->question->getSubscribed();

    // Determine if the user is already subscribed.
    if (!$this->currentUser->isAnonymous() && in_array($this->currentUser->id(), $subscribed)) {
      $form['already-subscribed'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['already-subscribed'],
        ],
        '#children' => $this->t('You are subscribed to this question.'),
      ];
      $form['unsubscribe'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Unsubscribe'),
        '#return_value' => 'unsubscribe',
        '#ajax' => [
          'callback' => [$this, 'markSubscribed'],
          'wrapper' => 'subscribe_button_' . $this->question->id(),
          'event' => 'change',
        ],
      ];
    }
    else {
      $form['subscribe'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Notify me when this question is answered'),
        '#return_value' => 'subscribe',
        '#ajax' => [
          'callback' => [$this, 'markSubscribed'],
          'wrapper' => 'subscribe_button_' . $this->question->id(),
          'event' => 'change',
        ],
      ];
    }

    return $form;
  }

  /**
   * Ajax callback function to mark an answer as helpful.
   */
  public function markSubscribed(array $form, FormStateInterface $form_state) {
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
