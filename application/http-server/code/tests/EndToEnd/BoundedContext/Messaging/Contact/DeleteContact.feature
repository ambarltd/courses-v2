Feature: Contact - Delete Contact
  As an existing user to the system
  I would like to delete an active contact
  So that I can prevent further messaging
  
  Background:
    Given I am signed in with a verified email
    And JohnDoe is also an existing user

  Scenario: a user should not be able to delete me as a contact, if we have never been contacts
    When JohnDoe requests to delete me as a contact
    Then his request should fail because "he cannot delete as a contact, someone who has never been a contact"

  Scenario: I should be able to delete an existing contact
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    When I request to delete JohnDoe as a contact
    Then my request should be successful

  Scenario: I should not be able to delete a contact after it cancelled its contact request
    Given JohnDoe has requested to be my contact
    And JohnDoe has cancelled his contact request
    When I request to delete JohnDoe as a contact
    Then my request should fail because "I cannot delete a contact after it cancelled its contact request"

  Scenario: I should not be able to delete a contact that has already been deleted
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    And I deleted JohnDoe as a contact
    When I request to delete JohnDoe as a contact
    Then my request should fail because "I cannot delete a contact that has already been deleted"

  Scenario: I should not be able to delete a contact that has already been deleted
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    And JohnDoe deleted me as a contact
    When I request to delete JohnDoe as a contact
    Then my request should fail because "I cannot delete a contact that has deleted me as a contact"

  Scenario: I should not be able to delete a contact after I rejected its contact request
    Given JohnDoe has requested to be my contact
    And I rejected JohnDoe's contact request
    When I request to delete JohnDoe as a contact
    Then my request should fail because "I cannot delete a contact after I rejected its contact request"

  Scenario: I should not be able to delete a contact with a pending contact request
    Given JohnDoe has requested to be my contact
    When I request to delete JohnDoe as a contact
    Then my request should fail because "I cannot delete a contact with a pending contact request"
