<?php

/**
 * @file
 * Hooks for messages in answers_notifications.
 */

/**
 * Implements hook_default_message_type().
 */
function answers_notifications_default_message_type() {
  $items = array();
  $items['answers_notifications_new_answer'] = entity_import('message_type', '{
    "name" : "answers_notifications_new_answer",
    "description" : "Alert subscribers that a question has a new answer",
    "argument_keys" : [],
    "argument" : [],
    "category" : "message_type",
    "data" : {
      "token options" : { "clear" : 0 },
      "purge" : { "override" : 0, "enabled" : 0, "quota" : "", "days" : "" }
  },
  "language" : "en",
  "arguments" : null,
  "message_text" : { "und" : [
      {
        "value" : "You have a new answer on [site:name].",
        "format" : "plain_text",
        "safe_value" : "\u003Cp\u003EYou have a new answer on [site:name].\u003C\/p\u003E\n"
      },
      {
        "value" : "\u003Cp\u003EUser \u003Ca href=\u0022[message:answers-notifications-answer:author:url:absolute]\u0022\u003E[message:answers-notifications-answer:author:name]\u003C\/a\u003E has offered a new \u003Ca href=\u0022[message:answers-notifications-question:url:absolute]#answer-[message:answers-notifications-answer:nid]\u0022\u003Eanswer\u003C\/a\u003E to this question: \u003Ca href=\u0022[message:answers-notifications-question:url:absolute]\u0022\u003E[message:answers-notifications-question:title]\u003C\/a\u003E.\u003C\/p\u003E",
        "format" : "full_html",
        "safe_value" : "\u003Cp\u003EUser \u003Ca href=\u0022[message:answers-notifications-answer:author:url:absolute]\u0022\u003E[message:answers-notifications-answer:author:name]\u003C\/a\u003E has offered a new \u003Ca href=\u0022[message:answers-notifications-question:url:absolute]#answer-[message:answers-notifications-answer:nid]\u0022\u003Eanswer\u003C\/a\u003E to this question: \u003Ca href=\u0022[message:answers-notifications-question:url:absolute]\u0022\u003E[message:answers-notifications-question:title]\u003C\/a\u003E.\u003C\/p\u003E\n"
      }
    ]
  },
  "rdf_mapping" : []
  }');
  return $items;
}
