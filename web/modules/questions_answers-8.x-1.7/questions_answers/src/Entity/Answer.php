<?php

namespace Drupal\questions_answers\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\questions_answers\AnswerInterface;

/**
 * Defines the Answer entity.
 *
 * @ingroup questions_answers
 *
 * @ContentEntityType(
 *   id = "questions_answers_answer",
 *   label = @Translation("Answer"),
 *   base_table = "questions_answers_answer",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "answer",
 *     "published" = "status",
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\questions_answers\AnswerViewsData",
 *   },
 * )
 */
class Answer extends ContentEntityBase implements AnswerInterface {

  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    $user = $this->get('uid')->entity;
    if (!$user || $user->isAnonymous()) {
      $user = User::getAnonymousUser();
      $user->name = $this->getAuthorName();
    }
    return $user;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthorName() {
    if ($this->get('uid')->target_id) {
      return $this->get('uid')->entity->label();
    }
    return \Drupal::config('user.settings')->get('anonymous');
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published = NULL) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    $this->save();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function checkViewable() {
    if (\Drupal::currentUser()->hasPermission('administer questions and answers')) {
      return TRUE;
    }
    else {
      return $this->isPublished() == 1;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addReport($user_id) {
    // Get the current reports.
    $reports = $this->getReports();
    // Add this user.
    $reports[] = $user_id;
    $this->reported = [$reports];
    $this->reported_count = count($reports);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getReports() {
    $reports = $this->reported->first();
    if (!empty($reports)) {
      return $reports->getValue();
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getReportCount() {
    return $this->get('reported_count')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function clearReports() {
    $this->reported = [[]];
    $this->reported_count = 0;
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getHelpfulVotes() {
    $helpfuldata = $this->helpful->first();
    if (!empty($helpfuldata)) {
      return $helpfuldata->getValue();
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getHelpfulVoteCount($type) {
    // Get the votes.
    $votes = $this->getHelpfulVotes();
    return count($votes[$type]);
  }

  /**
   * {@inheritdoc}
   */
  public function checkHelpfulVote($user_id) {
    // Get the votes.
    $votes = $this->getHelpfulVotes();

    // Determine how this user voted.
    if (in_array($user_id, $votes['yes'])) {
      return 'yes';
    }
    elseif (in_array($user_id, $votes['no'])) {
      return 'no';
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function addHelpfulVote($user_id, $type) {
    // Get the current votes.
    $votes = $this->getHelpfulVotes();
    // Remove user's old votes.
    if (($key = array_search($user_id, $votes['yes'])) !== FALSE) {
      unset($votes['yes'][$key]);
    }
    if (($key = array_search($user_id, $votes['no'])) !== FALSE) {
      unset($votes['no'][$key]);
    }
    // Add this user's vote.
    $votes[$type][] = $user_id;
    $this->helpful = [$votes];
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    if (isset($this->get('created')->value)) {
      return $this->get('created')->value;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return $this->get('answer')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value) {
    $this->answer->value = $value;
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function isVerified() {
    return $this->get('verified')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVerified($status) {
    $this->verified->value = $status;
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->get('entity_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->getQuestion()->getEntity();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Answer entity.'))
      ->setReadOnly(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Answer entity.'))
      ->setReadOnly(TRUE);

    $fields['entity_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The ID of the associated entity for the question.'))
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'questions_answers_question')
      ->setDefaultValue(0);

    $fields['answer'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Answer'))
      ->setDescription(t('The text of the Answer entity.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => '',
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['reported'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Reported'))
      ->setDescription(t('A list of users who have reported this answer.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(TRUE)
      ->setDefaultValue([]);
    $fields['reported_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Reported Count'))
      ->setDescription(t('The current count of reports.'))
      ->setReadOnly(TRUE)
      ->setDefaultValue(0);

    $fields['verified'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Verified'))
      ->setRevisionable(FALSE)
      ->setTranslatable(TRUE)
      ->setDescription(t('The verified status of the answer.'));

    $fields['helpful'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Helpful'))
      ->setDescription(t('A list of users who have flagged this answer as helpful or unhelpful.'))
      ->setRevisionable(FALSE)
      ->setTranslatable(TRUE)
      ->setDefaultValue(['yes' => [], 'no' => []]);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the answer author.'))
      ->setTranslatable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValue(0);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setTranslatable(TRUE);

    // Set the default value callback for the status field.
    $fields['status']->setDefaultValueCallback('Drupal\questions_answers\Entity\Answer::getDefaultStatus');

    return $fields;
  }

  /**
   * Default value callback for 'status' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return bool
   *   TRUE if the comment should be published, FALSE otherwise.
   */
  public static function getDefaultStatus() {
    return \Drupal::currentUser()->hasPermission('administer questions and answers') ? AnswerInterface::PUBLISHED : AnswerInterface::NOT_PUBLISHED;
  }

}
