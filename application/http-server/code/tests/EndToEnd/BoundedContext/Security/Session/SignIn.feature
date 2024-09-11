Feature: Session - Sign In
  As an existing user
  I would like to be able to sign in
  So that I can use the system

  Background: After signing up,
    Given that I have signed up with username "galeas", password "Test12345*", and email "test@galeas.com"

  Scenario: I should be able to sign in with my username and password.
    When I request to sign in with username "galeas" and password "Test12345*"
    Then my request should be successful

  Scenario: I should be able to sign in with my email and password.
    When I request to sign in with email "test@galeas.com" and password "Test12345*"
    Then my request should be successful

  Scenario: I should not be able to sign in with an incorrect username.
    When I request to sign in with username "pullfor" and password "Test12345*"
    Then my request should fail because "the username is incorrect"

  Scenario: I should not be able to sign in with an incorrect email.
    When I request to sign in with email "test2@galeas.com" and password "Test12345*"
    Then my request should fail because "the email is incorrect"

  Scenario: I should not be able to sign in with an incorrect password.
    When I request to sign in with email "test@galeas.com" and password "Test67890*"
    Then my request should fail because "the password is incorrect"

  Scenario: I should not be able to sign in without providing a password.
    When I request to sign in with email "test@galeas.com" and no password
    Then my request should fail because "the password is missing"

  Scenario: I should not be able to sign in without providing either an email or a username.
    When I request to sign in without either an email or username
    Then my request should fail because "the username or email is missing"
