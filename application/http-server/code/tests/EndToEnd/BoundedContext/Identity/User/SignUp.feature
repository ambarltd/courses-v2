Feature: User - Sign up
  As a new user to the system
  I would like to sign up using a username, password and email
  So that I can sign in and start using features

  Background:
    Given that I want to sign up as a new user

  Scenario Outline: User can successfully sign up
    When I provide sign up data <username>, <password>, <email>
    And I "accept" the terms and conditions
    And I request to sign up
    Then I should be able to sign up successfully
    And I should receive a confirmation email at <email> with a link containing a newly issued and random verification code

    Examples:
    | username    | password      | email               |
    | "galeas"   | "Test12345*"  | "test@galeas.com"  |
    | "4Let"      | "Test12345!"  | "test2@galeas.com" |
    | "12Xyz"     | "12345%Blah"  | "test3@galeas.com" |

  Scenario Outline: User cannot sign up with an invalid username
    When I provide sign up data <username>, <password>, <email>
    And I "accept" the terms and conditions
    And I request to sign up
    Then I should not be able to sign up successfully for the following reason <reason>

    Examples:
    | username                             | password     | email              | reason                                        |
    | "Ma"                                 | "Test12345*" | "test@galeas.com" | "Username must be at least 3 characters long" |
    | "Maeyxnxueueyecdherreuiieushr12xyz"  | "Test12345*" | "test@galeas.com" | "Username must be at most 32 characters long" |
    | "Maki*"                              | "Test12345*" | "test@galeas.com" | "Username cannot have a special character"    |
    | "gal eas"                           | "Test12345*" | "test@galeas.com" | "Username cannot have a special character"    |
    | "galeas "                           | "Test12345*" | "test@galeas.com" | "Username cannot have a special character"    |
    | " galeas"                           | "Test12345*" | "test@galeas.com" | "Username cannot have a special character"    |
    # Later, when special characters are accepted, prevent the use of @ to prevent the username from being the same as the email.
    # Furthermore, given that there are characters for other languages, and prevention of similar usernames is important,
    # consider the canonicalization of a username being like a url slug (Laravel Str:slug has a guide implementation).
    # An example of a slug for french, jean-françois -> jean-francois or jean-françois -> jeanfrancois

  Scenario Outline: User cannot sign up with invalid password
    When I provide sign up data <username>, <password>, <email>
    And I "accept" the terms and conditions
    And I request to sign up
    Then I should not be able to sign up successfully for the following reason <reason>

    Examples:
    | username   | password         | email              | reason |
    | "Test"     | "TestMe123"      | "test@galeas.com" | "Password must be at least 10 characters long"      |
    | "Test"     | "TESTME123$"     | "test@galeas.com" | "Password must have at least one lowercase letter"  |
    | "Test"     | "testme1234"     | "test@galeas.com" | "Password must have at least one uppercase letter"  |
    | "Test"     | "TestMe1234"     | "test@galeas.com" | "Password must have at least one special character" |
    | "Test"     | "TestMe@@$$"     | "test@galeas.com" | "Password must have at least one number"            |

  Scenario Outline: User cannot sign up with invalid email
    When I provide sign up data <username>, <password>, <email>
    And I "accept" the terms and conditions
    And I request to sign up
    Then I should not be able to sign up successfully for the following reason <reason>

    Examples:
    | username   | password     | email            | reason                 |
    | "Test"     | "testMe111*" | "te"             | "Invalid email length" |
    | "Test"     | "testMe111!" | "test@galeas"   | "Invalid email"        |

  Scenario Outline: User cannot sign up without accepting terms of use
    When I provide sign up data <username>, <password>, <email>
    And I "do not accept" the terms and conditions
    And I request to sign up
    Then I should not be able to sign up successfully for the following reason <reason>

    Examples:
    | username    | password     | email              | reason                                     |
    | "test"      | "Test12345*" | "test@galeas.com" | "You must agree with terms and conditions" |

  Scenario Outline: User cannot sign up with an email or username that is already in use
    Given that a registered user exists with <anotherUsername>, <anotherPassword>, <anotherEmail>
    When I provide sign up data <username>, <password>, <email>
    And I "accept" the terms and conditions
    And I request to sign up
    Then I should not be able to sign up successfully for the following reason <reason>
    Examples:
      | username   | password     | email             | anotherUsername | anotherPassword  | anotherEmail      | reason                           |
      | "foo"      | "Test12345*" | "foo@galeas.com" | "foo"           | "Test12345*"     | "bar@galeas.com" | "The username is already in use" |
      | "foo"      | "Test12345*" | "foo@galeas.com" | "Foo"           | "Test12345*"     | "bar@galeas.com" | "The username is already in use" |
      | "foo"      | "Test12345*" | "foo@galeas.com" | "bar"           | "Test12345*"     | "foo@galeas.com" | "The email is already in use"    |
      | "foo"      | "Test12345*" | "foo@galeas.com" | "bar"           | "Test12345*"     | "Foo@galeas.com" | "The email is already in use"    |
