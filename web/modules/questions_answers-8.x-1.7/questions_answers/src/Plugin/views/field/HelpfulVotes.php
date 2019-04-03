<?php

namespace Drupal\questions_answers\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\user\Entity\User;

/**
 * Field handler to display helpful votes.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("questions_answers_helpfulvotes")
 */
class HelpfulVotes extends FieldPluginBase {

  /**
   * Additional query data.
   */
  public function query() {}

  /**
   * Define the available options.
   *
   * @return array
   *   Array of available options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['display_type'] = ['default' => 'count'];

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['display_type'] = [
      '#title' => $this->t('How should this be displayed?'),
      '#type' => 'select',
      '#default_value' => $this->options['display_type'],
      '#options' => [
        'count' => $this->t('Count of votes'),
        'list' => $this->t('List of users'),
      ],
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * Render the field.
   *
   * @param \Drupal\views\ResultRow $values
   *   The result row we are rendering the value for.
   */
  public function render(ResultRow $values) {
    $entity = $values->_entity;

    // Use the relationship entity if this is one.
    if ($this->options['table'] == 'questions_answers_answer') {
      foreach ($values->_relationship_entities as $r_entity) {
        $entity = $r_entity;
      }
    }

    // Handle the functionality based on display type.
    switch ($this->options['display_type']) {

      case 'list':
        if ($entity->getHelpfulVoteCount('yes') > 0 || $entity->getHelpfulVoteCount('no') > 0) {
          $votes = [
            'yes' => [
              '#theme' => 'item_list',
              '#items' => [],
              '#type' => 'ul',
              '#title' => $this->t('Helpful Votes'),
            ],
            'no' => [
              '#theme' => 'item_list',
              '#items' => [],
              '#type' => 'ul',
              '#title' => $this->t('Unhelpful Votes'),
            ],
          ];
          foreach ($entity->getHelpfulVotes() as $type => $uids) {
            foreach ($uids as $uid) {
              $user = User::load($uid);
              $votes[$type]['#items'][] = $user->toLink()->toString();
            }
          }
          return $votes;
        }
        break;

      default:
      case 'count':
        if ($entity->getHelpfulVoteCount('yes') > 0 || $entity->getHelpfulVoteCount('no') > 0) {
          return $this->t('@yescount Helpful Votes, @nocount Unhelpful Votes', [
            '@yescount' => $entity->getHelpfulVoteCount('yes'),
            '@nocount' => $entity->getHelpfulVoteCount('no'),
          ]);
        }
        break;
    }

    return $this->t('No votes');
  }

}
