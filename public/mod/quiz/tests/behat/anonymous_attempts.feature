@mod @mod_quiz @mod_quiz_anonymous
Feature: Anonymous quiz attempts
  As a teacher I should not see student names on anonymous quizzes unless I have capability to do so

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | student  | Student   | One      | student@example.com |
      | teacher  | Teacher   | One      | teacher@example.com |
      | manager  | Manager   | One      | manager@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student  | C1     | student        |
      | teacher  | C1     | editingteacher |
      | manager  | C1     | manager        |
    And the following "activities" exist:
      | activity | name         | intro            | course | idnumber | grade | anonymous  | showuserpicture |
      | quiz     | Normal Quiz  | Quiz description | C1     | normal   | 100   | 0          |                 |
      | quiz     | Anon Quiz    | Quiz description | C1     | anon     | 100   | 1          |                 |
      | quiz     | Pix Quiz     | Quiz description | C1     | pix      | 100   | 1          | 1               |
      | qbank    | Qbank 1      | Question bank 1  | C1     | qbank1   |       |            |                 |
    And the following "question categories" exist:
      | contextlevel    | reference  | name           |
      | Activity module | qbank1     | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name  | questiontext    |
      | Test questions   | truefalse   | Q1    | First question  |
    And quiz "Normal Quiz" contains the following questions:
      | question | page | maxmark |
      | Q1       | 1    | 1.0     |
    And quiz "Anon Quiz" contains the following questions:
      | question | page | maxmark |
      | Q1       | 1    | 1.0     |
    And quiz "Pix Quiz" contains the following questions:
      | question | page | maxmark |
      | Q1       | 1    | 1.0     |
    And user "student" has attempted "Normal Quiz" with responses:
      | slot | response |
      | 1    | True     |
    And user "student" has attempted "Anon Quiz" with responses:
      | slot | response |
      | 1    | True     |
    And user "student" has attempted "Pix Quiz" with responses:
      | slot | response |
      | 1    | True     |

  Scenario: Teachers can see student names on non-anonymous quiz attempts
    Given I am on the "Normal Quiz" "mod_quiz > View" page logged in as "teacher"
    And I follow "Attempts: 1"
    Then I should see "Student One"

  Scenario: Teachers cannot see student names on anonymous quiz attempts
    Given I am on the "Anon Quiz" "mod_quiz > View" page logged in as "teacher"
    And I follow "Attempts: 1"
    Then I should not see "Student One"
    And I should see "Participant 1"

  Scenario: Managers can see student names on anonymous quiz attempts
    Given I am on the "Anon Quiz" "mod_quiz > View" page logged in as "manager"
    And I follow "Attempts: 1"
    Then I should see "Student One"

  Scenario: Teachers cannot see the student names on the review page of anonymous quiz attempts
    Given I am on the "Anon Quiz" "mod_quiz > View" page logged in as "teacher"
    And I follow "Attempts: 1"
    And I follow "Review attempt"
    Then I should not see "Student One"
    And I should see "Participant 1"

  Scenario: Managers can see the student names on the review page of anonymous quiz attempts
    Given I am on the "Anon Quiz" "mod_quiz > View" page logged in as "manager"
    And I follow "Attempts: 1"
    And I follow "Review attempt"
    Then I should see "Student One"

  Scenario: Teachers cannot see the student names on the review question page of anonymous quiz attempts
    Given I am on the "Anon Quiz" "mod_quiz > View" page logged in as "teacher"
    And I follow "Attempts: 1"
    And I follow "Review attempt"
    And I click on "//a[@title='Review response' and normalize-space()='1']" "xpath_element"
    Then I should not see "Student One"
    And I should see "Participant 1"

  Scenario: Managers can see the student names on the review question page of anonymous quiz attempts
    Given I am on the "Anon Quiz" "mod_quiz > View" page logged in as "manager"
    And I follow "Attempts: 1"
    And I follow "Review attempt"
    And I click on "//a[@title='Review response' and normalize-space()='1']" "xpath_element"
    Then I should see "Student One"

  Scenario: Teachers cannot see the student names on the comment page of anonymous quiz attempts
    Given I am on the "Anon Quiz" "mod_quiz > View" page logged in as "teacher"
    And I follow "Attempts: 1"
    And I follow "Review attempt"
    And I follow "Make comment or override mark"
    Then I should not see "Student One"
    And I should see "Participant 1"

  Scenario: Managers can see the student names on the comment page of anonymous quiz attempts
    Given I am on the "Anon Quiz" "mod_quiz > View" page logged in as "manager"
    And I follow "Attempts: 1"
    And I follow "Review attempt"
    And I follow "Make comment or override mark"
    Then I should see "Student One"

  Scenario: Teachers cannot see the student names in the navigation panel
    Given I am on the "Pix Quiz" "mod_quiz > View" page logged in as "teacher"
    And I follow "Attempts: 1"
    And I follow "Review attempt"
    Then I should not see "Student One"

  Scenario: Managers can see the student names in the navigation panel
    Given I am on the "Pix Quiz" "mod_quiz > View" page logged in as "manager"
    And I follow "Attempts: 1"
    And I follow "Review attempt"
    Then I should see "Student One"

  Scenario: Teachers cannot see the student names on the manual grading page
    Given the following config values are set as admin:
      | enablemyhome    | 1 |             |
      | enablemycourses | 1 |             |
      | unaddableblocks |   | theme_boost |
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Administration" block if not present
    And I configure the "Administration" block
    And I set the following fields to these values:
      | Page contexts | Display throughout the entire site |
    And I press "Save changes"
    And I am on the "Anon Quiz" "mod_quiz > View" page logged in as "teacher"
    And I follow "Attempts: 1"
    And I follow "Manual grading"
    And I follow "Also show questions that have been graded automatically"
    And I follow "grade all"
    Then I should not see "Student One"
    And I should see "Participant 1"

  Scenario: Managers can see the student names on the manual grading page
    Given the following config values are set as admin:
      | enablemyhome    | 1 |             |
      | enablemycourses | 1 |             |
      | unaddableblocks |   | theme_boost |
    And I log in as "admin"
    And I am on site homepage
    And I turn editing mode on
    And I add the "Administration" block if not present
    And I configure the "Administration" block
    And I set the following fields to these values:
      | Page contexts | Display throughout the entire site |
    And I press "Save changes"
    And I am on the "Anon Quiz" "mod_quiz > View" page logged in as "manager"
    And I follow "Attempts: 1"
    And I follow "Manual grading"
    And I follow "Also show questions that have been graded automatically"
    And I follow "grade all"
    Then I should see "Student One"

  @javascript
  Scenario: Releasing student identities reveals the student names
    Given I am on the "Anon Quiz" "mod_quiz > View" page logged in as "teacher"
    And I follow "Attempts: 1"
    Then I should not see "Student One"
    And I follow "Reveal student identities"
    And I press "Continue"
    And I should see "Grades have been released. Anonymous attempts are now disabled and any future attempts will be immediately added to Gradebook."
    And I should see "Student One"
