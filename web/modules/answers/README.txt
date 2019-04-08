DESCRIPTION
============

This file gives a brief guide to the Answers Module, which provides a
question-answer service.

HOW IT WORKS
============

This module defines two content types, questions and answers related to
questions.

It defines views and page displays to work with them.

It offers a series of submodules that add:
- Answers_Notifications: Subscribing to questions and getting emails when new
answers are added
- Answers_Userpoints: Point system for questions and answers (via userpoints and
rules)
- Answers_Voting: Voting on questions and answers
- Best_Answer: Flagging of the best answer for a question (which is then shown
first)

TYPES OF USERS
============

There are 6 relevant roles (these are not drupal system roles but rather types
of users for the module):
1  Viewers: Those who view questions and answers
2  Question Authors: Those who ask a question.
3  Answer Authors: Those who answer a question
4  Site Administrator: Those who have the "administer settings" privilege and
who can set up the module
5  Site Developer: Those who code sites using modules
6  Module Developers: Those who want to create modules using the Answers
functionality

What the module does:
•  Question Authors can create new questions by going to the path
'node/add/question' (in this approach, users are taken directly to the new
question form)
•  Viewers can review a list of all questions. This is available at the path
'questions/'
•  When viewing the list of questions, Viewers can sort by author, title,
post date, and number of answers. If answers_voting is implemented, viewers
can sort by votes.
•  When displaying a question, the system shows all the answers to that question
•  When deleting a question, the system also deletes all of the answers to it.

Features that are coming (but have not yet be re-implemented in 7.x-4.x)
•  Question Authors can create new questions by going to the path
'questions/start_ask' (in this approach, users are asked to review questions
that match theirs first)
•  Viewers can review a list of questions that match a search query. This is
available at the path 'questions/search' [NOT YET RE-IMPLEMENTED]
•  Viewers can also see lists of questions they asked and they answered

HISTORY & KEY CONTRIBUTORS
============================

This module is based on:
•  The original D5 "answers" module written by Amanuel Tewolde.
•  The original D6 conversion of that module written by Inders Singh
•  The original features version of the module written by Greg Harvey
•  An updated features version of the module was written in 2011 by Chip Cleary.
•  Module reengineered (features removed) in Dec 2012 by Alessandro Mascherpa.
