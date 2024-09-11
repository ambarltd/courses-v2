Feature: Session - Sign Out
  As an existing user who is signed in
  I would like to be able to sign out
  So that I can stop using the system

  Background: Given that I have signed up, and I am signed in,
    Given that I have signed up with username "galeas", password "Test12345*", and email "test@galeas.com"
    And I am signed in using email "test@galeas.com" and password "Test12345*"

  Scenario: I should be able to sign out
    When I request to sign out
    Then my request should be successful

  Scenario: I should not be able to sign out if I am not signed in.
    Given I have signed out
    When I request to sign out again
    Then my request should fail because "I am not signed in"
