# Mallas Trujillo — quoting system

A quoting system built for a safety-netting company in Peru. Replaces a per-job
spreadsheet with a form, a searchable history and a printable quote.

PHP · MySQL · PDO · Bootstrap 5

## The problem

The cost of an installation depends on total area, and the area depends on how many
panels were measured on site. On top of that sit volume discounts, additional costs
that vary with access difficulty, and an installation minimum below which the job
stops being worth doing. Done by hand, every quote was a different spreadsheet, and
every arithmetic slip was either lost money or an unhappy client.

## Three decisions worth knowing about

**Pricing lives in the database, not the code.** Price per square metre and the three
area-based discount tiers sit in a `precios` table, editable from a screen in the app.
When material costs rise the owner changes a number; every subsequent quote uses it.
No developer involved, and no divergence between the app's price and the
salesperson's memory.

**The server recalculates the total.** The form computes live so the user sees a
figure before saving, but on save the server recomputes area, subtotal, discount,
extras and the installation minimum from the stored line items and writes that result.
The browser proposes; the database decides. The whole save runs in one transaction.

**PDFs without a PDF library.** The quote is rendered as a page with a print
stylesheet — A4, margins, forced colours — and exported by the browser. No dependency
to maintain, and it works on the phone the installer carries to site. The mobile view
shows instructions instead of firing the print dialog, because save-as-PDF on a phone
isn't obvious to anyone who hasn't done it.

## Layout

```
index.php              Entry point
nueva.php              New quote (accepts ?template=ID to copy a previous one)
historial.php          Searchable quote history
precios.php            Price and discount tier editor
proforma.php           Single quote view
Backend/db_connect.php PDO connection helper
calls/                 AJAX endpoints — search, save, price update, PDF views
CSS/                   Bootstrap distribution
```

## Setup

```bash
git clone https://github.com/RodolfoGaspary/Mallas-Trujillo-CRM.git
cp config.example.php config.php    # then edit with your credentials
mysql -u root mallas_trujillo < mallas_trujillo.sql
```

Serve the folder with XAMPP or any PHP 8 host. `RESET_database.sql` empties every
table and resets the auto-increment counters — useful while testing.

## Status

This is the local development version, published as-is. It runs against a MySQL
database that is no longer configured. Known gaps, listed rather than hidden:

- No authentication — it was built for a single-machine local deployment
- No CSRF tokens on the write endpoints
- `mobile_pdf.php` is a partial variant of `pdf.php`
- Error messages surface database detail when `$SHOW_DB_ERRORS` is on

Anyone deploying this beyond localhost should close those first.
