Feature: Library - Open Folder
  As an existing user to the system
  I would like to log the times I'm opening a folder
  So that I can analyze my usage at a later date

  Background:
    Given I am signed in with a verified email

  Scenario: I should not be able to log that I have opened a folder which is already deleted
    Given I have created and deleted a folder with name DeletedFolder
    When I request to log that I have opened folder DeletedFolder
    Then my request should fail because "the opened folder is deleted"

  Scenario: I should be able to log that I have opened a folder with a great grand child and no parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to log that I have opened folder GreatGrandParent
    Then my request should be successful

  Scenario: I should be able to log that I have opened a folder with a grand child
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to log that I have opened folder GrandParent
    Then my request should be successful

  Scenario: I should be able to log that I have opened a folder with a child
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to log that I have opened folder Parent
    Then my request should be successful

  Scenario: I should be able to log that I have opened a folder with a parent and no child
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to log that I have opened folder Child
    Then my request should be successful

  Scenario: I should not be able to log that I have opened a folder under a deleted great grand parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder GreatGrandParent
    When I request to log that I have opened folder Child
    Then my request should fail because "the opened folder is deleted"

  Scenario: I should not be able to log that I have opened a folder under a deleted grand parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder GrandParent
    When I request to log that I have opened folder Child
    Then my request should fail because "the opened folder is deleted"

  Scenario: I should not be able to log that I have opened a folder under a deleted parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder Parent
    When I request to log that I have opened folder Child
    Then my request should fail because "the opened folder is deleted"

  Scenario: I should be able to log that I have opened a folder with a deleted child
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder Child
    When I request to log that I have opened folder Parent
    Then my request should be successful

  Scenario: I should not be able to log that I have opened another user's folder
    Given another user has created a folder with name FolderBelongingToAnotherUser
    When I request to log that I have opened folder FolderBelongingToAnotherUser
    Then my request should fail because "I don't own the opened folder FolderBelongingToAnotherUser"