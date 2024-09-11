Feature: Session - Refresh Token
  As an an existing user who is signed in
  I would like to be able to refresh my session token
  So that I can continue using the system

  Background: Given that I have signed up, and signed in,
    Given that I have signed up with username "galeas", password "Test12345*", and email "test@galeas.com"
    And I am signed in using email "test@galeas.com" and password "Test12345*"

  Scenario: I should be able to refresh my session token
    When I request to refresh my session token
    Then my request should be successful

  Scenario: If I sign out, I shouldn't be able to refresh my session token.
    Given I have signed out
    When I request to refresh my session token
    Then my request should fail because "I am not signed in"

  Scenario: I should be able to refresh my session token multiple times.
    Given I have refreshed my session token
    When I request to refresh my session token again
    Then my request should be successful
