<?php

/**
 * @file
 * Definition of AnswersNewContentSinceLastLoginViewsHandler.
 */

/**
 * Field handler to display new content in question listing since last login.
 *
 * @ingroup views_field_handlers
 */
class AnswersNewContentSinceLastLoginViewsHandler extends views_handler_field {

  /**
   * Construct a new field handler.
   */
  public function construct() {
    parent::construct();
    global $user;
    if ($user->uid) {
      $this->additional_fields['nid'] = array('table' => 'node', 'field' => 'nid');
      $this->additional_fields['created'] = array('table' => 'node', 'field' => 'created');
    }
  }

  /**
   * Called to add the field to a query.
   */
  public function query() {
    global $user;
    if (!$user->uid) {
      return;
    }
    $this->field_alias = 'new_answers_since_last_login';
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  /**
   * Render the field.
   *
   * @param array $values
   *   The values retrieved from the database.
   */
  public function render(array $values) {
    global $user;
    if (!$user->uid) {
      return '';
    }
    $qid = $this->get_value($values, 'nid');
    $created = $this->get_value($values, 'created');
    $timestamp = node_last_viewed($qid);
    $output = '';
    // If the node was viewed or is a favorite.
    if ($timestamp) {
      $answers = db_query("SELECT nid FROM  {node} node LEFT JOIN {field_data_answers_related_question} field_data_answers_related_question ON node.nid = field_data_answers_related_question.entity_id AND field_data_answers_related_question.entity_type = 'node' AND field_data_answers_related_question.deleted = '0' WHERE field_data_answers_related_question.answers_related_question_target_id = :qid AND node.status = '1' AND node.created > :last_viewed_timestamp", array(':qid' => $qid, ':last_viewed_timestamp' => $timestamp))->fetchCol();
      if ($answers) {
        // Render as link will point to latest answer.
        $new_answer_text = format_plural(count($answers), "1 new answer", "@count new answers");
        $output = l($new_answer_text, 'node/' . $qid, array('attributes' => array('class' => 'answers-new'), 'fragment' => "node-" . end($answers)));
      }
    }
    else {
      if ($created > $user->login) {
        $output = '<span class="answers-new">' . t('new') . '</span>';
      }
    }
    return $output;
  }

}
