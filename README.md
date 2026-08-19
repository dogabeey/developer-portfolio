# Game Dev Portfolio admin

This portfolio now uses PHP and MySQL to load published projects and includes a protected admin panel at `/admin/login.php`.

## Local setup

1. Create the database and tables with `database.sql`:

   ```powershell
   mysql -u root -p < database.sql
   ```

2. Copy `config.example.php` to `config.php` and set the MySQL connection values. `config.php` is ignored by Git.

   ```powershell
   Copy-Item config.example.php config.php
   ```

3. Create the first login account. Passwords are stored only as secure hashes:

   ```powershell
   php create-admin.php admin use-a-long-unique-password
   ```

4. Serve the directory with PHP (or put it in the document root of Apache/Nginx with PHP and `pdo_mysql` enabled):

   ```powershell
   php -S localhost:8000
   ```

5. Open `http://localhost:8000/admin/login.php`, sign in, and add projects. Published projects appear on the homepage; uncheck the publish box to save a draft.

### Automatic link thumbnails

When a project has a project link but no thumbnail URL, the site reads the linked page's Open Graph or Twitter image and saves it as the thumbnail. Enable PHP's `openssl` extension in `php.ini` for HTTPS links (the usual case); `curl` is optional but recommended:

```ini
extension=openssl
extension=curl
```

Restart the PHP development server after changing `php.ini`.

## Data model

- `users`: administrator usernames and bcrypt/Argon-compatible password hashes managed by PHP's `password_hash`.
- `projects`: title, role, description, optional project/cover links, display order, publish state, and timestamps.

Do not expose `config.php` or `create-admin.php` from a public deployment. The latter only runs from the command line, but deleting it after creating your account is still a sensible production precaution.
