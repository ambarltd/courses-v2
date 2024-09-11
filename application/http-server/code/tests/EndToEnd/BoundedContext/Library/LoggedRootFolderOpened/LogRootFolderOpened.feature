Feature: Library - Log Root Folder Opened
  As an existing user to the system
  I would like to log the times I'm opening my root folder
  So that I can analyze my usage at a later date

  Background:
    Given I am signed in with a verified email

  Scenario: I should not be able to log that I have opened my root folder folder, if it has no children
    When I request to log that I have opened my root folder
    Then my request should be successful

  Scenario: I should not be able to log that I have opened my root folder folder, if it has a great great grandchild
    Given I have created a folder with name GreatGrandParent
    And I have created a folder with name GrandParent under folder GreatGrandParent
    And I have created a folder with name Parent under folder GrandParent
    And I have created a folder with name Child under Parent
    When I request to log that I have opened my root folder
    Then my request should be successful
