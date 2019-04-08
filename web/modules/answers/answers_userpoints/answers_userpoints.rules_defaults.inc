<?php

/**
 * @file
 * Default rules for the answers_userpoints module.
 */

/**
 * Provide default rules.
 */
function answers_userpoints_default_rules_configuration() {
  $rules['rules_voteup_question'] = entity_import('rules_config', '{
    "rules_voteup_question" : {
      "LABEL" : "voteup_question",
      "PLUGIN" : "reaction rule",
      "OWNER" : "rules",
     "TAGS" : [ "answers" ],
    "REQUIRES" : [ "rules", "voting_rules", "userpoints_rules" ],
    "ON" : { "voting_rules_insert_node" : [] },
    "IF" : [
      { "node_is_of_type" : {
          "node" : [ "node" ],
          "type" : { "value" : { "answers_question" : "answers_question" } }
        }
      },
      { "voting_rules_condition_check_vote_value" : { "vote" : [ "vote" ], "value" : "1" } }
    ],
     "DO" : [
       { "userpoints_action_grant_points" : {
           "user" : [ "node:author" ],
          "points" : "5",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "addPoints",
          "display" : 1,
          "moderate" : "default"
        }
      }
    ]
  }}');

  $rules['rules_voteup_question_cancel'] = entity_import('rules_config', '{
    "rules_voteup_question_cancel" : {
    "LABEL" : "voteup_question_cancel",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "REQUIRES" : [ "rules", "voting_rules", "userpoints_rules" ],
    "ON" : { "voting_rules_delete_node" : [] },
    "IF" : [
      { "node_is_of_type" : {
          "node" : [ "node" ],
          "type" : { "value" : { "answers_question" : "answers_question" } }
        }
      },
      { "voting_rules_condition_check_vote_value" : { "vote" : [ "vote" ], "value" : "1" } }
    ],
    "DO" : [
      { "userpoints_action_grant_points" : {
          "user" : [ "node:author" ],
          "points" : "-5",
           "tid" : "0",
           "entity" : [ "" ],
           "operation" : "addPoints",
           "display" : 1,
           "moderate" : "default"
        }
      }
     ]
   }}');

  $rules['rules_votedown_question'] = entity_import('rules_config', '{
    "rules_votedown_question" : {
    "LABEL" : "votedown_question",
    "PLUGIN" : "reaction rule",
    "TAGS" : [ "answers" ],
    "REQUIRES" : [ "rules", "voting_rules", "userpoints_rules" ],
    "ON" : { "voting_rules_insert_node" : [] },
    "IF" : [
      { "node_is_of_type" : {
          "node" : [ "node" ],
          "type" : { "value" : { "answers_question" : "answers_question" } }
        }
      },
     { "voting_rules_condition_check_vote_value" : { "vote" : [ "vote" ], "value" : "-1" } }
    ],
    "DO" : [
      { "userpoints_action_grant_points" : {
          "user" : [ "node:author" ],
          "points" : "-2",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "removePoints",
          "display" : 1,
          "moderate" : "default"
        }
      },
      { "userpoints_action_grant_points" : {
          "user" : [ "vote:user" ],
          "points" : "-1",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "removePoints",
          "display" : 1,
          "moderate" : "default"
      }}
    ]
  }}');

  $rules['rules_votedown_question_cancel'] = entity_import('rules_config', '{
    "rules_votedown_question_cancel" : {
    "LABEL" : "votedown_question_cancel",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "REQUIRES" : [ "rules", "voting_rules", "userpoints_rules" ],
    "ON" : { "voting_rules_delete_node" : [] },
    "IF" : [
      { "node_is_of_type" : {
          "node" : [ "node" ],
          "type" : { "value" : { "answers_question" : "answers_question" } }
        }
      },
      { "voting_rules_condition_check_vote_value" : { "vote" : [ "vote" ], "value" : "-1" } }
    ],
    "DO" : [
      { "userpoints_action_grant_points" : {
          "user" : [ "vote:user" ],
          "points" : "1",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "deletePoints",
          "display" : 1,
          "moderate" : "default"
        }
      },
      { "userpoints_action_grant_points" : {
          "user" : [ "node:author" ],
          "points" : "2",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "deletePoints",
          "display" : 1,
          "moderate" : "default"
        }
      }
    ]
  }}');

  $rules['rules_voteup_answer'] = entity_import('rules_config', '{
    "rules_voteup_answer" : {
    "LABEL" : "voteup_answer",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "REQUIRES" : [ "rules", "userpoints_rules", "voting_rules" ],
    "ON" : { "voting_rules_insert_node" : [] },
    "IF" : [
      { "node_is_of_type" : {
          "node" : [ "node" ],
          "type" : { "value" : { "answers_answer" : "answers_answer" } }
        }
      },
      { "voting_rules_condition_check_vote_value" : { "vote" : [ "vote" ], "value" : "1" } }
    ],
    "DO" : [
      { "userpoints_action_grant_points" : {
          "user" : [ "node:author" ],
          "points" : "10",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "addPoints",
          "display" : 1,
          "moderate" : "default"
        }
     }
     ]
   }}');

  $rules['rules_voteup_answer_cancel'] = entity_import('rules_config', '{
    "rules_voteup_answer_cancel" : {
    "LABEL" : "voteup_answer_cancel",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "REQUIRES" : [ "rules", "userpoints_rules", "voting_rules" ],
    "ON" : { "voting_rules_delete_node" : [] },
    "IF" : [
      { "node_is_of_type" : {
          "node" : [ "node" ],
          "type" : { "value" : { "answers_answer" : "answers_answer" } }
        }
      },
      { "voting_rules_condition_check_vote_value" : { "vote" : [ "vote" ], "value" : "1" } }
    ],
    "DO" : [
      { "userpoints_action_grant_points" : {
          "user" : [ "node:author" ],
          "points" : "-10",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "addPoints",
          "display" : 1,
          "moderate" : "default"
        }
      }
    ]
  }}');

  $rules['rules_votedown_answer'] = entity_import('rules_config', '{
    "rules_votedown_answer" : {
    "LABEL" : "votedown_answer",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "REQUIRES" : [ "rules", "voting_rules", "userpoints_rules" ],
    "ON" : { "voting_rules_insert_node" : [] },
    "IF" : [
      { "node_is_of_type" : {
          "node" : [ "node" ],
          "type" : { "value" : { "answers_answer" : "answers_answer" } }
        }
      },
      { "voting_rules_condition_check_vote_value" : { "vote" : [ "vote" ], "value" : "-1" } }
    ],
    "DO" : [
      { "userpoints_action_grant_points" : {
          "user" : [ "vote:user" ],
          "points" : "-1",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "deletePoints",
          "display" : 1,
          "moderate" : "default"
        }
      },
      { "userpoints_action_grant_points" : {
          "user" : [ "node:author" ],
          "points" : "-2",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "deletePoints",
          "display" : 1,
          "moderate" : "default"
        }
      }
    ]
  }}');

  $rules['rules_votedown_answer_cancel'] = entity_import('rules_config', '{
    "rules_votedown_answer_cancel" : {
    "LABEL" : "votedown_answer_cancel",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "REQUIRES" : [ "rules", "voting_rules", "userpoints_rules" ],
    "ON" : { "voting_rules_delete_node" : [] },
    "IF" : [
      { "node_is_of_type" : {
          "node" : [ "node" ],
          "type" : { "value" : { "answers_answer" : "answers_answer" } }
        }
      },
      { "voting_rules_condition_check_vote_value" : { "vote" : [ "vote" ], "value" : "-1" } }
    ],
    "DO" : [
      { "userpoints_action_grant_points" : {
          "user" : [ "vote:user" ],
          "points" : "1",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "addPoints",
          "display" : 1,
          "moderate" : "default"
        }
      },
      { "userpoints_action_grant_points" : {
          "user" : [ "node:author" ],
          "points" : "2",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "addPoints",
          "display" : 1,
          "moderate" : "default"
        }
      }
    ]
  }}');

  $rules['rules_flag_answers_best_answer'] = entity_import('rules_config', '{
    "rules_flag_answers_best_answer" : {
    "LABEL" : "flag_answers_best_answer",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "TAGS" : [ "answers" ],
    "REQUIRES" : [ "rules", "userpoints_rules", "flag" ],
    "ON" : { "flag_flagged_best_answer" : [] },
    "IF" : [
      { "NOT data_is" : { "data" : [ "flagging-user" ], "value" : [ "flagged-node:author" ] } }
    ],
    "DO" : [
      { "userpoints_action_grant_points" : {
          "user" : [ "flagged-node:author" ],
          "points" : "15",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "addPoints",
          "display" : 1,
          "moderate" : "default"
        }
      },
      { "userpoints_action_grant_points" : {
          "user" : [ "flagging-user" ],
          "points" : "2",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "addPoints",
          "display" : 1,
          "moderate" : "default"
        }
      }
    ]
  }
}
  ');

  $rules['rules_unflag_answers_best_answer'] = entity_import('rules_config', '{
    "rules_unflag_answers_best_answer" : {
    "LABEL" : "unflag_answers_best_answer",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "TAGS" : [ "answers" ],
    "REQUIRES" : [ "rules", "userpoints_rules", "flag" ],
    "ON" : { "flag_unflagged_best_answer" : [] },
    "IF" : [
      { "NOT data_is" : { "data" : [ "flagging-user" ], "value" : [ "flagged-node:author" ] } }
    ],
    "DO" : [
      { "userpoints_action_grant_points" : {
          "user" : [ "flagged-node:author" ],
          "points" : "-15",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "addPoints",
          "display" : 1,
          "moderate" : "default"
        }
      },
      { "userpoints_action_grant_points" : {
          "user" : [ "flagging-user" ],
          "points" : "-2",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "removePoints",
          "display" : 1,
          "moderate" : "default"
        }
      }
    ]
  }
}
  ');

  $rules['answers_userpoints_flag_answers_own_best_answer'] = entity_import('rules_config', '{
    "answers_userpoints_flag_answers_own_best_answer" : {
    "LABEL" : "flag_answers_own_best_answer",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "TAGS" : [ "answers" ],
    "REQUIRES" : [ "rules", "userpoints_rules", "flag" ],
    "ON" : { "flag_flagged_best_answer" : [] },
    "IF" : [
      { "data_is" : { "data" : [ "flagging-user" ], "value" : [ "flagged-node:author" ] } }
    ],
    "DO" : [
      { "userpoints_action_grant_points" : {
          "user" : [ "flagging-user" ],
          "points" : "2",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "addPoints",
          "display" : 1,
          "moderate" : "default"
        }
      }
    ]
  }
}
  ');

  $rules['answers_userpoints_unflag_answers_own_best_answer'] = entity_import('rules_config', '{
    "answers_userpoints_unflag_answers_own_best_answer" : {
    "LABEL" : "unflag_answers_own_best_answer",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "TAGS" : [ "answers" ],
    "REQUIRES" : [ "rules", "userpoints_rules", "flag" ],
    "ON" : { "flag_unflagged_best_answer" : [] },
    "IF" : [
      { "data_is" : { "data" : [ "flagging-user" ], "value" : [ "flagged-node:author" ] } }
    ],
    "DO" : [
      { "userpoints_action_grant_points" : {
          "user" : [ "flagging-user" ],
          "points" : "-2",
          "tid" : "0",
          "entity" : [ "" ],
          "operation" : "removePoints",
          "display" : 1,
          "moderate" : "default"
        }
      }
    ]
  }
}
  ');

  return $rules;
}
