Feature: Library - Create Folder
  As an existing user to the system
  I would like to create folders in my library
  So that I can organize my files

  # Untested scenarios
  # I should not be able to create a top level folder if there are already 65530 other top level folders
  # I should not be able to create a folder under a parent with 65530 children
  # I should not be able to create a folder with more than 60 ancestors

  Background:
    Given I am signed in with a verified email

  Scenario Outline: I should be able to create a folder with valid names
    When I request to create a folder with name <name>
    Then my request should be successful
    Examples:
      | name                              |
      | "Folder Name"                     |
      | "This is a valid Folder Name"     |
      | "Folder.Name"                     |
      | "*"                               |
      | "/"                               |
      | "."                               |
      | "1"                               |
      | "a"                               |
      | "1234"                            |
      | "1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456"    |

  Scenario Outline: I should not be able to create a folder with an invalid name
    When I request to create a folder with name <name>
    Then my request should fail because "the created folder's name is invalid"

    Examples:
      | name     |
      | ""       |
      | " "      |
      | "  "     |
      | "   "    |
      | "12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567"    |

  Scenario: I should not be able to create a folder without providing a name
    When I request to create a folder without a name
    Then my request should fail because "the created folder's name is required"

  Scenario: I should not be able to create a folder under a deleted great grand parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder GreatGrandParent
    When I request to create a folder under Parent
    Then my request should fail because "the parent of the created folder is deleted"

  Scenario: I should not be able to create a folder under a deleted grand parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder GrandParent
    When I request to create a folder under Parent
    Then my request should fail because "the parent of the created folder is deleted"

  Scenario: I should not be able to create a folder under a deleted parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder Parent
    When I request to create a folder under Parent
    Then my request should fail because "the parent of the created folder is deleted"

  Scenario: I should be able to create a folder with a deleted sibling
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder Child
    When I request to create a folder under Parent
    Then my request should be successful

  Scenario: I should not be able to create a folder under another user's folder
    Given another user has created a folder with name FolderBelongingToAnotherUser
    When I request to create a folder under FolderBelongingToAnotherUser
    Then my request should fail because "I don't own the parent FolderBelongingToAnotherUser"
