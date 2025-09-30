# Mallas Trujillo

Simple XAMPP + PHP project for managing proformas, clientes and items.

Notes
- `config.php` contains DB credentials and is excluded from git via `.gitignore`.
- Use `Backend/db_connect.php` to get a PDO or MySQLi connection: `get_pdo_connection()` / `get_mysqli_connection()`.
- Security: move non-public PHP to `Backend/` and keep only public entry points under `public/`.
