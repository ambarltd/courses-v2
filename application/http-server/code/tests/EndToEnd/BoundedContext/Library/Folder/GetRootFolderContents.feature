Feature: Library - Get Folder Contents
  As an existing user to the system
  I would like to be able to get the contents of my root folder
  So that I can find obtain information about folders and files I care about

  Background:
    Given I am signed in with a verified email

  Scenario: I should be able to obtain information about my root folder with children
    Given I have created two folders under my root folder with names Art and Science
    When I request to get the contents of my root folder
    Then I should obtain my root folder with children Art and Science

  Scenario: I should be able to obtain information about my root folder without children
    When I request to get the contents of my empty root folder
    Then I should obtain my root folder without children

  Scenario: I should be able to obtain updated information about my root folder's children once a child has been renamed
    Given I have created two folders under my root folder with names Art and Science
    And I have renamed Science to ScienceSubject
    When I request to get the contents of my root folder, with renamed folder Science
    Then I should obtain my root folder with children Art and ScienceSubject

  Scenario: I should be able to obtain updated information about my root folder's children once a child has been moved
    Given I have created two folders under my root folder with names Art and Science
    And I have moved Science under Art
    When I request to get the contents of my root folder
    Then I should obtain my root folder with child Art

  Scenario: I should be able to obtain updated information about my root folder's children once a child has been deleted
    Given I have created two folders under my root folder with names Art and Science
    And I have deleted Science
    When I request to get the contents of my root folder
    Then I should obtain my root folder with child Art

  Scenario: I should not be able to obtain information about another user's root folder
    Given another user has created a folder with name FolderBelongingToAnotherUser
    When I request to get the contents of my empty root folder
    Then I should obtain my root folder without children
