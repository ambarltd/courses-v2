Feature: User - Request Primary Email Change After First Verification
  As a registered user to the system, with a verified email,
  I would like to request to change my primary email,
  So that I can later verify it and use the system with the new email.

  Background: After verifying my primary email for the first time,
    Given that I signed up with username "galeas", password "Test12345*", and email "test@galeas.com"
    And I am signed in, with a verified email, using email "test@galeas.com" and password "Test12345*"

  Scenario: I can successfully request a primary email change
    When I request to change my primary email to "test2@galeas.com" using password "Test12345*"
    Then my request should be successful
    And I should be able to sign in with email "test@galeas.com" and password "Test12345*"
    And I should not be able to sign in with email "test2@galeas.com" and password "Test12345*"

  Scenario: I can successfully request a primary email change twice
    When I request to change my primary email to "test2@galeas.com" using password "Test12345*"
    And I request to change my primary email to "test3@galeas.com" using password "Test12345*"
    Then my request should be successful
    And I should be able to sign in with email "test@galeas.com" and password "Test12345*"
    And I should not be able to sign in with email "test2@galeas.com" and password "Test12345*"
    And I should not be able to sign in with email "test3@galeas.com" and password "Test12345*"

  Scenario: I cannot request a primary email change if the new email is identical to my current email
    When I request to change my primary email to "test@galeas.com" using password "Test12345*"
    Then my request should fail because "the email is not changing"

  Scenario: I cannot request a primary email change to the same email twice in a row
    When I request to change my primary email to "test2@galeas.com" using password "Test12345*"
    And I request to change my primary email to "test2@galeas.com" using password "Test12345*"
    Then my request should fail because "the email is not changing"

  Scenario: I cannot request a primary email change without providing the current password
    When I request to change my primary email to "test2@galeas.com" using password "WrongPassword1234*"
    Then my request should fail because "the password does not match"

  Scenario: I cannot request a primary email change to an unverified email belonging to another user
    Given another user exists with username "test2", password "Test212345*", and email "test2@galeas.com"
    When I request to change my primary email to "test2@galeas.com" using password "Test12345*"
    Then my request should fail because "the email is taken"

  Scenario: I cannot request a primary email change to a verified email belonging to another user
    Given another user exists with username "test2", password "Test212345*", and verified email "test2@galeas.com"
    When I request to change my primary email to "test2@galeas.com" using password "Test12345*"
    Then my request should fail because "the email is taken"

  Scenario Outline: I cannot request a primary email change to an invalid email
    When I request to change my primary email to <email> using password <password>
    Then my request should fail because "the email is invalid"

    Examples:
      | email                  | password     |
      | "3ltr"                 | "Test12345*" |
      | "jasdf@"               | "Test12345*" |
      | "@abc"                 | "Test12345*" |
