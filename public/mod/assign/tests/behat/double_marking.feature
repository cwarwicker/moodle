@mod @mod_assign @javascript
Feature: Complete double marking workflow
  In order to conduct an assignment with double marking
  As a teacher
  I need to:
    - Allocate markers to the student(s)
    - Allocate marks to the student(s) submission(s)
    - Calculate a final grade based on the configured double marking agreement method
    - Release the final grade to the student(s)

  Background:
    Given the following "users" exist:
      | username | firstname  | lastname | email                |
      | student1 | Student    | One      | student1@example.com |
      | student2 | Student    | Two      | student2@example.com |
      | student3 | Student    | Three    | student3@example.com |
      | student4 | Student    | Four     | student4@example.com |
      | teacher1 | Teacher    | One      | teacher1@example.com |
      | teacher2 | Teacher    | Two      | teacher2@example.com |
      | teacher3 | Teacher    | Three    | teacher3@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | editingteacher |
      | teacher3 | C1     | editingteacher |
    And the following "groups" exist:
      | name    | course | idnumber | participation |
      | Group 1 | C1     | G1       | 1             |
      | Group 2 | C1     | G2       | 1             |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1    |
      | student2 | G1    |
      | student3 | G2    |
      | student4 | G2    |
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
      | multimarkmethod          | average       |
      | multimarkrounding        | 1             |
      | grade[modgrade_type]     | point         |
      | grade[modgrade_point]    | 100           |
    And the following "activity" exists:
      | activity                 | assign        |
      | course                   | C1            |
      | idnumber                 | A2            |
      | name                     | Assignment 2  |
      | section                  | 1             |
      | completion               | 1             |
      | markingworkflow          | 1             |
      | markingallocation        | 1             |
      | markercount              | 2             |
      | multimarkmethod          | maximum       |
      | multimarkrounding        | 1             |
      | teamsubmission           | 1             |
      | grade[modgrade_type]     | point         |
      | grade[modgrade_point]    | 100           |

  Scenario: Complete workflow of double marking
    # Firstly, allocate the markers to the students.
    Given I am on the "A1" "assign activity" page logged in as teacher1
    When I navigate to "Submissions" in current page administration
    And I set the field "selectall" to "1"
    And I click on "Allocate marker" "button" in the "sticky-footer" "region"
    And I click on "Allocate marker" "button" in the ".modal-footer" "css_element"
    And I select "Teacher One" from the "Allocated marker 1" singleselect
    And I select "Teacher Two" from the "Allocated marker 2" singleselect
    And I press "Save changes"
    Then "Student One" row "Marker 1" column of "generaltable" table should contain "Teacher One"
    And "Student One" row "Marker 2" column of "generaltable" table should contain "Teacher Two"
    And "Student Two" row "Marker 1" column of "generaltable" table should contain "Teacher One"
    And "Student Two" row "Marker 2" column of "generaltable" table should contain "Teacher Two"
    # Then allocate marks to the student submissions as teacher1.
    When I go to "Student One" "Assignment 1" activity advanced marking page
    And I set the field "Mark out of 100" to "99"
    And I set the field "Marking workflow state" to "Marking completed"
    And I press "Save changes"
    And I go to "Student Two" "Assignment 1" activity advanced marking page
    And I set the field "Mark out of 100" to "11"
    And I set the field "Marking workflow state" to "Marking completed"
    And I press "Save changes"
    And I am on the "A1" "assign activity" page
    And I navigate to "Submissions" in current page administration
    Then "Student One" row "Marker 1" column of "generaltable" table should contain "99"
    And "Student Two" row "Marker 1" column of "generaltable" table should contain "11"
    And "Student One" row "Status" column of "generaltable" table should contain "In marking"
    And "Student Two" row "Status" column of "generaltable" table should contain "In marking"
    # Then allocate marks to the student submissions as teacher2.
    When I am on the "A1" "assign activity" page logged in as teacher2
    And I go to "Student One" "Assignment 1" activity advanced marking page
    And I set the field "Mark out of 100" to "88"
    And I set the field "Marking workflow state" to "Marking completed"
    And I press "Save changes"
    And I go to "Student Two" "Assignment 1" activity advanced marking page
    And I set the field "Mark out of 100" to "22"
    And I set the field "Marking workflow state" to "Marking completed"
    And I press "Save changes"
    And I am on the "A1" "assign activity" page
    And I navigate to "Submissions" in current page administration
    Then "Student One" row "Marker 2" column of "generaltable" table should contain "88"
    And "Student Two" row "Marker 2" column of "generaltable" table should contain "22"
    And "Student One" row "Status" column of "generaltable" table should contain "Marking completed"
    And "Student Two" row "Status" column of "generaltable" table should contain "Marking completed"
    And "Student One" row "Grade" column of "generaltable" table should contain "94"
    And "Student Two" row "Grade" column of "generaltable" table should contain "17"
    # Then we check the calculated final grade and release them to the students.
    When I am on the "A1" "assign activity" page logged in as teacher1
    And I navigate to "Submissions" in current page administration
    And I set the field "selectall" to "1"
    And I click on "Change marking state" "button" in the "sticky-footer" "region"
    And I click on "Change marking state" "button" in the "Set marking workflow state" "dialogue"
    And I set the field "Workflow context" to "Grade"
    And I set the field "Marking workflow state" to "Released"
    And I press "Save changes"
    Then "Student One" row "Status" column of "generaltable" table should contain "Released"
    And "Student Two" row "Status" column of "generaltable" table should contain "Released"
    And "Student One" row "Final grade" column of "generaltable" table should contain "94"
    And "Student Two" row "Final grade" column of "generaltable" table should contain "17"

  Scenario: Complete workflow of double marking with group submissions
    # Firstly, allocate the markers to the students (to test thoroughly, one group will have the same marker
    # for both students. The other group won't).
    Given I am on the "A2" "assign activity" page logged in as teacher1
    When I navigate to "Submissions" in current page administration
    And I click on "Quick grading" "checkbox"
    And I set the field "Allocated marker 1" in the "Student One" "table_row" to "Teacher One"
    And I set the field "Allocated marker 1" in the "Student Two" "table_row" to "Teacher One"
    And I set the field "Allocated marker 1" in the "Student Three" "table_row" to "Teacher One"
    And I set the field "Allocated marker 1" in the "Student Four" "table_row" to "Teacher One"
    And I set the field "Allocated marker 2" in the "Student One" "table_row" to "Teacher Two"
    And I set the field "Allocated marker 2" in the "Student Two" "table_row" to "Teacher Two"
    And I set the field "Allocated marker 2" in the "Student Three" "table_row" to "Teacher Two"
    And I set the field "Allocated marker 2" in the "Student Four" "table_row" to "Teacher Three"
    And I click on "Save" "button" in the "sticky-footer" "region"
    And I press "Continue"
    And I click on "Quick grading" "checkbox"
    Then "Student One" row "Marker 1" column of "generaltable" table should contain "Teacher One"
    And "Student One" row "Marker 2" column of "generaltable" table should contain "Teacher Two"
    And "Student Two" row "Marker 1" column of "generaltable" table should contain "Teacher One"
    And "Student Two" row "Marker 2" column of "generaltable" table should contain "Teacher Two"
    And "Student Three" row "Marker 1" column of "generaltable" table should contain "Teacher One"
    And "Student Three" row "Marker 2" column of "generaltable" table should contain "Teacher Two"
    And "Student Four" row "Marker 1" column of "generaltable" table should contain "Teacher One"
    And "Student Four" row "Marker 2" column of "generaltable" table should contain "Teacher Three"
    # Next we test adding a mark as Marker 1 (teacher1) to a student in Group 1. This should populate to the other
    # student in Group 1, but not the student with this same marker, who is not in Group 1.
    When I go to "Student One" "Assignment 2" activity advanced marking page
    And I set the field "Mark out of 100" to "50"
    And I set the field "Marking workflow state" to "Marking completed"
    And I press "Save changes"
    And I go to "Student Four" "Assignment 2" activity advanced marking page
    And I set the field "Mark out of 100" to "60"
    And I set the field "Marking workflow state" to "Marking completed"
    And I press "Save changes"
    And I am on the "A2" "assign activity" page
    And I navigate to "Submissions" in current page administration
    Then "Student One" row "Marker 1" column of "generaltable" table should contain "50"
    And "Student Two" row "Marker 1" column of "generaltable" table should contain "50"
    And "Student Three" row "Marker 1" column of "generaltable" table should contain "60"
    And "Student Four" row "Marker 1" column of "generaltable" table should contain "60"
    # Next we add a mark as teacher2 to a student in both groups. The mark given to the student in Group 1 should
    # populate to the other student in Group 1. The mark given to the student in Group 2 should not, as they are not
    # an allocated marker for that final student.
    When I am on the "A2" "assign activity" page logged in as teacher2
    And I go to "Student One" "Assignment 2" activity advanced marking page
    And I set the field "Mark out of 100" to "30"
    And I set the field "Marking workflow state" to "Marking completed"
    And I press "Save changes"
    And I am on the "A2" "assign activity" page
    And I go to "Student Three" "Assignment 2" activity advanced marking page
    And I set the field "Mark out of 100" to "15"
    And I set the field "Marking workflow state" to "Marking completed"
    And I press "Save changes"
    And I am on the "A2" "assign activity" page
    And I navigate to "Submissions" in current page administration
    Then "Student One" row "Marker 2" column of "generaltable" table should contain "30"
    And "Student Two" row "Marker 2" column of "generaltable" table should contain "30"
    And "Student Three" row "Marker 2" column of "generaltable" table should contain "15"
    And "Student Four" row "Marker 2" column of "generaltable" table should contain ""
    # Then we login as teacher3 and add that last mark to the final student.
    When I am on the "A2" "assign activity" page logged in as teacher3
    And I go to "Student Four" "Assignment 2" activity advanced marking page
    And I set the field "Mark out of 100" to "99"
    And I set the field "Marking workflow state" to "Marking completed"
    And I press "Save changes"
    And I am on the "A2" "assign activity" page
    And I navigate to "Submissions" in current page administration
    Then "Student One" row "Marker 2" column of "generaltable" table should contain "30"
    And "Student Two" row "Marker 2" column of "generaltable" table should contain "30"
    And "Student Three" row "Marker 2" column of "generaltable" table should contain "15"
    And "Student Four" row "Marker 2" column of "generaltable" table should contain "100"
    # Then we check that the grades have been calculated correctly.
    When I am on the "A2" "assign activity" page logged in as teacher1
    And I navigate to "Submissions" in current page administration
    Then "Student One" row "Grade" column of "generaltable" table should contain "50"
    Then "Student Two" row "Grade" column of "generaltable" table should contain "50"
    Then "Student Three" row "Grade" column of "generaltable" table should contain "60"
    Then "Student Four" row "Grade" column of "generaltable" table should contain "99"
    # Then we release them all and check the final grade column.
    When I am on the "A2" "assign activity" page logged in as teacher1
    And I navigate to "Submissions" in current page administration
    And I set the field "selectall" to "1"
    And I click on "Change marking state" "button" in the "sticky-footer" "region"
    And I click on "Change marking state" "button" in the "Set marking workflow state" "dialogue"
    And I set the field "Workflow context" to "Grade"
    And I set the field "Marking workflow state" to "Released"
    And I press "Save changes"
    Then "Student One" row "Status" column of "generaltable" table should contain "Released"
    And "Student Two" row "Status" column of "generaltable" table should contain "Released"
    And "Student Three" row "Status" column of "generaltable" table should contain "Released"
    And "Student Four" row "Status" column of "generaltable" table should contain "Released"
    And "Student One" row "Final grade" column of "generaltable" table should contain "50"
    And "Student Two" row "Final grade" column of "generaltable" table should contain "50"
    And "Student Three" row "Final grade" column of "generaltable" table should contain "60"
    And "Student Four" row "Final grade" column of "generaltable" table should contain "99"
