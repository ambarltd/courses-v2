Feature: Library - Get Folder Contents
  As an existing user to the system
  I would like to be able to get the contents of my folders
  So that I see the folders and files inside of them

  Background:
    Given I am signed in with a verified email
    And I have created two folders under my root folder with names Music and Images
    And I have created two folders under Music with names Jazz and Rock

  Scenario: I should be able to obtain information about a folder without a parent and with children
    When I request to get the contents of Music
    Then I should obtain Music without a parent and with children Jazz and Rock

  Scenario: I should be able to obtain information about a folder without a parent and without children
    When I request to get the contents of Images
    Then I should obtain Images without a parent and without children

  Scenario: I should be able to obtain information about a folder with a parent and with children
    Given I have created two folders Classic and HeavyMetal under Rock
    When I request to get the contents of Rock
    Then I should obtain Rock with parent Music and children Classic and HeavyMetal

  Scenario: I should be able to obtain information about a folder with a parent and without children
    When I request to get the contents of Jazz
    Then I should obtain Jazz with parent Music and without children

  Scenario: I should be able to obtain updated information about a folder's children once a child has been moved
    Given I have created two folders Classic and HeavyMetal under Rock
    And I have moved Classic under Jazz
    When I request to get the contents of Rock
    Then I should obtain Rock with parent Music and child HeavyMetal

  Scenario: I should be able to obtain updated information about a folder's parent once it has been moved
    Given I have created two folders Classic and HeavyMetal under Rock
    And I have moved Classic under Jazz
    When I request to get the contents of Classic
    Then I should obtain Classic with parent Jazz and without children

  Scenario: I should be able to obtain updated information about a folder's children once a child has been deleted
    Given I have created two folders Classic and HeavyMetal under Rock
    And I have deleted Classic
    When I request to get the contents of Rock
    Then I should obtain Rock with parent Music and child HeavyMetal

  Scenario: I should not be able to obtain information about a deleted folder
    Given I have deleted Music
    When I request to get the contents of Music
    Then my request should fail because "the contents of this folder are deleted"

  Scenario: I should not be able to obtain information about a folder with a deleted parent
    Given I have deleted Music
    When I request to get the contents of Jazz
    Then my request should fail because "the contents of this folder are deleted"

  Scenario: I should be able to obtain information about a renamed folder
    Given I have created a folder with name Space under Images
    And I have renamed Space to Astronomy
    When I request to get the contents of Astronomy
    Then I should obtain Astronomy with its updated name

  Scenario: I should be able to obtain information about a folder's children once a child has been renamed
    Given I have created a folder with name Space under Images
    And I have renamed Space to Astronomy
    When I request to get the contents of Images, with renamed folder Space
    Then I should obtain Images without a parent and with child Astronomy

  Scenario: I should not be able to obtain information about someone else's folder
    Given another user has created a folder with name MusicBelongingToAnotherUser
    When I request to get the contents of MusicBelongingToAnotherUser
    Then my request should fail because "the contents of this folder are not mine"
