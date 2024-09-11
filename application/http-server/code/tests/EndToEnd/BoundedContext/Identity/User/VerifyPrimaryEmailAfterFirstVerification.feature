Feature: User - Verify Primary Email Change After First Verification
  As a registered user to the system, with a verified email,
  I would like to verify a request to change my primary email
  So that I can use the system with my newly verified email

  Background: After verifying my primary email for the first time,
    Given that I have signed up with username "galeas", password "Test12345*", and email "test@galeas.com"
    And I am signed in, with a verified email, using email "test@galeas.com" and password "Test12345*"

  Scenario: I can request a primary email change and successfully verify the new primary email
    Given I have successfully requested to change my primary email to "test2@galeas.com" with password "Test12345*"
    When I request to verify that primary email change with the correct verification code
    Then my request should be successful
    And I should be able to sign in with email "test2@galeas.com" and password "Test12345*"
    And I should not be able to sign in with email "test@galeas.com" and password "Test12345*"

  Scenario: I can request a primary email change, but cannot verify the new primary email without the correct code
    Given I have successfully requested to change my primary email to "test2@galeas.com" with password "Test12345*"
    When I request to verify that primary email change with verification code "ThisIsMadeUp01234"
    Then my request should fail because "the verification code is incorrect"
    And I should be able to sign in with email "test@galeas.com" and password "Test12345*"
    And I should not be able to sign in with email "test2@galeas.com" and password "Test12345*"

  Scenario: I can request a primary email change and successfully verify the new primary email. The verification code cannot be reused.
    Given I have successfully requested to change my primary email to "test2@galeas.com" with password "Test12345*"
    When I request to verify that primary email change with the correct verification code twice
    Then my first request should be successful
    And my second request should fail because "the code has been used"
    And I should be able to sign in with email "test2@galeas.com" and password "Test12345*"
    And I should not be able to sign in with email "test@galeas.com" and password "Test12345*"
