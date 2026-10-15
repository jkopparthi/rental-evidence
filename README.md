# Rental Evidence

This is my full-stack diploma project. It's a small web app for documenting the
condition of a rental unit — you photograph every room when you move in, photograph
it again when you move out, write a short note on each photo, and the app lines
them up so you can compare. I built it after hearing too many stories about people
losing part of their deposit with nothing to prove the place was already like that.

Frontend is plain HTML, CSS and JavaScript. Backend is PHP with a MySQL database.
No frameworks — I wanted to actually understand what was happening at every layer
instead of relying on something like Laravel to do it for me.

## What it does

- Sign up, log in, log out
- Add a rental (address, landlord, move-in/move-out dates, notes)
- Upload photos for a room, tag each batch as "move-in" or "move-out", and write
  a description
- Each room shows its move-in and move-out photos next to each other
- A slider view lets you drag to compare one room's before/after photo directly
- A print view lays everything out as a report you can save as a PDF
- Every uploaded photo gets a SHA-256 hash stored alongside it, and if the phone
  saved EXIF data, the original capture date shows up too (see "What the hash
  actually proves" below — it's a smaller guarantee than it sounds like)

## Why some of the backend decisions

I spent more time on this than I expected because I kept finding ways it could go
wrong once I thought about it from an attacker's angle instead of a happy-path
user's angle:

- Every database query uses PDO prepared statements. No string-concatenated SQL
  anywhere.
- Anything printed back to the page goes through an `e()` helper that escapes it,
  so a description field can't be used to inject a script tag.
- Every form (and every AJAX call) sends a CSRF token that gets checked before
  anything happens.
- Photos aren't stored inside the public web folder. `photo.php` is the only way
  to view one, and it checks that the logged-in user actually owns that photo
  before it streams the file back. I tested this by trying to load another
  account's photo by ID and it correctly 404s.
- File uploads are checked by their actual content (`finfo`), not by trusting the
  file extension or whatever content-type the browser sends, and they're saved
  under a random filename so nobody can guess or overwrite another user's file.
- Passwords are hashed with PHP's `password_hash()`, and login regenerates the
  session ID so a session can't be hijacked from before you logged in.

## What the hash actually proves

I store a SHA-256 hash of each photo on upload, and I was initially describing
that as "proof the photo wasn't edited or backdated." That's an overstatement,
so here's what it really does:

- It proves the file in storage today is byte-for-byte the same file that was
  uploaded at that timestamp. If someone edited the photo afterward, re-hashing
  it would produce a different value than what's stored — so it catches
  tampering *after* upload.
- It does **not** prove anything about the photo before that moment. Someone
  could edit a photo in another app and then upload the edited version — the
  hash just fingerprints whatever was uploaded, doctored or not.
- The hash lives in the same database as the photo. Anyone with database access
  (me, right now, as the only admin) could update the file and the hash
  together, so it isn't tamper-evident against the app's own operator — only
  against someone who doesn't have that access.
- The timestamp is just the app server's clock. There's no independent,
  third-party timestamping involved, which is what real tamper-evidence (e.g.
  a notarization service) would need.

So it's a legitimate integrity check, and worth describing as "SHA-256 file
integrity verification" on a resume, but it isn't evidence a court or landlord
would treat as proof of anything. The app's real value is just having dated,
organized photos instead of none.

## Running it locally

1. Clone this repo.
2. Import the schema: open `database/schema.sql` in phpMyAdmin, or run
   `mysql -u root -p < database/schema.sql`.
3. Copy `config/config.example.php` to `config/config.php` and put in your own
   database password.
4. From the project root: `php -S localhost:8000 -t public`
5. Go to `http://localhost:8000` and create an account.

If you're uploading photos straight from a phone, you may need to raise
`upload_max_filesize` and `post_max_size` in your php.ini — phone photos get big.

## Folder layout

```
config/     config.example.php — copy this to config.php (git ignores the real one)
database/   schema.sql
public/     the actual web pages, one PHP file per route, plus css/js in assets/
src/        shared backend code — db connection, auth, csrf, upload handling, layout
storage/    where uploaded photos actually live (also gitignored)
```

## Privacy

Short version: nothing leaves the one server you run it on, every page checks
that you own what you're looking at, and I've written out exactly what's
collected and where the real gaps are in [`PRIVACY.md`](PRIVACY.md).

## Screenshots

![Property page with move-in/move-out photos](docs/screenshots/property.png)

## Things I didn't get to

- A read-only link you could send a landlord instead of showing them your whole
  account
- Actual thumbnailing/resizing on upload — right now it stores the full-size file
- Tests. I tested everything manually while building it, but there's no automated
  test suite yet
- Email verification and password reset. There's no email sending at all right
  now, so the email field is just a login identifier — nobody else ever sees it,
  and there's nothing to protect by verifying it yet. It becomes necessary the
  moment there's a "forgot password" flow, since you can't email a reset link
  to an address you never confirmed.
