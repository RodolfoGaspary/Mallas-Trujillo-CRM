# Mallas Trujillo

Simple XAMPP + PHP project for managing proformas, clientes and items.

Quick start
1. Place this project inside your XAMPP `htdocs` folder or configure Apache to point to the project.
2. Ensure MySQL/MariaDB is running in XAMPP and import the database dump: `Backend/mallas_trujillo.sql`.
3. Import mock data if you want example rows: `Backend/mock_data.sql`.
4. Edit `config.php` at the project root to set DB credentials if needed.
5. Open `http://localhost/Mallas%20Trujillo/public/` in your browser (or adjust for your Apache setup).

Notes
- `config.php` contains DB credentials and is excluded from git via `.gitignore`.
- Use `Backend/db_connect.php` to get a PDO or MySQLi connection: `get_pdo_connection()` / `get_mysqli_connection()`.
- Security: move non-public PHP to `Backend/` and keep only public entry points under `public/`.
