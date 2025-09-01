# Simple PHP + MySQL Auth App

A minimal PHP app with:
- Registration
- Login
- Logout
- Protected users list (shows all registered users)

## Requirements
- PHP 8.1+
- MySQL 5.7+/8.x
- PDO MySQL extension enabled

## Setup
1. Create a MySQL database (e.g., `php_auth_app`).
2. Edit `config.php` with your DB credentials.
3. The users table is auto-created on first run.

## Run (built-in server)
```bash
php -S localhost:8000
```
Then visit:
- `http://localhost:8000/register.php` to create an account
- `http://localhost:8000/login.php` to sign in
- `http://localhost:8000/users.php` (requires login)
```

## Notes
- Passwords use `password_hash()` / `password_verify()`.
- Keep it simple—no framework needed.
