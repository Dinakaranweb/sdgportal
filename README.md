# SDG Portal

A content managed portal for publishing an institution's work against the
United Nations Sustainable Development Goals.

Every goal has its own page and its own colour. Below each goal sits a tree of
sub menus and reporting questions, nested as deeply as needed, and each of those
pages carries a permanent address built from the slug trail — so a single link
can be handed to a ranking body, a partner institution or another portal.

Page content is assembled from content blocks. A block can hold text, a link, an
image, a document, tabular data, figures, or any combination of them.

---

## Requirements

- PHP 8.2 or newer, with the `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` and
  `gd` extensions
- MySQL 5.7 / MariaDB 10.3 or newer
- Composer
- Apache with `mod_rewrite` (the standard XAMPP build is fine)

No Node build step is needed. The stylesheets and scripts in `public/assets`
are served as they are.

---

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the database and point `.env` at it:

```ini
APP_URL=http://localhost/sdg/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sdg_portal
DB_USERNAME=root
DB_PASSWORD=
```

Then build the schema, load the starter content and link the uploads folder:

```bash
php artisan migrate --seed
php artisan storage:link
```

Seeding creates all 17 goals in their published UN colours, the theme settings
and one administrator account. It also cuts the bundled artwork into one image
per goal and prepares the institution logo and favicon.

Worked sample content — sub menus, reporting questions and content blocks — is
only created for **goals 3, 7, 12 and 17**, as a pattern to follow. The other
thirteen goals are created with their artwork and colour but left empty, ready
for real content. The list is the `SAMPLE_GOALS` constant at the top of
`database/seeders/ContentSeeder.php`.

A goal with nothing published yet still appears in the grid, the menu and the
footer strip, but its tile stays on the current page instead of opening an empty
one. It starts linking normally the moment it has its first published page or
block, and it is kept out of `sitemap.xml` until then.

### Signing in

| | |
|---|---|
| Control panel | `/control` |
| Email | `admin@example.com` |
| Password | `ChangeMe123!` |

**Change this password before the site is reachable by anyone else.** Do it from
*Accounts* in the control panel.

---

## Where the site lives

With the project in `htdocs/sdg`, the portal is at:

```
http://localhost/sdg/public/
```

The `.htaccess` in the project root forwards `http://localhost/sdg/` to that
address, so either will do.

On a production server, point the virtual host `DocumentRoot` at the `public`
folder instead and set `APP_URL` to the bare domain. The root `.htaccess` is
then unused and can be deleted.

---

## How the content fits together

```
Goal                     /goals/sdg-13-climate-action
└── Page                 /goals/sdg-13-climate-action/initiatives
    └── Page             /goals/sdg-13-climate-action/initiatives/campus-programmes
```

**Goals** carry the number, title, statement, colour, logo, banner and SEO
fields. The colour set on a goal drives the whole appearance of its pages and
everything nested beneath them.

**Pages** are the sub menus and reporting questions. Each has a `type`
(sub menu, question, section, or external link), an optional reference such as
`13.3`, its own slug, and an optional colour that overrides the goal's on that
branch of the tree. Nesting a page rewrites its address and the addresses of
everything below it automatically.

**Content blocks** attach to a goal or to any page. The available types are:

| Group | Types |
|---|---|
| Text | Text, Highlight note, Quote, Bullet list |
| Media | Image, Image gallery, Video, Document |
| Links | Link list, Cards, Buttons, Call to action |
| Data | Figures, Data pairs, Table, Progress bars, Timeline, Accordion |
| Advanced | Embed |

Each block also has a background (plain, soft, goal tint, goal colour, dark, or
bordered), a width, a column count where it shows rows, and an optional anchor
name so the block can be linked to directly.

---

## Artwork

Two images are bundled in `database/seeders/assets`, and two commands turn them
into the files the portal uses:

```bash
php artisan portal:slice-goal-tiles   # global-goals.png  -> one tile per goal
php artisan portal:install-brand      # institution-logo.jpg -> header logo + favicon
```

`portal:slice-goal-tiles` cuts the six by three artwork sheet into
`uploads/goals/sdg-1.png` through `sdg-17.png` and points each goal record at
its own tile. The combined emblem from the last cell is saved to
`uploads/brand/global-goals.png`.

`portal:install-brand` scales the logo lockup to a header sized PNG and crops
the crest from it as a square favicon.

Neither command overwrites a logo that has been changed by hand; pass `--force`
if you want them to. If the artwork is ever replaced, pass `--source` to point
at the new file — the tile grid coordinates are constants at the top of
`app/Console/Commands/SliceGoalTiles.php`.

To use different artwork entirely, just upload it against each goal in the
control panel; nothing depends on the bundled files after seeding.

---

## Writing content

Long text fields — the goal introduction, a page's opening text, most block
bodies, and the accordion answers — use a rich text editor with headings, bold
and italic, lists, links, tables and images.

Images dropped or pasted into the editor are uploaded to `uploads/editor` and
recorded in the media library rather than being inlined into the saved markup,
so pages stay small.

What the editor produces is filtered on save: a conservative set of tags is
kept, and script tags and inline event handlers are removed. Two fields stay
deliberately plain — the Quote block, which is styled for you, and the Embed
block, which needs its markup left alone.

The editor is served from `public/assets/vendor`, so the control panel works
without an internet connection.

---

## Where files are stored

Every upload — goal artwork, logos, images placed in the text, and documents —
goes to the disk named by `FILESYSTEM_DISK`. Nothing in the application code
names a disk, so moving the portal's files to S3 is a configuration change.

### On this server (the default)

```ini
FILESYSTEM_DISK=public
```

Files land in `storage/app/public` and are served through the `public/storage`
symlink, so `php artisan storage:link` has to have been run.

### On Amazon S3

```ini
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=…
AWS_SECRET_ACCESS_KEY=…
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=your-bucket
AWS_URL=                    # optional: a CloudFront or custom domain
AWS_ENDPOINT=               # optional: MinIO, Spaces, other S3 compatible service
AWS_USE_PATH_STYLE_ENDPOINT=false
AWS_VISIBILITY=public
```

Then:

```bash
php artisan portal:sync-media --from=public --to=s3   # copy what is already there
php artisan config:clear
```

`portal:sync-media` streams the files across, skips anything already on the
target, and takes `--dry-run` to show what it would do and `--overwrite` to
replace. It works in either direction, so `--from=s3 --to=public` brings
everything back.

The bucket has to allow public reads, because the portal links to files by
their address rather than signing each request. Either turn off "Block all
public access" and attach a read-only bucket policy, or put CloudFront in front
of the bucket and set `AWS_URL` to the distribution address.

Two things worth knowing before switching:

- Flipping the disk changes where *new* uploads go. Files already written stay
  where they are, which is what `portal:sync-media` is for. Run it first.
- Browsers only honour the download attribute for files on the same origin, so
  once documents are served from S3 a Word or Excel file may open in a browser
  tab instead of downloading, depending on the `Content-Disposition` the bucket
  returns. PDFs are unaffected — they are meant to open in a tab.

---

## Documents and PDFs

In a Document block, a **PDF opens in a new tab** so it can be read in the
browser's own viewer without losing the page behind it. The card shows "View"
and notes that it opens in a new tab.

Every other file type is offered as a download instead.

The same rule applies wherever a PDF is linked — link lists, cards, buttons,
accordion answers, timelines and call to action blocks all open a PDF in a new
tab, as they already did for links to other sites.

---

## Theming

Everything the portal paints is read from the `settings` table and written into
the page as CSS custom properties. Brand, secondary and accent colours, page and
card backgrounds, body and muted text, borders, the header, utility bar and
footer, the heading and body font families (any Google Fonts family by name),
heading weight, corner radius, content width, goal tile shape and banner style
are all editable under **Theme & settings**.

A goal's own colour layers on top of those tokens for its pages, and a page can
override it again for its own branch. Nothing needs recompiling: save the
setting and the next page load reflects it.

---

## Addresses and sharing

- `/goals` lists every published goal
- `/goals/{goal}` is a goal page
- `/goals/{goal}/{path}` is any sub menu or question, at any depth
- `/sitemap.xml` lists every published address, excluding goals with no content
- `/search?q=` searches goal and page titles, summaries and block content

Search also understands goal numbers. `7`, `07`, `goal 7`, `sdg 7` and `SDG7`
all open goal 7 and list everything published beneath it, rather than returning
every page that happens to contain the digit. Single digits work, so goals 1 to
9 can be found by number alone.

The **Copy address** button on the goal and page forms in the control panel is
where you pick up a link to hand to a ranking body or a partner portal.

A page returns `404` when it is unpublished, when its goal is unpublished, or
when any page above it in the tree is unpublished — so an in-progress branch is
never reachable through a shared deep link.

Changing a slug changes the address and any previously shared link to it will
stop working. The control panel warns about this on both forms.

---

## Housekeeping

```bash
php artisan migrate            # apply new migrations
php artisan optimize           # cache config, routes and views for production
php artisan optimize:clear     # clear those caches
php artisan storage:link       # re-create the uploads symlink if it is missing
```

Re-running `php artisan db:seed` is safe: existing settings keep their saved
values, goals are matched on their slug, and sample content is only added to a
goal that has no pages at all.

### Notes for whoever maintains this

- Adding a block type means one entry in `app/Support/BlockType.php` and a
  matching partial in `resources/views/blocks/`. The editor form and the public
  renderer both read that registry, so nothing else needs touching.
- Icons live in one place, `resources/views/components/icon.blade.php`, and are
  listed for the control panel in `app/Support/Icons.php`.
- Colour shades, tints and readable text colours are derived in
  `app/Support/Palette.php`; only the base colour is stored.
- Editor supplied HTML is filtered through `clean_html()`. The Embed block is
  the one place raw markup is kept, and `embed_html()` still strips script tags,
  inline event handlers and `javascript:` URLs. Give that block only to people
  you would trust with the markup of a page.
- Deleting a goal or a page cascades through the whole subtree and removes the
  content blocks and the files uploaded through them. Files picked from the
  media library are shared, so they are left alone.
- The goal artwork is United Nations material. The UN permits its use for
  informational purposes and asks that the icons are not altered or used to
  imply endorsement; check the current SDG communications guidelines before
  publishing.
