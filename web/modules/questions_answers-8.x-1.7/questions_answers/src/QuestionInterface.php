<?php

namespace Drupal\questions_answers;

/**
 * Provides an interface defining a question entity.
 */
interface QuestionInterface extends QuestionAnswerInterface {

  /**
   * Add subscription to this question by an individual user.
   *
   * @param int $user_id
   *   ID of user subscribing to this question.
   */
  public function addSubscription($user_id);

  /**
   * Remove subscription to this question by an individual user.
   *
   * @param int $user_id
   *   ID of user unsubscribing to this question.
   */
  public function removeSubscription($user_id);

  /**
   * Get the subscriptions on this question.
   *
   * @return array
   *   An array of user IDs who have subscribed to this question.
   */
  public function getSubscribed();

  /**
   * Get the subscription count for the question.
   *
   * @return int
   *   The number of current subscriptions on the entity.
   */
  public function getSubscribedCount();

  /**
   * Returns the question author's name.
   *
   * For anonymous authors, this is the value as typed in the question form.
   *
   * @return string
   *   The name of the question author.
   */
  public function getAuthorName();

  /**
   * Update the answer count on the question.
   */
  public function updateAnswerCount();

  /**
   * Get the answer count for the question.
   *
   * @return int
   *   The number of current answers on the question.
   */
  public function getAnswerCount();

  /**
   * Get the answers for this question.
   *
   * @return array
   *   An array of Answer entities tied to this question.
   */
  public function getAnswers();

}
