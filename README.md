# Dreamers Assessment – Project & People Reviews API

This is a Laravel 12 REST API that lets employees review projects, team members, and managers. It models an organisation with executives, managers, associates, and internal advisors, enforcing fine‑grained access rules over who can see and manage what.

---

## Tech Stack

- **Framework**: Laravel 12 (PHP 8.2+ / 8.4)
- **Database**: MySQL
- **Server**: Laravel Herd or `php artisan serve`
- **Testing**: PHPUnit / Laravel test runner

---

## Setup

### 1. Clone & install dependencies

```bash
git clone <your-repo-url> dreamers_assessment
cd dreamers_assessment

composer install
```

### 2. Environment & database

Copy the example env and configure MySQL:

```bash
cp .env.example .env
```

In `.env`, set at least:

```
APP_NAME="Dreamers Assessment"
APP_ENV=local
APP_URL=http://dreamers_assessment.test   # if using Herd

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dreamers_assessment
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password
```

Create the database in MySQL:

```sql
CREATE DATABASE dreamers_assessment CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Generate the app key:

```bash
php artisan key:generate
```

### 3. Migrate & seed

```bash
php artisan migrate --seed
```

This creates:
- Roles: `executive`, `manager`, `associate`, `advisor`
- Users for each role
- Teams, team memberships (with `is_manager`)
- Projects and team assignments
- Reviews for projects and people

---

## Running the app

### With Laravel Herd

If you opened the project via Herd, it will serve:

- Base URL: `http://dreamers_assessment.test` (or `https://...` depending on Herd)
- APIs are under `/api`, for example:
  - `GET http://dreamers_assessment.test/api/reviews`

### With `php artisan serve` (fallback)

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

- Base URL: `http://127.0.0.1:8000`
- APIs under `/api` (e.g. `http://127.0.0.1:8000/api/reviews`)

---

## Authentication for testing (X-User-Id)

For the assessment, full auth is replaced with a simple impersonation middleware to make Postman testing easy.

- Add header `X-User-Id: {user_id}` to any request.
- The app will treat that user as the authenticated user for the request.

You can find seeded user IDs by checking the database (`users` table) or printing them in a tinker session:

```bash
php artisan tinker
>>> App\Models\User::with('role')->get(['id','name','role_id']);
```

Example roles:
- Executive user → can manage users, teams, projects and see all reviews with reviewer names.
- Manager / associate / advisor → restricted views as per the rules below.

---

## Key API Endpoints (JSON)

All responses are JSON. All endpoints expect `Accept: application/json` (optional but recommended).

### Reviews

Middleware: `impersonate`, `role:executive,manager,associate,advisor`

- **List reviews**
  - `GET /api/reviews`
  - Visibility depends on role:
    - **executive**: sees all reviews + reviewer names
    - **manager**: sees reviews of themselves, their team members, and their projects
    - **associate**: sees reviews of themselves and their team’s projects
    - **advisor**: sees project reviews for projects they advise
  - Non‑executives do not see the `reviewer` relation.

- **Show one review**
  - `GET /api/reviews/{review}`  
    Same visibility rules as above.

- **Create review**
  - `POST /api/reviews`
  ```json
  {
    "project_id": 1,
    "reviewee_id": 2,
    "content": "Great collaboration",
    "rating": 5
  }
  ```

- **Update own review**
  - `PUT /api/reviews/{review}`
  - Only the reviewer (or an executive for delete) is allowed.

- **Delete review**
  - `DELETE /api/reviews/{review}`
  - Allowed for the reviewer or an executive.

### Users (executive only)

Middleware: `impersonate`, `role:executive`

- `GET /api/users` – list users with roles and teams.
- `POST /api/users` – create user
```json
{
  "name": "New User",
  "email": "new@example.com",
  "password": "secret123",
  "role_id": 1,
  "team_ids": [1],
  "manager_team_ids": [1]
}
```
- `GET /api/users/{user}` – show one user.
- `PUT /api/users/{user}` – update details and team assignments.
- `DELETE /api/users/{user}` – delete user.

### Teams (executive only)

- `GET /api/teams` – list teams with users (manager flag) and projects.
- `POST /api/teams` – create team
```json
{
  "name": "Team Alpha",
  "description": "Core product team",
  "user_ids": [2, 3, 4],
  "manager_ids": [2]
}
```
- `GET /api/teams/{team}`
- `PUT /api/teams/{team}` – update info and user assignments.
- `DELETE /api/teams/{team}`

### Projects (executive only)

- `GET /api/projects` – list projects with teams and reviews.
- `POST /api/projects` – create project and assign teams/advisors
```json
{
  "name": "Project Apollo",
  "description": "New initiative",
  "team_ids": [1, 2],
  "advisor_ids": [5, 6]
}
```
- `GET /api/projects/{project}`
- `PUT /api/projects/{project}` – update details, teams, advisors.
- `DELETE /api/projects/{project}`

---

## Architecture Overview

**Models**
- `User` – belongs to `Role`; belongsToMany `Team` (with `is_manager`); has many `reviewsWritten` / `reviewsReceived`; belongsToMany `advisingProjects`.
- `Role` – has many `User`.
- `Team` – belongsToMany `User` (pivot `is_manager`); belongsToMany `Project`.
- `Project` – belongsToMany `Team`; belongsToMany `User` as `advisors`; has many `Review`.
- `Review` – belongs to `reviewer` (User), `reviewee` (User), and `Project`.

**Controllers (API)**
- `ReviewController` – review CRUD + role‑based visibility rules.
- `UserController` – exec‑only user management and team assignment.
- `TeamController` – exec‑only team management and user assignment.
- `ProjectController` – exec‑only project management, team linkage, and advisors.

**Middleware**
- `RoleMiddleware` (`role:`) – checks `user->role->name` against allowed roles.
- `ImpersonateUser` (`impersonate`) – header-based auth (`X-User-Id`) for local testing.

---

## Testing

Run all tests:

```bash
php artisan test
```

Included tests:
- `tests/Feature/ExecutiveOnlyEndpointsTest.php` – ensures only executives can access `/api/users` and `/api/projects`.
- `tests/Feature/ReviewVisibilityTest.php` – ensures executives can see reviews and non-executives do **not** see `reviewer` relation.

Unit tests can be added for model helpers if needed, but feature tests already cover main rules.
