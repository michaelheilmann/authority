<?php

/// The DTO for an organization.
class Organization {
  /// The unique ID of the organization.
  /// For example <code>looney-tunes-1</code>.
  public string $id;
  /// The name of the organization.
  /// For example <code>Looney Tunes</code>.
  public string $name;
};

/// The DTO for a person.
class Person {
  /// The unique ID of th person.
  /// For example <code>bugs-bunny-1</code>.
  public string $id;
  /// The prename of the person.
  /// For example <code>Bugs</code>.
  public string $prename;
  /// The surname of the person.
  /// For example <code>Bunny</code>.
  public string $surname;
};

/// The DTO for a user.
class User {
  // The name.
  public string $name;
  // The email.
  public string $email;
  // The salted and hashed password.
  public string $password;
};

?>

