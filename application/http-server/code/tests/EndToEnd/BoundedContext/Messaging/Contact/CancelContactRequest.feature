Feature: Contact - Accept Contact Request
  As an existing user to the system
  I would like to cancel a contact request to another user
  So that I can undo a request made by mistake

  Background:
    Given I am signed in with a verified email
    And JaneDoe is also an existing user

  Scenario: a user should not be able to cancel a contact request, if no contact request has been made
    When JaneDoe requests to cancel my contact request
    Then her request should fail because "there is no contact request to be cancelled"
    
  Scenario: a user should not be able to cancel a contact request made by another user
    Given I have requested to be a contact of JaneDoe
    When JaneDoe requests to cancel my contact request
    Then her request should fail because "only I can cancel the contact request"

  Scenario: I should not be able to cancel a contact request to an already active contact"
    Given I have requested to be a contact of JaneDoe
    And JaneDoe has accepted my contact request
    When I request to cancel my contact request to JaneDoe
    Then my request should fail because "I cannot cancel a contact request to an already active contact"

  Scenario: I should not be able to cancel a contact request that has already been cancelled
    Given I have requested to be a contact of JaneDoe
    And I have cancelled my contact request to JaneDoe
    When I request to cancel my contact request to JaneDoe
    Then my request should fail because "I cannot cancel a contact request that has already been cancelled"

  Scenario: I should not be able to cancel a contact request after having been deleted as a contact
    Given I have requested to be a contact of JaneDoe
    And JaneDoe has accepted my contact request
    And JaneDoe has deleted me as a contact
    When I request to cancel my contact request to JaneDoe
    Then my request should fail because "I cannot cancel a contact request after having been deleted as a contact"

  Scenario: I should not be able to cancel a contact request after having deleted the contact
    Given I have requested to be a contact of JaneDoe
    And JaneDoe has accepted my contact request
    And I have deleted JaneDoe as a contact
    When I request to cancel my contact request to JaneDoe
    Then my request should fail because "I cannot cancel a contact request after having deleted the contact"

  Scenario: I should not be able to cancel a contact request after having rejected it
    Given I have requested to be a contact of JaneDoe
    And JaneDoe has rejected my contact request
    When I request to cancel my contact request to JaneDoe
    Then my request should fail because "I cannot cancel a contact request after having rejected it"

  Scenario: I should be able to cancel a contact request I made to another user
    Given I have requested to be a contact of JaneDoe
    When I request to cancel my contact request to JaneDoe
    Then my request should be successful