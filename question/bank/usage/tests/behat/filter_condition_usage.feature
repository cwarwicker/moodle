@qbank @qbank_usage @javascript
Feature: Last used filter condition
  As a teacher
  In order to organise my questions
  I want to filter the list of questions by the time and date they were last used in a quiz
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | student  | Student   | One      | student@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student  | C1     | student |
    And the following "activities" exist:
      | activity   | name    | intro              | course | idnumber |
      | qbank      | Qbank 1 | Question bank 1    | C1     | qbank1   |
      | quiz       | Quiz 1  | Quiz 1 description | C1     | quiz1    |
      | quiz       | Quiz 2  | Quiz 2 description | C1     | quiz2    |
    And the following "question categories" exist:
      | contextlevel    | reference | name           |
      | Activity module | qbank1    | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name    | questiontext    |
      | Test questions   | truefalse   | TF1   | First question  |
      | Test questions   | truefalse   | TF2   | Second question |
      | Test questions   | truefalse   | TF3   | Third question  |
      | Test questions   | truefalse   | TF4   | Fourth question  |
      | Test questions   | truefalse   | TF5   | Fifth question  |
      | Test questions   | truefalse   | TF6   | Sixth question  |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
      | TF3      | 1    |
      | TF5      | 1    |
    And quiz "Quiz 2" contains the following questions:
      | question | page |
      | TF2      | 1    |
      | TF4      | 1    |
      | TF6      | 1    |

  Scenario: Filter by question time last used when there have been no attempts
    Given I am on the "Qbank 1" "core_question > question bank" page logged in as "admin"
    And I add question bank filter "Time last attempted"
    And I set the field "Time last attempted before" to "## now ##%FT%R##"
    And I press "Apply filters"
    Then I should not see "TF1"
    And I should not see "TF2"
    And I should not see "TF3"
    And I should not see "TF4"
    And I should not see "TF5"
    And I should not see "TF6"

  Scenario: Filter by question time last used before when there have been attempts
    Given user "student" has attempted "Quiz 1" at "5 minutes ago" with responses:
      | slot | response |
      |   1  | True     |
      |   2  | False    |
      |   3  | True     |
    And I am on the "Qbank 1" "core_question > question bank" page logged in as "admin"
    And I add question bank filter "Time last attempted"
    And I set the field "Time last attempted before" to "## now ##%FT%R##"
    And I press "Apply filters"
    Then I should see "TF1"
    And I should see "TF3"
    And I should see "TF5"
    And I should not see "TF2"
    And I should not see "TF4"
    And I should not see "TF6"

  Scenario: Filter by question time last used after when there have been attempts
    Given user "student" has attempted "Quiz 1" with responses:
      | slot | response |
      |   1  | True     |
      |   2  | False    |
      |   3  | True     |
    And I am on the "Qbank 1" "core_question > question bank" page logged in as "admin"
    And I add question bank filter "Time last attempted"
    And I set the field "Select dates" to "After"
    And I set the field "Time last attempted after" to "## 5 minutes ago ##%FT%R##"
    And I press "Apply filters"
    Then I should see "TF1"
    And I should see "TF3"
    And I should see "TF5"
    And I should not see "TF2"
    And I should not see "TF4"
    And I should not see "TF6"

  Scenario: Filter by question time last used between when there have been attempts
    Given user "student" has attempted "Quiz 2" at "5 minutes ago" with responses:
      | slot | response |
      |   1  | True     |
      |   2  | False    |
      |   3  | True     |
    And I am on the "Qbank 1" "core_question > question bank" page logged in as "admin"
    And I add question bank filter "Time last attempted"
    And I set the field "Select dates" to "Between"
    And I set the field "Time last attempted after" to "## 10 minutes ago ##%FT%R##"
    And I set the field "Time last attempted before" to "## now ##%FT%R##"
    And I press "Apply filters"
    Then I should see "TF2"
    And I should see "TF4"
    And I should see "TF6"
    And I should not see "TF1"
    And I should not see "TF3"
    And I should not see "TF5"
