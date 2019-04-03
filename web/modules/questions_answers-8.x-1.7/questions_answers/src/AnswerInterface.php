<?php

namespace Drupal\questions_answers;

/**
 * Provides an interface defining a question entity.
 */
interface AnswerInterface extends QuestionAnswerInterface {

  /**
   * Get the helpful votes on this answer.
   *
   * @return array
   *   An array of user IDs who have voted on this answer.
   */
  public function getHelpfulVotes();

  /**
   * Get the vote count for a yes or no vote on this answer.
   *
   * @param string $type
   *   The type of vote we are counting.
   *
   * @return int
   *   The number of users who have voted this way for this answer.
   */
  public function getHelpfulVoteCount($type);

  /**
   * Get a user's vote on this answer.
   *
   * @param int $user_id
   *   ID of user we are checking.
   *
   * @return string
   *   The user's vote on whether the answer is helpful.
   */
  public function checkHelpfulVote($user_id);

  /**
   * Vote on this answer by an individual user.
   *
   * @param int $user_id
   *   ID of user voting on this answer.
   * @param string $type
   *   The type of vote we are adding.
   */
  public function addHelpfulVote($user_id, $type);

  /**
   * Get the verification status of this answer.
   *
   * @return int
   *   The verification value.
   */
  public function isVerified();

  /**
   * Set the verification status of this answer.
   *
   * @param int $status
   *   The verification value.
   */
  public function setVerified($status);

  /**
   * Get the question this answer is associated with.
   *
   * @return Drupal\questions_answers\Entity\Question
   *   The question associated with this answer.
   */
  public function getQuestion();

}
