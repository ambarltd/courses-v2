Feature: Contact - List Contacts
  As an existing user to the system
  I would like to list my requested contacts,
  my requesting contacts, and my active contacts,
  so I can track who I can message,
  and who I could message later.

  Background:
    Given I am signed in with a verified email
    And JohnDoe is also an existing user

  Scenario: My contact list should be empty before I have made or received any contact requests
    When I request to see a list of my contacts
    Then I should have no requested contacts
    And I should have no requesting contacts
    And I should have no active contacts

  Scenario: My contact list should be empty before I have made or received any contact requests
    When JohnDoe requests to see a list of his contacts
    Then JohnDoe should have no requested contacts
    And JohnDoe should have no requesting contacts
    And JohnDoe should have no active contacts

  Scenario: Upon accepting another user's contact request, that user should appear in my list of active contacts
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    When I request to see a list of my contacts
    Then I should have no requested contacts
    And I should have no requesting contacts
    And JohnDoe should be my only active contact

  Scenario: Upon accepting another user's contact request, I should appear in that user's list of active contacts
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    When JohnDoe requests to see a list of his contacts
    Then JohnDoe should have no requested contacts
    And JohnDoe should have no requesting contacts
    And I should be JohnDoe's only active contact

  Scenario: Upon another user cancelling their contact request to me, that user should no longer appear in my list of requested contacts
    Given JohnDoe has requested to be my contact
    And JohnDoe has cancelled his contact request
    When I request to see a list of my contacts
    Then I should have no requested contacts
    And I should have no requesting contacts
    And I should have no active contacts

  Scenario: Upon another user cancelling their contact request to me, I should no longer appear in that user's list of requested contacts
    Given JohnDoe has requested to be my contact
    And JohnDoe has cancelled his contact request
    When JohnDoe requests to see a list of his contacts
    Then JohnDoe should have no requested contacts
    And JohnDoe should have no requesting contacts
    And JohnDoe should have no active contacts

  Scenario: Upon deleting another user as a contact, that user should no longer appear in my list of active contacts
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    And I deleted JohnDoe as a contact
    When I request to see a list of my contacts
    Then I should have no requested contacts
    And I should have no requesting contacts
    And I should have no active contacts

  Scenario: Upon deleting another user as a contact, I should no longer appear in that user's list of active contacts
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    And I deleted JohnDoe as a contact
    When JohnDoe requests to see a list of his contacts
    Then JohnDoe should have no requested contacts
    And JohnDoe should have no requesting contacts
    And JohnDoe should have no active contacts

  Scenario: Upon another user deleting me as a contact, that user should no longer appear in my list of active contacts
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    And JohnDoe deleted me as a contact
    When I request to see a list of my contacts
    Then I should have no requested contacts
    And I should have no requesting contacts
    And I should have no active contacts

  Scenario: Upon another user deleting me as a contact, I should no longer appear in that user's list of active contacts
    Given JohnDoe has requested to be my contact
    And I have accepted JohnDoe's contact request
    And JohnDoe deleted me as a contact
    When JohnDoe requests to see a list of his contacts
    Then JohnDoe should have no requested contacts
    And JohnDoe should have no requesting contacts
    And JohnDoe should have no active contacts

  Scenario: Upon rejecting another user's contact request, that user should no longer appear in my list of requesting contacts
    Given JohnDoe has requested to be my contact
    And I rejected JohnDoe's contact request
    When I request to see a list of my contacts
    Then I should have no requested contacts
    And I should have no requesting contacts
    And I should have no active contacts

  Scenario: Upon rejecting another user's contact request, I should no longer appear in that user's list of requested contacts
    Given JohnDoe has requested to be my contact
    And I rejected JohnDoe's contact request
    When JohnDoe requests to see a list of his contacts
    Then JohnDoe should have no requested contacts
    And JohnDoe should have no requesting contacts
    And JohnDoe should have no active contacts

  Scenario:Upon another user requesting me as a contact, that user should appear in my list of requesting contacts
    Given JohnDoe has requested to be my contact
    When I request to see a list of my contacts
    Then I should have no requested contacts
    And JohnDoe should be my only requesting contact
    And I should have no active contacts

  Scenario: Upon another user requesting me as a contact, I should appear in that user's list of requested contacts
    Given JohnDoe has requested to be my contact
    When JohnDoe requests to see a list of his contacts
    Then I should be JohnDoe's only requested contact
    And JohnDoe should have no requesting contacts
    And JohnDoe should have no active contacts

