Feature: Library - Rename Folder
  As an existing user to the system
  I would like to rename folders
  So that I can keep my files organized

  Background:
    Given I am signed in with a verified email

  Scenario Outline: I should be able to rename a folder to a valid name
    Given I have created a folder named FolderToBeRenamed
    When I request to rename FolderToBeRenamed to <name>
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

  Scenario Outline: I should not e able to rename a folder to an invalid name
    Given I have created a folder named FolderToBeRenamed
    When I request to rename FolderToBeRenamed to <name>
    Then my request should fail because "the renamed folder's name is invalid"

    Examples:
      | name     |
      | ""       |
      | " "      |
      | "  "     |
      | "   "    |
      | "12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567"    |

  Scenario: I should not be able to rename a folder without providing a name
    Given I have created a folder named FolderToBeRenamed
    When I request to rename FolderToBeRenamed without a new name
    Then my request should fail because "the renamed folder's name is required"

  Scenario: I should not be able to rename a deleted folder
    Given I have created and deleted a folder with name DeletedFolder
    When I request to rename DeletedFolder to DeletedFolderNewName
    Then my request should fail because "the renamed folder is deleted"

  Scenario: I should be able to rename a folder with a great grand child and no parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to rename GreatGrandParent to GreatGrandParentRenamed
    Then my request should be successful

  Scenario: I should be able to rename a folder with a grand child
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to rename GrandParent to GrandParentRenamed
    Then my request should be successful

  Scenario: I should be able to rename a folder with a child
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to rename Parent to ParentRenamed
    Then my request should be successful

  Scenario: I should be able to rename a folder with a parent and no child
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to rename Child to ChildRenamed
    Then my request should be successful

  Scenario: I should not be able to rename a folder with a deleted great grand parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder GreatGrandParent
    When I request to rename Child to ChildRenamed
    Then my request should fail because "the renamed folder is deleted"

  Scenario: I should not be able to rename a folder with a deleted grand parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder GrandParent
    When I request to rename Child to ChildRenamed
    Then my request should fail because "the renamed folder is deleted"

  Scenario: I should not be able to rename a folder with a deleted parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder Parent
    When I request to rename Child to ChildRenamed
    Then my request should fail because "the renamed folder is deleted"

  Scenario: I should be able to rename a folder with a deleted parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder Child
    When I request to rename Parent to ParentRenamed
    Then my request should be successful

  Scenario: I should not be able to rename another user's folder
    Given another user has created a folder with name FolderBelongingToAnotherUser
    When I request to rename FolderBelongingToAnotherUser to FolderBelongingToAnotherUserRenamed
