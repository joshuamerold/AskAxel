<?php

namespace Drupal\questions_answers;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a question entity.
 */
interface QuestionAnswerInterface extends ContentEntityInterface, EntityOwnerInterface, EntityPublishedInterface {

  /**
   * Comment is awaiting approval.
   */
  const NOT_PUBLISHED = 0;

  /**
   * Comment is published.
   */
  const PUBLISHED = 1;

  /**
   * Check if this question/answer can be viewed by the user.
   *
   * @return bool
   *   Flag indicating if the question/answer is viewable.
   */
  public function checkViewable();

  /**
   * Report this question/answer by an individual user.
   *
   * @param int $user_id
   *   ID of user reporting this question/answer.
   */
  public function addReport($user_id);

  /**
   * Get the reports on this question/answer.
   *
   * @return array
   *   An array of user IDs who have reported this question/answer.
   */
  public function getReports();

  /**
   * Get the report count for the question/answer.
   *
   * @return int
   *   The number of current reports on the entity.
   */
  public function getReportCount();

  /**
   * Clear all reports for this question/answer.
   */
  public function clearReports();

  /**
   * Returns the time that the comment was created.
   *
   * @return int
   *   The timestamp of when the comment was created.
   */
  public function getCreatedTime();

  /**
   * Get the primary value for the question/answer.
   *
   * @return string
   *   The question or answer string.
   */
  public function getValue();

  /**
   * Set the primary value for the question/answer.
   *
   * @param string $value
   *   The question or answer string.
   */
  public function setValue($value);

  /**
   * Get the entity this question/answer is associated with.
   *
   * @return Drupal\questions_answers\Entity
   *   The question or answer associated with this answer.
   */
  public function getEntity();

}
