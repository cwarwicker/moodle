@mod @mod_assign
Feature: Allocate marks to student submissions
  In order to assess a submission with multiple markers
  As a teacher
  I need to allocate marks to student submissions

Background:
  Given the following "users" exist:
    | username | firstname  | lastname | email                |
    | student1 | Student    | One      | student1@example.com |
    | student2 | Student    | Two      | student1@example.com |
    | teacher1 | Teacher    | One      | teacher1@example.com |
    | teacher2 | Teacher    | Two      | teacher1@example.com |
  And the following "courses" exist:
    | fullname | shortname | enablecompletion | showcompletionconditions |
    | Course 1 | C1        | 1                | 1                        |
  And the following "course enrolments" exist:
    | user     | course | role           |
    | student1 | C1     | student        |
    | student2 | C1     | student |
    | teacher1 | C1     | editingteacher |
    | teacher2 | C1     | editingteacher |
  And the following "activity" exists:
    | activity                 | assign        |
    | course                   | C1            |
    | idnumber                 | A1            |
    | name                     | Assignment 1  |
    | section                  | 1             |
    | completion               | 1             |
    | markingworkflow          | 1             |
    | markingallocation        | 1             |
    | markercount              | 2             |
    | grade[modgrade_type]     | point         |
    | grade[modgrade_point]    | 100           |
  And the following "mod_assign > marker_allocations" exist:
    | assign       | user          | marker      |
    | Assignment 1 | student1      | teacher1    |
    | Assignment 1 | student1      | teacher2    |


  # Allocate marks via quick grading
  @javascript
  Scenario: Allocating marks to students via the Quick Grading page
    Given I am on the "A1" "assign activity" page logged in as teacher1
    And I navigate to "Submissions" in current page administration
    And I click on "Quick grading" "checkbox"
    And I set the field "User mark" in the "Student One" "table_row" to "100"
    And I click on "Save" "button" in the "sticky-footer" "region"
    And I press "Continue"
    And I click on "Quick grading" "checkbox"
    Then "Student One" row "Marker 1" column of "generaltable" table should contain "100"

  # Allocate marks via marker window
  @javascript
  Scenario: Allocating marks to students via the advanced marker window
    Given I am on the "A1" "assign activity" page logged in as teacher1
    And I go to "Student One" "Assignment 1" activity advanced marking page
    And I set the field "Mark out of 100" to "50"
    And I press "Save changes"
    And I am on the "A1" "assign activity" page
    And I navigate to "Submissions" in current page administration
    Then "Student One" row "Marker 1" column of "generaltable" table should contain "50"

  # Confirm can't use mark allocation with rubrics (yet)

  # groups?
  #workflow