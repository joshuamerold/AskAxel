<?php

/**
 * @file
 * Question locking functions for the 'Answers' module.
 *
 * The Answers module allow questions to be "locked". This means that users are
 * no longer permitted to provide new answers. Users may still comment on and
 * edit answers (and, depending on how permissions are set, users may comment on
 * and edit questions).
 *
 * Other modules may want to change their behavior based on whether a question
 * is locked. For exmaple, the best answer module does not permit question
 * authors to set or change best answers for locked questions.
 *
 * The logic about *when* to lock or unlock questions is set by other modules.
 * The answers module (and this file) only provides  the API. When several
 * modules are using locks, the rule for combining results is that if *any*
 * module wants a question locked, then it is locked.
 *
 * Users who have the "manage answers content" permission are not impacted by
 * question locks. The can still add answers and change best answers. Site
 * administrators may wish to give this permission to content editors.
 *
 * To support locking, this file provides a hook and some utility functions.
 *
 * The hook:
 *   - hook_answers_lock_info(): Modules that want to lock or unlock questions
 *     define this hook.
 *     It should return TRUE if the module will participate in locking.
 *
 * The utility functions:
 *    - answers_question_lock_set($question, $module):
 *        Lock a specific question
 *    - answers_question_lock_unset($question, $module):
 *        Unlock a specific question
 *    - answers_question_locked_p($question):
 *        Returns non-null if question is locked
 */

/**
 * Determine which modules have a stake in locking questions.
 */
function answers_locking_modules() {
  $locks = &drupal_static(__FUNCTION__);

  if (!isset($locks)) {
    // Try to get from cache.
    if ($cache = cache_get('answers_locking_modules')) {
      $locks = $cache->data;
    }

    if (!$locks) {
      $locks = array();
      foreach (module_implements('answers_lock_info') as $module) {
        if (module_invoke($module, 'answers_lock_info')) {
          $locks[$module] = $module;
        }
      }

      cache_set('answers_locking_modules', $locks, 'cache', CACHE_TEMPORARY);
    }
  }

  return $locks;
}

/**
 * Clear locking information cache.
 */
function answers_reset_lock_info() {
  drupal_static_reset('answers_locking_modules');
  cache_clear_all('answers_locking_modules', 'cache');
}

/**
 * Lock a question.
 *
 * @param object $question
 *   A fully loaded question node.
 * @param string $module
 *   The module setting the lock.
 */
function answers_question_lock_set($question, $module) {
  if (in_array($module, answers_locking_modules())) {
    $items = field_get_items('node', $question, 'question_locks');

    // Check if the question is already locked by this module.
    if (!$items || array_search(array('value' => $module), $items) === FALSE) {
      // Find the delta.
      $delta = count($items);
      $lang = field_language('node', $question, 'question_locks');
      $question->question_locks[$lang][$delta]['value'] = $module;
      node_save($question);
    }
  }
}

/**
 * Unlock a question.
 *
 * @param object $question
 *   A fully loaded question node.
 * @param string $module
 *   The module that set the lock.
 */
function answers_question_lock_unset($question, $module) {
  if (in_array($module, answers_locking_modules())) {
    $items = field_get_items('node', $question, 'question_locks');
    // If the question is locked by this module, unset the lock.
    if ($items && ($delta = array_search(array('value' => $module), $items) !== FALSE)) {
      $lang = field_language('node', $question, 'question_locks');
      $count = count($items);

      // Copy elements of lock array down.
      for ($i = $delta; $i < $count - 1; $i++) {
        $question->question_locks[$lang][$i] = $question->question_locks[$lang][$i + 1];
      }
      // Delete last element of lock array.
      unset($question->question_locks[$lang][$count - 1]);
      node_save($question);
    }
  }
}

/**
 * Indicate whether a question is locked or not.
 *
 * @param object $question
 *   A question node.
 */
function answers_question_locked_p($question) {
  $items = field_get_items('node', $question, 'question_locks');
  if (!$items) {
    return FALSE;
  }

  $modules = array();
  foreach ($items as $module) {
    $modules[] = $module['value'];
  }

  // Compare the list with only currently locking modules.
  return count(array_intersect($modules, answers_locking_modules())) > 0;
}

/**
 * Implements hook_query_TAG_alter().
 *
 * The 'question_locked' tag modifies the query to return only those
 * questions which are locked.
 */
function answers_query_question_locked_alter(QueryAlterableInterface $query) {
  $query->innerJoin('field_data_question_locks', 'l', 'node.nid = l.entity_id AND l.entity_type = :entity_type', array(':entity_type' => 'node'));
}

/**
 * Implements hook_query_TAG_alter().
 *
 * The 'question_unlocked' tag modifies the query to return only those
 * questions which are not locked.
 */
function answers_query_question_unlocked_alter(QueryAlterableInterface $query) {
  $query->leftJoin('field_data_question_locks', 'l', 'node.nid = l.entity_id AND l.entity_type = :entity_type', array(':entity_type' => 'node'));
  $query->isNull('l.question_locks_value');
}
