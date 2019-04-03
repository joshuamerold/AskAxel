<?php

namespace Drupal\questions_answers\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Path\PathValidatorInterface;

/**
 * Defines a form that configures Questions and Answers settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * {@inheritdoc}
   */
  public function __construct(PathValidatorInterface $path_validator) {
    $this->pathValidator = $path_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'questions_answers_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'questions_answers.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get current settings.
    $settings = $this->config('questions_answers.settings');

    $form['terms_and_conditions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to Terms and Conditions page'),
      '#default_value' => $settings->get('terms_and_conditions'),
      '#description' => $this->t('You can add an optional path to legal terms and conditions here to show at the bottom of your questions and answers. Leave blank if you do not wish to add a link'),
    ];
    $form['no_access_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message to users who do not have permissions to ask questions'),
      '#default_value' => $settings->get('no_access_message'),
      '#description' => $this->t('This message is displayed below all questions on an entity to users who do not have permission to ask questions. Leave blank for no message.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate terms and conditions path.
    if (($value = $form_state->getValue('terms_and_conditions')) && $value[0] !== '/') {
      $form_state->setErrorByName('terms_and_conditions', $this->t("The path '%path' has to start with a slash.", ['%path' => $form_state->getValue('terms_and_conditions')]));
    }
    if (!$form_state->isValueEmpty('terms_and_conditions') && !$this->pathValidator->isValid($form_state->getValue('terms_and_conditions'))) {
      $form_state->setErrorByName('terms_and_conditions', $this->t("The path '%path' is either invalid or you do not have access to it.", ['%path' => $form_state->getValue('terms_and_conditions')]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Save the updated settings.
    $this->config('questions_answers.settings')
      ->set('terms_and_conditions', $values['terms_and_conditions'])
      ->set('no_access_message', $values['no_access_message'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
