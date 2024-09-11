Feature: Contact - Delete Contact
  As an existing user to the system
  I would like to reject contact requests
  So that I can stop unwanted messages 

  Background:
    Given I am signed in with a verified email
    And JohnDoe is also an existing user

  Scenario: a user should not be able to reject my contact request, if no contact request has been made
    When JohnDoe requests to reject my contact request
    Then his request should fail because "there is no contact request to be rejected"

  Scenario: a user should not be able to reject their own contact request to another user
    Given JohnDoe has requested to be my contact
    When JohnDoe requests to reject my contact request
    Then his request should fail because "only I can reject the contact request"

  Scenario: I should not be able to reject a contact request to an already active contact
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    When I request to reject JohnDoe's contact request
    Then my request should fail because "I cannot reject a contact request to an already active contact"

  Scenario: I should not be able to reject a contact request after the requester cancelled it
    Given JohnDoe has requested to be my contact
    And JohnDoe has cancelled his contact request
    When I request to reject JohnDoe's contact request
    Then my request should fail because "I cannot reject a contact request after the requester cancelled it"

  Scenario: I should not be able to reject a contact request after having been deleted as a contact
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    And I deleted JohnDoe as a contact
    When I request to reject JohnDoe's contact request
    Then my request should fail because "I cannot reject a contact request after having deleted the contact"

  Scenario: I should not be able to reject a contact request after having been deleted as a contact
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    And JohnDoe deleted me as a contact
    When I request to reject JohnDoe's contact request
    Then my request should fail because "I cannot reject a contact request after having been deleted as a contact"

  Scenario: I should not be able to reject a contact request that has already been rejected
    Given JohnDoe has requested to be my contact
    And I rejected JohnDoe's contact request
    When I request to reject JohnDoe's contact request
    Then my request should fail because "I cannot reject a contact request that has already been rejected"

  Scenario: I should be able to reject a contact request from another user
    Given JohnDoe has requested to be my contact
    When I request to reject JohnDoe's contact request
    Then my request should be successful
