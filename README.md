# StudentHub Portal — WDF Lab (ITUE203)

A semester-long, progressively-built student portal for **ITUE203 – Web Development
Frameworks (WDF)**. The repository contains one folder per practical, aligned to the
official practical list (`Practical_List_ITUE203_WDF_2026_27.pdf`).

The detailed practical list defines **14 practicals**. Each practical folder is a
**cumulative snapshot**: it contains all files from the previous practical plus the
changes introduced by that practical. Read the folders from `Practical1` through
`practical14` to follow the project from static HTML pages to a secure, database-backed
PHP application with a JSON/AJAX API and a role-based admin dashboard.

> Note on the course summary table: the syllabus summary lists a 15th row
> ("Final Integration, Testing, Documentation, GitHub Submission, and Local
> Deployment") but it has **no detailed practical section and no implementation
> folder** in the provided list. The implemented work therefore covers practicals
> **1–14**; the final-integration expectations (GitHub submission, documentation,
> local deployment) are addressed through this README and the run instructions below.

---

## Practical index

| # | Title | Folder | Focus |
|---|-------|--------|-------|
| 1 | Project Initiation, Requirement Analysis, Sitemap, Wireframe, GitHub Setup | `Practical1/` | Planning, sitemap, wireframes, repo setup |
| 2 | Semantic HTML5 Pages with Accessibility-Ready Structure | `Practical2/` | 10+ linked semantic HTML pages |
| 3 | Responsive UI Design (Grid, Flexbox, Bootstrap/Tailwind) | `Practical3/` | Mobile-first responsive CSS |
| 4 | JavaScript DOM Manipulation, Events, UI Interactivity | `Practical4/` | FAQ, modal, slider, theme, hamburger menu |
| 5 | Registration Form with Frontend Validation | `Practical5/` | Client-side validation, password strength |
| 6 | Rendering External JSON using Fetch API | `Practical6/` | Fetch, search, filter, sort, pagination |
| 7 | PHP Form Processing + CSV/JSON Storage | `practical7/` | Server-side validation, file storage |
| 8 | MySQL Schema, ER Model, PDO, Prepared Statements | `practical8/` | `db.php`, SQL dump, CRUD via PDO |
| 9 | Secure Registration (Insert, Duplicate Check, Hashing) | `practical9/` | `password_hash`, duplicate email check |
| 10 | Secure Login/Logout, Sessions, RBAC, Timeout, Remember-Me | `practical10/` | Auth, roles, session timeout |
| 11 | Student Management CRUD with Search and Filter | `practical11/` | Admin student CRUD |
| 12 | Event Management CRUD with Poster Upload | `practical12/` | Event CRUD + validated file upload |
| 13 | PHP JSON API + AJAX/Fetch CRUD (no page reload) | `practical13/` | REST-style API, async CRUD |
| 14 | Admin Dashboard with RBAC, Dynamic Menu, Audit Log | `practical14/` | Stats dashboard, RBAC menu, audit trail |

Each detailed section below lists the **files added in that practical (vs. the previous
practical)**, the **files modified**, and the **key implementation details**.

---

## Practical 1 — Project Initiation, Requirements, Sitemap, Wireframe, GitHub

**Folder:** `Practical1/`

**Objective:** Define the problem scope, user roles, navigation flow and at least 10
pages for the StudentHub portal; produce a sitemap, low-fidelity wireframes, a folder
structure, a README and a GitHub repository.

**Files added (initial practical):**

| File | Purpose |
|------|---------|
| `pages/index.html` | First landing/entry page |
| `css/style.css` | Shared base stylesheet |
| `images/charusat.png` | Project logo asset |
| `SITEMAP` | Site map of planned pages and navigation flow |
| `wireframefinal.drawio` | Editable low-fidelity wireframe (Draw.io) |
| `wireframe-Page-3.drawio.png` | Exported wireframe screenshot |

**Key details:** Purely design artefacts and a single HTML page — no scripting or
backend yet. Establishes the repository, folder conventions (`pages/`, `css/`,
`images/`) and the minimum 10-page scope that later practicals implement.

---

## Practical 2 — Semantic HTML5 Pages (Accessibility-Ready)

**Folder:** `Practical2/`

**Objective:** Develop static HTML5 skeletons for at least 10 StudentHub pages using
semantic tags and accessibility-friendly structure.

**Files added (vs. Practical1):**

- **Pages (HTML):** `assignment.html`, `attendance.html`, `contact.html`,
  `courses.html`, `login.html`, `profile.html`, `register.html`, `result.html`
- **Stylesheets (CSS):** `assignment.css`, `attendance.css`, `contact.css`,
  `courses.css`, `login.css`, `profile.css`, `register.css`, `result.css`
- **Asset:** `pages/profile.jpg`

**Files modified:** `pages/index.html` (linked the new pages into the navigation).

**Key details:** Introduces semantic landmarks (`header`, `nav`, `main`, `section`,
`article`, `aside`, `footer`), labelled form fields, image `alt` text and a consistent
navigation menu across all pages. Each page gets its own stylesheet in addition to the
shared `style.css`.

---

## Practical 3 — Responsive UI (CSS Grid, Flexbox)

**Folder:** `Practical3/`

**Objective:** Make the layouts responsive for mobile, tablet and desktop using CSS
Grid and Flexbox with a mobile-first approach.

**Files added:** none (no new files in this practical).

**Files modified:** every stylesheet (`style.css` and all per-page CSS) and
`pages/index.html`.

**Key details:** Adds media queries, flexbox/grid layouts, fluid typography and spacing
so the existing pages adapt across breakpoints. This is a **refinement of Practical 2's
output**, so the folder is otherwise identical to `Practical2/`.

---

## Practical 4 — JavaScript DOM Manipulation and Interactivity

**Folder:** `Practical4/`

**Objective:** Add dynamic UI components — collapsible FAQ, modal popup, content
slider, notification banner, hamburger menu and light/dark theme switcher.

**Files added (vs. Practical3):**

- `js/index.js` — DOM interactions (theme switcher with `localStorage`, FAQ accordion,
  modal, slider, notification banner, hamburger menu)
- `js/login.js` — login page interactivity

**Files modified:** all stylesheets plus `pages/courses.html`, `pages/index.html`,
`pages/login.html`, `pages/result.html` (wired up the scripts and components).

**Key details:** Uses `document.querySelector`, event listeners and `classList`
toggling. Theme choice is persisted in `localStorage` and restored on load. This is the
first practical that introduces client-side JavaScript.

---

## Practical 5 — Registration Form with Frontend Validation

**Folder:** `Practical5/`

**Objective:** Build a registration form with HTML5 input types and JavaScript
validation (name, email, mobile, password, confirm password, course, year, gender,
terms) using regular expressions and a password-strength meter.

**Files added (vs. Practical4):**

- `js/register.js` — client-side validation logic for the registration form

**Files modified:** `css/register.css`, `css/style.css`, `js/index.js`, `js/login.js`,
`pages/index.html`, `pages/register.html`.

**Key details:** Real-time validation on `keyup`/`change`, inline error messages near
each field, regex checks and password-strength feedback. Errors are shown without
submitting the form.

---

## Practical 6 — Rendering External JSON with Fetch API

**Folder:** `Practical6/`

**Objective:** Fetch and render data from external JSON files, implementing search,
filter, sort, pagination, and loading/error states.

**Files added (vs. Practical5):**

- `data/student.json` — sample dataset consumed by the frontend
- `js/json-test.js` — demonstrates `fetch()` + parsing (loads student data, resolves the
  logged-in student and logs/renders it)

**Files modified:** `js/index.js`, `js/login.js`, `js/register.js`, `pages/index.html`.

**Key details:** Introduces `fetch()` with promise chaining (`response.ok` checks and
error handling). The profile/dashboard views are driven by the JSON data instead of
hard-coded markup.

---

## Practical 7 — PHP Form Processing with CSV/JSON Storage

**Folder:** `practical7/`

**Objective:** Process the registration form with PHP POST, validate/sanitize on the
server and store records in a CSV/JSON file, returning clear success/error messages.

**Files added (vs. Practical6):**

- `pages/register.php` — server-side form handler
- `pages/students.csv` — CSV storage for submitted registrations

**Files removed:** `pages/register.html` (replaced by `register.php`).

**Files modified:** most HTML pages (navigation updated to point to the PHP handler).

**Key details:** Uses `$_POST`, `filter_var` (e.g. `FILTER_VALIDATE_EMAIL`),
`htmlspecialchars` for output escaping, and `fopen`/`fputcsv` to append records to
`students.csv`. Displays success/error messages to the user. First server-side
practical.

---

## Practical 8 — MySQL Schema, PDO Connectivity and Prepared Statements

**Folder:** `practical8/`

**Objective:** Design a normalized StudentHub database (students, events,
registrations) and connect to it from PHP using PDO and prepared statements.

**Files added (vs. practical7):**

| File | Purpose |
|------|---------|
| `db.php` | Shared PDO connection (host `localhost`, DB `studenthub`, user `root`) |
| `database/studenthub.sql` | SQL dump defining `students`, `events`, `registrations` |
| `data/students.csv` | CSV storage used by the file-based view |
| `pages/events.php` | Lists/adds events from the database |
| `pages/event_register.php` | Registers a student for an event (DB inserts) |
| `pages/login.php` | Database-backed login (`password_verify`) |
| `pages/test_connection.php` | Connection smoke test (counts rows) |
| `pages/view_students.php` | Renders stored student records |
| `js/js/login.js` | Login helper placed in a nested `js/` folder |

**Files removed:** `pages/login.html`, `pages/profile.jpg`, `pages/result.html`, and
`js/login.js` (relocated to `js/js/login.js`).

**Files modified:** `css/attendance.css`, `pages/register.php` and several HTML pages.

**Key details:** `db.php` creates a PDO instance with `ERRMODE_EXCEPTION`. `events.php`,
`event_register.php`, `login.php` and `register.php` use `prepare()`/`execute()` with
named placeholders. The SQL dump defines primary keys, a unique email index and foreign
keys linking `registrations` to `students` and `events`.

> Repository quirk: `js/login.js` was moved into `js/js/login.js` in this practical, so
> the login page references the nested path. Later practicals keep this layout.

---

## Practical 9 — Secure Registration (Insert, Duplicate Check, Hashing)

**Folder:** `practical9/`

**Objective:** Implement secure registration: validate on client and server, check for
duplicate email/username, hash passwords with `password_hash()`, and show clear
messages.

**Files added:** none.

**Files modified:** `data/students.csv` (updated sample/test data).

**Key details:** The secure registration logic lives in `pages/register.php`, which
already performs the required checks: it validates input with `filter_var`, runs a
`SELECT ... WHERE student_id = :id OR email = :email` duplicate check, hashes the
password with `password_hash(..., PASSWORD_DEFAULT)` and inserts via a prepared
statement. `practical9/` carries this implementation forward unchanged from
`practical8/` and refreshes the accompanying sample data.

---

## Practical 10 — Secure Login/Logout, Sessions, RBAC, Timeout, Remember-Me

**Folder:** `practical10/`

**Objective:** Implement login/logout with PHP sessions, `password_verify`, session ID
regeneration, role-based dashboard access and redirection, session timeout and an
optional hashed remember-me token.

**Files added (vs. practical9):**

- `pages/Admin.php` — admin dashboard
- `pages/admin_register.php` — admin account creation
- `pages/logout.php` — destroys the session and clears cookies
- `pages/role.php` — role handling / role-based redirection helper
- `pages/session_check.php` — guards protected pages + enforces the 15-minute timeout
- PHP versions of the site pages: `assignment.php`, `attendance.php`, `contact.php`,
  `courses.php`, `index.php`, `profile.php`

**Files removed:** the corresponding `.html` versions (`assignment.html`,
`attendance.html`, `contact.html`, `courses.html`, `index.html`, `profile.html`).

**Files modified:** `pages/login.php`, `pages/event_register.php`, `pages/events.php`,
`data/students.csv`.

**Key details:**

- `login.php` verifies with `password_verify()`, calls `session_regenerate_id(true)`,
  stores `student_id`, `full_name`, `email`, `role` and `last_activity` in the session,
  updates `last_login`, and issues a hashed remember-me token.
- `session_check.php` blocks access when the session is missing/expired and enforces
  `15 * 60` seconds of inactivity.
- `logout.php` clears the session and cookies.
- Role-based redirection sends admins to `Admin.php` and students to `index.php`.

---

## Practical 11 — Student Management CRUD with Search and Filter

**Folder:** `practical11/`

**Objective:** Provide an admin-only student management module with create, read,
update, delete, search and course filtering.

**Files added (vs. practical10):**

- `pages/adminpages/manage_students.php`
- `pages/adminpages/events.php` (event listing moved from `pages/events.php`)

**Files removed:** `pages/events.php` (relocated under `pages/adminpages/`).

**Files modified:** `pages/Admin.php`, `pages/assignment.php`, `pages/attendance.php`,
`pages/contact.php`, `pages/courses.php`, `pages/event_register.php`,
`pages/index.php`, `pages/profile.php`, `pages/session_check.php`.

**Key details:** `manage_students.php` implements the full CRUD cycle with CSRF tokens,
server-side validation, email validation, `password_hash()` on create/update, a
`SELECT DISTINCT course` filter dropdown and paginated-style search using prepared
statements. Admin pages are grouped under `pages/adminpages/` from this practical
onward.

---

## Practical 12 — Event Management CRUD with Poster Upload

**Folder:** `practical12/`

**Objective:** Extend the events module with full CRUD and validated poster image
uploads.

**Files added:** none (event CRUD already existed; this practical enriches it).

**Files modified:**

- `pages/adminpages/events.php` — adds poster upload and richer fields
- `database/studenthub.sql` — schema/data updates
- `data/student.json`, `data/students.csv` — sample data updates

**Key details:** `events.php` adds event fields (`category`, `status`) and poster
handling: it stores uploads under `uploads/event_posters/`, enforces a **2 MB** limit,
whitelists **jpg/png/webp**, verifies the real MIME type with `finfo`, uses
`move_uploaded_file`, and removes the old poster when replaced or deleted.

---

## Practical 13 — PHP JSON API + AJAX/Fetch CRUD (no page reload)

**Folder:** `practical13/`

**Objective:** Create PHP JSON API endpoints for students/events and consume them with
the Fetch API to perform list, add, update and delete operations asynchronously.

**Files added (vs. practical12):**

| File | Purpose |
|------|---------|
| `api/config.php` | API bootstrap: JSON headers, `json_response()`, `json_error()`, `require_login()`, `require_admin()`, `read_json_body()`, CSRF helpers, PDO guard |
| `api/students.php` | Student resource: `GET` (list + search/filter/sort/pagination, single by id), `POST` (create), `PUT` (update), `DELETE` |
| `api/events.php` | Event resource: `GET` (list + search/status filter, single by id), `POST`, `PUT`, `DELETE` |
| `pages/ajax_crud.php` | Admin-only dashboard UI for async student & event CRUD |
| `js/api.js` | Fetch-based client: request helper, loading/success/error states, debounced search, pagination, course options |

**Files modified:** `pages/Admin.php`, `pages/adminpages/manage_students.php`,
`pages/adminpages/events.php` (navigation links), `pages/session_check.php`
(fixed the hard-coded login path to point at `practical13`) and the SQL dump was
rebuilt as a complete, importable schema (see below).

**Key details:**

- The API returns consistent JSON (`{success, data, ...}` or `{success:false, error}`)
  with proper HTTP status codes (200/201/400/401/403/404/409/419/422/500).
- Mutations require the **admin** role and a valid CSRF token supplied via the
  `X-CSRF-Token` header (validated against the session with `hash_equals`).
- `students.php` supports `search`, `course`, `role`, `sort`, `dir`, `page`, `limit`.
  `events.php` supports `search` and `status`.
- `js/api.js` shows loading, success and error states, debounces the live search
  (350 ms), renders pagination, and gracefully collects course filter options.

**Rebuilt database schema (shared with practical12/practical14):**
`database/studenthub.sql` now creates the `studenthub` database and the tables
`students` (with `role`, `last_login`, `remember_token_hash`), `events` (with
`category`, `status`, `poster_path`), `registrations` and `audit_logs`, plus seed
admin/student accounts. This fixes the earlier incomplete dump.

---

## Practical 14 — Admin Dashboard with RBAC, Dynamic Menu, and Audit Log

**Folder:** `practical14/`

**Objective:** Build an admin dashboard showing user statistics, student/event counts
and latest registrations; restrict access to admins; show a menu based on role; and
record important admin actions in `audit_logs`.

**Files added (vs. practical13):**

| File | Purpose |
|------|---------|
| `audit.php` | `log_audit()` helper — writes an audit entry (student, role, action, entity, IP) |
| `pages/menu.php` | `build_dynamic_menu()` / `render_dynamic_menu()` — role-based navigation |
| `pages/adminpages/audit_logs.php` | Admin-only audit log viewer with search/filter/pagination |

**Files modified:**

- `pages/Admin.php` — rewritten dashboard: student/admin/event/registration stats,
  latest registrations table, recent audit activity, a registrations-by-month chart and
  a dynamic admin menu
- `pages/index.php` — student dashboard now renders the dynamic role-based menu
- `pages/login.php`, `pages/logout.php` — audit entries for `login`,
  `login_remember` and `logout`
- `pages/adminpages/manage_students.php`, `pages/adminpages/events.php` — audit entries
  for student/event create/update/delete + Audit Log navigation link
- `api/students.php`, `api/events.php` — audit entries for API create/update/delete
- `pages/session_check.php` — login path updated to `practical14`

**Key details:**

- **RBAC:** every admin page/section checks `$_SESSION["role"] === "admin"` and returns
  HTTP 403 otherwise; the API enforces it via `require_admin()`.
- **Dynamic menu:** `menu.php` builds different menu items for `student` and `admin`
  roles; both dashboards render from it instead of hard-coded links.
- **Dashboard queries:** `SUM(CASE WHEN role = ...)` counts, `COUNT(*)` for events and
  registrations, a `JOIN` for the latest registrations, and a
  `GROUP BY DATE_FORMAT(registered_at, '%Y-%m')` query for the monthly chart.
- **Audit logging:** `log_audit()` records the actor, role, action, entity,
  entity id, details and client IP. Actions are logged for authentication
  (login/logout) and every admin create/update/delete (both UI and API paths), and are
  reviewable in `pages/adminpages/audit_logs.php`.

---

## Running the project locally

The database-backed practicals (**8–14**) require PHP + MySQL (XAMPP/WAMP/LAMP).

1. Start **Apache** and **MySQL**.
2. Import the schema for the practical you want to run (creates the `studenthub`
   database). Import, for example:
   `practical14/database/studenthub.sql` via phpMyAdmin.
3. Confirm the connection settings in that practical's `db.php`
   (default: host `localhost`, database `studenthub`, user `root`, empty password).
4. Open the login page, e.g.:
   `http://localhost/WDF_LAB/practical14/pages/login.php`
   (adjust the path to match your web root).

### Demo credentials (seeded by the SQL dump)

| Role | Student ID | Password |
|------|-----------|----------|
| Admin | `ADMIN001` | `Admin@123` |
| Student | `25DCE003` | `Student@123` |

Static practicals (**1–7**) can be opened directly in a browser; practical 6's JSON
fetch requires being served over HTTP (not `file://`).

---

## Conventions used across the hands-on practicals

- **Database access:** a shared `db.php` exposing `$conn` (PDO, exceptions enabled);
  pages use `require_once "../db.php"` and prepared statements with named placeholders.
- **Sessions:** `pages/session_check.php` guards protected pages and enforces a
  15-minute inactivity timeout.
- **RBAC:** `$_SESSION["role"]` values are `student` and `admin`; admin pages live under
  `pages/adminpages/`.
- **Security:** CSRF tokens (`$_SESSION["csrf_token"]`, with `hash_equals`) on forms;
  the JSON API uses a session token validated from the `X-CSRF-Token` header.
- **Output escaping:** the `e()` helper (`htmlspecialchars(..., ENT_QUOTES, "UTF-8")`)
  is used for all dynamic output.
- **Comments:** in line with the project requirement, the source in this repository is
  kept free of code comments (database executable comment directives such as
  `/*!40101 ... */` are preserved as they are functionally required by MySQL).

---

## Repository

https://github.com/chauhand2463/WDF_LAB
