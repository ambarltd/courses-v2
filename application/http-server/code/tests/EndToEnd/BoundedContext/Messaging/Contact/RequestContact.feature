Feature: Contact - Request Contact
  As an existing user to the system
  I would like to request to be a contact of another user
  So that we can message each other, if the request is accepted

  Background:
    Given I am signed in with a verified email
    And JaneDoe is also an existing user

  Scenario: I should not be able to request to be my own contact
    When I request to be my own contact
    Then my request should fail because "I cannot request to be my own contact"

  Scenario: I should not be able to request to be the contact of a non-existing user
    When I request to be a contact of a non-existing user
    Then my request should fail because "a user that does not exist cannot be requested as a contact"

  Scenario: I should be able to request to be the contact of another user
    When I request to be a contact of JaneDoe
    Then my request should be successful

  Scenario: I should not be able to request to be the contact of a user who is already my contact
    Given I have requested to be a contact of JaneDoe
    And JaneDoe has accepted my contact request
    When I request to be a contact of JaneDoe
    Then my request should fail because "I cannot request to be a contact of an already active contact"

  Scenario: I should be able to request to be the contact of a user, after cancelling my contact request
    Given I have requested to be a contact of JaneDoe
    And I have cancelled my contact request to JaneDoe
    When I request to be a contact of JaneDoe
    Then my request should be successful

  Scenario: I should be able to request to be the contact of a user, after having been deleted as a contact
    Given I have requested to be a contact of JaneDoe
    And JaneDoe has accepted my contact request
    And JaneDoe has deleted me as a contact
    When I request to be a contact of JaneDoe
    Then my request should be successful

  Scenario: I should be able to request to be the contact of a user, after having deleted the user as a contact
    Given I have requested to be a contact of JaneDoe
    And JaneDoe has accepted my contact request
    And I have deleted JaneDoe as a contact
    When I request to be a contact of JaneDoe
    Then my request should be successful

  Scenario: I should be able to request to be the contact of a user, after the contact request was rejected
    Given I have requested to be a contact of JaneDoe
    And JaneDoe has rejected my contact request
    When I request to be a contact of JaneDoe
    Then my request should be successful

  Scenario: I should not be able to request to be the contact of a user, if I have a pending contact request to that user
    Given I have requested to be a contact of JaneDoe
    When I request to be a contact of JaneDoe
    Then my request should fail because "I cannot request to be a contact of an already pending contact"