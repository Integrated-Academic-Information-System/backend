# Community Project Backend

Laravel backend for a school result management system. This API handles authentication for three user types, student profile access, marks entry, dropdown data for forms, and report export.

## Overview

The application is built with Laravel 12 and uses JWT authentication for login sessions. It stores school data such as students, teachers, grades, subjects, terms, exam years, marks, and pivot tables that define access rules and subject assignments.

The backend is designed around these roles:

- Admin: full access to protected operations.
- Teacher: can be a subject teacher or class incharge.
- Student: can log in and view their own profile.

## Tech Stack

- PHP 8.2+
- Laravel 12
- JWT auth via `tymon/jwt-auth`
- Laravel Sanctum installed in the project, but the current API uses JWT-based login flow
- Vite for frontend asset building

## Main Features

- Login for admins, teachers, and students
- JWT token issuance and logout
- Student list filtering by grade, subject, term, and exam year
- Student profile endpoint with core and bucket subjects
- Marks create/update flow with access checks
- Dropdown data endpoint for forms
- CSV report export by class, subject, term, and exam year

## Authentication Flow

The login endpoint accepts a username and password and determines the account type by the username pattern:

- Admins use `user_name`
- Students use `reg_no`
- Teachers use `user_name`

Successful login returns:

- `token`
- `token_type`
- `expires_in`
- `user_name`
- `teacher_status` for teachers
- `teacher_id` for teachers

Teacher status values currently used by the code:

- `0`: subject teacher
- `1`: class incharge

## API Endpoints

Base path: `/api`

### Public endpoints

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/login` | Authenticate admin, teacher, or student and return a JWT token |
| GET | `/students` | Fetch students with optional filters |
| POST | `/save-marks` | Save or update marks data |
| GET | `/form-data` | Fetch terms, grades, subjects, exam years, and editable subject IDs |
| GET | `/generate-report` | Stream a CSV class report |

### Protected student endpoints

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/student/profile` | Return the authenticated student profile |
| POST | `/student/logout` | Logout a student session |

### Protected admin endpoint

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/admin/logout` | Logout an admin session |

### Protected teacher endpoint

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/teacher/logout` | Logout a teacher session |

## Endpoint Details

### POST /api/login

Request body:

```json
{
	"user_name": "admin01",
	"password": "secret"
}
```

Response includes the JWT token and role details.

### GET /api/students

Optional query parameters:

- `role`
- `teacher_id`
- `grade_id`
- `term_id`
- `subject_id`
- `exam_year_id`

This endpoint can filter students by grade and can also attach marks when all of `grade_id`, `term_id`, `subject_id`, and `exam_year_id` are supplied.

### POST /api/save-marks

Expected fields:

- `marks_data`
- `exam_year_id`
- `term_id`
- `grade_id`
- `subject_id`

Each item in `marks_data` should contain a `student_id` and `mark` value. Existing marks are updated, and missing records are created.

### GET /api/form-data

Optional query parameters:

- `role`
- `teacher_id`

Response data includes:

- `terms`
- `grades`
- `subjects`
- `exam_years`
- `editable_subject_ids`

### GET /api/generate-report

Query parameters:

- `grade_id`
- `subject_id`
- `exam_year_id`
- `term_id`

Returns a CSV stream named like `Class_Report_YYYY-MM-DD.csv`.

### GET /api/student/profile

Returns the authenticated student details plus:

- grade name
- core subjects for the grade
- bucket subjects assigned to the student

## Data Model

Primary models in the backend:

- `Admin`
- `Student`
- `Teacher`
- `Grade`
- `Subject`
- `Term`
- `Mark`
- `StudentHasMark`

Important tables and pivots used by the logic:

- `admins`
- `students`
- `teachers`
- `grades`
- `subjects`
- `terms`
- `exam_years`
- `marks`
- `student_has_marks`
- `teacher_has_grade`
- `teacher_has_subject`
- `grade_has_subject`
- `bucket_subjects`
- `subject_has_bucket_subject`
- `student_has_bucket_subject`
- `teacher_bucket_subject`

Relationship summary:

- A student belongs to a grade.
- Grades map to core subjects through a pivot table.
- Teachers are linked to grades and subjects for access control.
- Students can also have bucket subjects through the bucket-subject pivots.
- Marks are stored separately and linked back through `student_has_marks`.

## Access Rules

The code applies these important checks:

- Non-admin users must provide `teacher_id` when requesting form data.
- Subject teachers can only see the subjects they teach.
- Class incharges can see grades assigned to them and may view broader subject data for reports.
- Marks can only be saved for a grade and subject a teacher is allowed to access.

## Local Setup

### Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- A database supported by Laravel, such as MySQL or SQLite

### Installation

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

If you are using a local development workflow, you can also run:

```bash
composer run dev
```

That command starts the Laravel server, queue listener, log watcher, and Vite dev server together.

### Running the app manually

```bash
php artisan serve
npm run dev
```

## Environment Variables

Typical variables you will need in `.env`:

- `APP_NAME`
- `APP_ENV`
- `APP_KEY`
- `APP_DEBUG`
- `APP_URL`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- JWT-related settings from `config/jwt.php`

## Testing

Run the test suite with:

```bash
php artisan test
```

The Composer script also clears config before running tests:

```bash
composer test
```

## Project Structure

- `app/Http/Controllers` contains the API logic.
- `app/Models` contains the Eloquent models.
- `database/migrations` defines the schema.
- `routes/api.php` defines the API endpoints.
- `routes/web.php` currently serves the default welcome page.

## Notes

- The project currently relies on JWT auth for API logins.
- Some route comments mention testing or optional access patterns; the README reflects the behavior in the controller code.
- If you want, this README can be extended with endpoint examples, request/response samples, or Postman collection notes.
