Feature: Contact - Accept Contact Request
  As an existing user to the system
  I would like to accept a contact request from another user
  So that we can message each other

  Background:
    Given I am signed in with a verified email
    And JohnDoe is also an existing user

  Scenario: a user should not be able to accept a contact request, if no contact request has been made
    When JohnDoe requests to accept my contact request
    Then his request should fail because "he cannot accept a contact request that has not been made"

  Scenario: a user should not be able to accept their own contact request to another user
    Given JohnDoe has requested to be my contact
    When JohnDoe requests to accept my contact request
    Then his request should fail because "he cannot accept a contact request he requested"

  Scenario: I should not be able to accept a contact request from an already active contact
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    When I request to accept JohnDoe's contact request
    Then my request should fail because "I cannot accept a contact request from an already active contact"

  Scenario: I should not be able to accept a contact request after cancelling the contact request
    Given JohnDoe has requested to be my contact
    And JohnDoe has cancelled his contact request
    When I request to accept JohnDoe's contact request
    Then my request should fail because "I cannot accept a contact request after cancelling the contact request"

  Scenario: I should not be able to accept a contact request after having deleted the contact
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    And I deleted JohnDoe as a contact
    When I request to accept JohnDoe's contact request
    Then my request should fail because "I cannot accept a contact request after having deleted the contact"

  Scenario: I should not be able to accept a contact request after being deleted by that contact
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    And JohnDoe deleted me as a contact
    When I request to accept JohnDoe's contact request
    Then my request should fail because "I cannot accept a contact request after being deleted by that contact"

  Scenario: I should not be able to accept a contact request after the request was rejected
    Given JohnDoe has requested to be my contact
    And I rejected JohnDoe's contact request
    When I request to accept JohnDoe's contact request
    Then my request should fail because "I cannot accept a contact request after the request was rejected"

  Scenario: I should be able to accept a contact request from another user
    Given JohnDoe has requested to be my contact
    When I request to accept JohnDoe's contact request
    Then my request should be successful
