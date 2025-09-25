<?php
/**
 * Validation helpers. Use these inside controllers before inserting/updating.
 */

function v_required($value): bool {
  return !(is_null($value) || $value === '' || (is_array($value) && count($value)===0));
}

function v_email(string $email): bool {
  return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function v_minlen(string $value, int $min): bool {
  return mb_strlen($value) >= $min;
}

function v_maxlen(string $value, int $max): bool {
  return mb_strlen($value) <= $max;
}

function v_in(array $set, $value): bool {
  return in_array($value, $set, true);
}

function v_digits(string $value): bool {
  return preg_match('/^\d+$/', $value) === 1;
}

function v_phone_basic(string $value): bool {
  // Simple international-ish check: digits, spaces, +, -, ()
  return preg_match('/^[0-9 \-\+\(\)]{6,20}$/', $value) === 1;
}

function v_password_strong(string $pwd): bool {
  // at least 8 chars, one letter, one digit (tweak as needed)
  return preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $pwd) === 1;
}

/**
 * Validate array of rules. Returns [ok=>bool, errors=>[field=>msg]]
 * $rules = [
 *   'email' => ['required','email'],
 *   'password' => ['required',['min',8]],
 *   'role' => [['in',['admin','instructor','student']]]
 * ];
 */
function validate(array $input, array $rules): array {
  $errors = [];
  foreach ($rules as $field => $checks) {
    $val = $input[$field] ?? null;
    foreach ($checks as $rule) {
      $name = $rule;
      $arg  = null;
      if (is_array($rule)) { $name = $rule[0]; $arg = $rule[1] ?? null; }

      switch ($name) {
        case 'required':
          if (!v_required($val)) $errors[$field] = 'This field is required.';
          break;
        case 'email':
          if (!is_null($val) && !v_email((string)$val)) $errors[$field] = 'Invalid email.';
          break;
        case 'min':
          if (!is_null($val) && !v_minlen((string)$val, (int)$arg)) $errors[$field] = "Must be at least $arg characters.";
          break;
        case 'max':
          if (!is_null($val) && !v_maxlen((string)$val, (int)$arg)) $errors[$field] = "Must be at most $arg characters.";
          break;
        case 'digits':
          if (!is_null($val) && !v_digits((string)$val)) $errors[$field] = 'Digits only.';
          break;
        case 'phone':
          if (!is_null($val) && !v_phone_basic((string)$val)) $errors[$field] = 'Invalid phone number.';
          break;
        case 'strong_password':
          if (!is_null($val) && !v_password_strong((string)$val)) $errors[$field] = 'Min 8 chars with letters and numbers.';
          break;
        case 'in':
          if (!is_null($val) && !v_in((array)$arg, $val)) $errors[$field] = 'Invalid option.';
          break;
      }

      if (isset($errors[$field])) break; // stop on first error per field
    }
  }

  return ['ok' => empty($errors), 'errors' => $errors];
}
