
CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Troubleshooting
 * Credits


INTRODUCTION
------------

Entity Questions and Answers is a module which allows for the use of user
powered questions and answers on entities of any kind. The entities are
implemented as fields which can be added to any entity type. Once added,
users with appropriate permissions can ask and answer questions on those
entities. Email alerts and voting systems are available for these questions
and answers as well.
 * For a full description of the module, visit the project page:
   https://drupal.org/project/questions_answers


REQUIREMENTS
------------

This module requires the following modules:
 * Entity Reference (Drupal core)
 * User (Drupal core)


INSTALLATION
------------

Install as you would normally install a contributed Drupal module. Visit:
https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
for further information.


CONFIGURATION
-------------

 * Configure user permissions in Administration » People » Permissions:

   - Administer Questions and Answers

     The top-level administration categories require this permission to be
     accessible. This allows users to edit, approve, and moderate questions
     and answers.

  - Ask Questions and Answers

    This allows users to post new questions.

  - Answer Questions and Answers

    This allows users to post answer responses to existing questions.

  - Report Questions and Answers

    This allows users to flag questions and answers as inappropriate ("report"
    them) for review by administrators.

  - Subscribe to Questions and Answers

    This allows users to subscribe to asked questions. Subscribing to a
    question causes a user to receive a notification email when the question
    receives an answer.

* Customize Questions and Answers in Content » Questions and Answers » Questions
   and Answers Settings. This screen sets custom global settings for the module.

* Add Questions and Answers fields to entities in entity admin pages, such as
  Structure » Content Types » Manage Fields. This allows users to ask and answer
  questions about that entity type. Configure the questions and answers fields
  after adding them to the entity under Manage Display.

* Grant staff roles to staff members under People. Granting the staff role
  provides custom user flair to these users when posting answers. This allows
  visitors to see that an answer comes from an official source.

* Add the moderation block under Structure » Block Layout. This block provides
  information on the current number of questions and answers awaiting
  moderation as well as links to the moderation queue.


TROUBLESHOOTING
---------------

* If you do not see questions and answers on an entity after installing the
  module, check the following:

    - Have you added the Questions and Answers field type to the entity?

    - Did you set the cardinality of the field too low (Limited values)?


CREDITS
-------

* Daniel Moberly https://drupal.org/u/danielmoberly
