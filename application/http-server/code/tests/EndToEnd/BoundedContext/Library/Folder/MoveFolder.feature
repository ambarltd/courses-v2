Feature: Library - Move Folder
  As an existing user to the system
  I would like to move my folders
  So that I can keep my files organized

  # Untested scenarios
  # I should not be able to move a folder to the top level folder if there are already 65530 top level folders
  # I should not be able to move a folder to another folder containing 65530 children
  # I should not be able to move a folder to another folder that has more than 60 ancestors

  Background:
    Given I am signed in with a verified email

  Scenario: I should be able to move a folder to another folder
    Given I have created folders with names MovingFolderA and MovingFolderB
    When I request to move MovingFolderA to MovingFolderB
    Then my request should be successful

  Scenario: I should not be able to move a deleted folder to another folder
    Given I have created folders with names MovingFolderA and MovingFolderB
    And I have deleted MovingFolderA
    When I request to move MovingFolderA to MovingFolderB
    Then my request should fail because "the moved folder is deleted"

  Scenario: I should not be able to move a folder to a deleted folder
    Given I have created folders with names MovingFolderA and MovingFolderB
    And I have deleted MovingFolderB
    When I request to move MovingFolderA to MovingFolderB
    Then my request should fail because "the destination folder is deleted"

  Scenario: I should not be able to move a deleted folder to be a top level folder
    Given I have created folders with names MovingFolderA and MovingFolderB
    And I have deleted MovingFolderA
    When I request to move MovingFolderA to be a top level folder
    Then my request should fail because "the moved folder is deleted"

  Scenario: I should not be able to move a folder to another folder with a deleted parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder Parent
    And I have created a folder with name MovingFolderC
    When I request to move MovingFolderC to Child
    Then my request should fail because "the destination folder is deleted"

  Scenario: I should not be able to move a folder to another folder with a deleted grand parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder GrandParent
    And I have created a folder with name MovingFolderC
    When I request to move MovingFolderC to Child
    Then my request should fail because "the destination folder is deleted"

  Scenario: I should not be able to move a folder to another folder with a deleted great grand parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    And I have deleted folder GreatGrandParent
    And I have created a folder with name MovingFolderC
    When I request to move MovingFolderC to Child
    Then my request should fail because "the destination folder is deleted"

  Scenario: I should not be able to move a folder to its great grand child
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to move GreatGrandParent to Child
    Then my request should fail because "the moved folder is an ancestor of the destination folder"

  Scenario: I should not be able to move a folder to its grand child
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to move GrandParent to Child
    Then my request should fail because "the moved folder is an ancestor of the destination folder"

  Scenario: I should not be able to move a folder to its child
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to move Parent to Child
    Then my request should fail because "the moved folder is an ancestor of the destination folder"

  Scenario: I should be able to move a folder to be a top level folder
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to move Child to be a top level folder
    Then my request should be successful

  Scenario: I should be able to move a folder to its great grand parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to move Child to GreatGrandParent
    Then my request should be successful

  Scenario: I should be able to move a folder to its grand parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to move Child to GrandParent
    Then my request should be successful

  Scenario: I should be able to move a folder to its parent
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to move Child to Parent
    Then my request should be successful

  Scenario: I should not be able to move my folder to another user's folder
    Given another user has created a folder with name FolderBelongingToAnotherUser
    And I have created a folder with name MovingFolderD
    When I request to move MovingFolderD to FolderBelongingToAnotherUser
    Then my request should fail because "I don't own the destination folder"

  Scenario: I should not be able to move another user's folder to my folder
    Given another user has created a folder with name FolderBelongingToAnotherUser
    And I have created a folder with name MovingFolderD
    When I request to move FolderBelongingToAnotherUser to MovingFolderD
    Then my request should fail because "I don't own the moved folder"




