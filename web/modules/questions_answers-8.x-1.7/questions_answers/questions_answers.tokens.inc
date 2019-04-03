<?php

/**
 * @file
 * Builds placeholder replacement tokens for questions and answers data.
 */

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Url;

/**
 * Implements hook_token_info().
 */
function questions_answers_token_info() {

  // Core question tokens.
  $question_type = [
    'name' => t('Questions'),
    'description' => t('Tokens for questions posted on the site.'),
    'needs-data' => 'question',
  ];
  $question = [];
  $question['id'] = [
    'name' => t('Question ID'),
    'description' => t('The unique ID of the question.'),
  ];
  $question['question'] = [
    'name' => t('Question'),
    'description' => t('The value of the question.'),
  ];
  $question['created'] = [
    'name' => t("Date created"),
    'description' => t("The date the question was posted."),
    'type' => 'date',
  ];
  $question['answer-count'] = [
    'name' => t('Answer count'),
    'description' => t('The count of the current number of answers for this question.'),
  ];
  $question['reported-count'] = [
    'name' => t('Reported count'),
    'description' => t('The count of the current number of reports for this question.'),
  ];
  $question['subscribed-count'] = [
    'name' => t('Subscribed count'),
    'description' => t('The count of the current number of subscriptions for this question.'),
  ];
  $question['entity'] = [
    'name' => t('Entity'),
    'description' => t('The entity the question was posted to.'),
    'type' => 'entity',
  ];
  $question['entity-link'] = [
    'name' => t('Entity Link'),
    'description' => t('Link to the entity the question was posted to.'),
  ];
  $question['author'] = [
    'name' => t('Author'),
    'description' => t('The author name of the question.'),
    'type' => 'user',
  ];

  // Core answer tokens.
  $answer_type = [
    'name' => t('Answers'),
    'description' => t('Tokens for answers posted on the site.'),
    'needs-data' => 'answer',
  ];
  $answer = [];
  $answer['id'] = [
    'name' => t('Answer ID'),
    'description' => t('The unique ID of the answer.'),
  ];
  $answer['answer'] = [
    'name' => t('Answer'),
    'description' => t('The value of the answer.'),
  ];
  $answer['created'] = [
    'name' => t("Date created"),
    'description' => t("The date the answer was posted."),
    'type' => 'date',
  ];
  $answer['reported-count'] = [
    'name' => t('Reported count'),
    'description' => t('The count of the current number of reports for this answer.'),
  ];
  $answer['entity'] = [
    'name' => t('Entity'),
    'description' => t('The entity the answer was posted to.'),
    'type' => 'entity',
  ];
  $answer['entity-link'] = [
    'name' => t('Entity Link'),
    'description' => t('Link to the entity the answer was posted to.'),
  ];
  $answer['author'] = [
    'name' => t('Author'),
    'description' => t('The author name of the answer.'),
    'type' => 'user',
  ];

  // Add global tokens.
  $questions_answers = [];
  $questions_answers_type = [
    'name' => t('Questions and Answers'),
    'description' => t('Tokens for questions and answers posted on the site.'),
  ];
  $questions_answers['unsubscribe-link'] = [
    'name' => t('Unsubscribe Link'),
    'description' => t('The link to unsubscribe to all questions and answers.'),
  ];

  return [
    'types' => [
      'question' => $question_type,
      'answer' => $answer_type,
      'questions-answers' => $questions_answers_type,
    ],
    'tokens' => [
      'question' => $question,
      'answer' => $answer,
      'questions-answers' => $questions_answers,
    ],
  ];
}

/**
 * Implements hook_tokens().
 */
function questions_answers_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();

  if (isset($options['langcode'])) {
    $langcode = $options['langcode'];
  }
  else {
    $langcode = NULL;
  }
  $replacements = [];

  // Handle question types.
  if ($type == 'questions-answers') {

    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Simple key values on the comment.
        case 'unsubscribe-link':
          $replacements[$original] = Url::fromRoute('questions_answers.unsubscribe', [], ['absolute' => TRUE])->toString();
          break;
      }
    }
  }
  // Handle question types.
  elseif ($type == 'question' && !empty($data['question'])) {
    /** @var \Drupal\questions_answers\QuestionInterface $question */
    $question = $data['question'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Simple key values on the comment.
        case 'id':
          $replacements[$original] = $question->id();
          break;

        case 'question':
          $replacements[$original] = $question->getValue();
          break;

        case 'answer-count':
          $replacements[$original] = $question->getAnswerCount();
          break;

        case 'reported-count':
          $replacements[$original] = $question->getReportedCount();
          break;

        case 'subscribed-count':
          $replacements[$original] = $question->getSubscribedCount();
          break;

        case 'entity-link':
          $replacements[$original] = $question->getEntity()->toLink(NULL, 'canonical', ['absolute' => TRUE])->toString();
          break;

        case 'author':
          $name = $question->getAuthorName();
          // Add the user cacheability metadata.
          if ($question->getOwnerId()) {
            $bubbleable_metadata->addCacheableDependency($question->getOwner());
          }
          $replacements[$original] = $name;
          break;

        case 'created':
          $date_format = DateFormat::load('medium');
          $bubbleable_metadata->addCacheableDependency($date_format);
          $replacements[$original] = format_date($question->getCreatedTime(), 'medium', '', NULL, $langcode);
          break;

        case 'entity':
          $entity = $question->getEntity();
          $bubbleable_metadata->addCacheableDependency($entity);
          $title = $entity->label();
          $replacements[$original] = $title;
          break;
      }
    }

    // Chained token relationships.
    if ($entity_tokens = $token_service->findwithPrefix($tokens, 'entity')) {
      $entity = $question->getEntity();
      $replacements += $token_service->generate($entity->getEntityTypeId(), $entity_tokens, [$entity->getEntityTypeId() => $entity], $options, $bubbleable_metadata);
    }

    if ($date_tokens = $token_service->findwithPrefix($tokens, 'created')) {
      $replacements += $token_service->generate('date', $date_tokens, ['date' => $question->getCreatedTime()], $options, $bubbleable_metadata);
    }

    if (($author_tokens = $token_service->findwithPrefix($tokens, 'author')) && $account = $question->getOwner()) {
      $replacements += $token_service->generate('user', $author_tokens, ['user' => $account], $options, $bubbleable_metadata);
    }
  }
  // Handle answer types.
  elseif ($type == 'answer' && !empty($data['answer'])) {
    /** @var \Drupal\questions_answers\QuestionInterface $question */
    $answer = $data['answer'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Simple key values on the comment.
        case 'id':
          $replacements[$original] = $answer->id();
          break;

        case 'answer':
          $replacements[$original] = $answer->getValue();
          break;

        case 'reported-count':
          $replacements[$original] = $answer->getReportedCount();
          break;

        case 'entity-link':
          $replacements[$original] = $answer->getEntity()->toLink(NULL, 'canonical', ['absolute' => TRUE])->toString();
          break;

        case 'author':
          $name = $answer->getAuthorName();
          // Add the user cacheability metadata.
          if ($answer->getOwnerId()) {
            $bubbleable_metadata->addCacheableDependency($answer->getOwner());
          }
          $replacements[$original] = $name;
          break;

        case 'created':
          $date_format = DateFormat::load('medium');
          $bubbleable_metadata->addCacheableDependency($date_format);
          $replacements[$original] = format_date($answer->getCreatedTime(), 'medium', '', NULL, $langcode);
          break;

        case 'entity':
          $entity = $answer->getEntity();
          $bubbleable_metadata->addCacheableDependency($entity);
          $title = $entity->label();
          $replacements[$original] = $title;
          break;
      }
    }

    // Chained token relationships.
    if ($entity_tokens = $token_service->findwithPrefix($tokens, 'entity')) {
      $entity = $answer->getEntity();
      $replacements += $token_service->generate($entity->getEntityTypeId(), $entity_tokens, [$entity->getEntityTypeId() => $entity], $options, $bubbleable_metadata);
    }

    if ($date_tokens = $token_service->findwithPrefix($tokens, 'created')) {
      $replacements += $token_service->generate('date', $date_tokens, ['date' => $answer->getCreatedTime()], $options, $bubbleable_metadata);
    }

    if (($author_tokens = $token_service->findwithPrefix($tokens, 'author')) && $account = $answer->getOwner()) {
      $replacements += $token_service->generate('user', $author_tokens, ['user' => $account], $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
