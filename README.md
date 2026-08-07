hash_passwords.php

This script helps migrate plain-text passwords stored in the `users` table to secure hashed passwords using PHP's `password_hash()`.

Usage:

- Dry run (shows what would change):

```bash
php scripts/hash_passwords.php
```

- Apply changes (updates the DB):

```bash
php scripts/hash_passwords.php --apply
```

Important:

- Always BACK UP your database before running with `--apply`.
- The script detects common hash formats (bcrypt, argon2). If a password does not look hashed it will be re-hashed.
- After running, users will log in using their existing plain password (now hashed on the server side). If you manually altered passwords earlier, ensure you know the original plain values before running.
