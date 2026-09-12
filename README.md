# EDU-AI

AI-powered e-learning and online testing platform built on Laravel, Inertia.js, and React.

## Overview

EDU-AI lets instructors build courses with a rich-text CMS, run auto-graded and
essay-based online quizzes, and gives students an AI tutor grounded in the
lesson they're reading. Instructors can generate draft quiz questions with
Gemini and get AI-assisted scoring suggestions for essay answers — both always
reviewed and finalized by a human before anything is published or graded.

## Features

- **Roles & authorization**: Admin / Instructor / Student, enforced with Laravel Policies (not just the UI).
- **Course CMS**: courses, sections, and lessons with drag-free up/down reordering, CKEditor 5 rich text, and server-side HTML sanitization.
- **Student learning**: enrollment, a lesson viewer with curriculum navigation, and progress tracking.
- **Online testing**: multiple-choice, true/false, short-answer, and essay questions; auto-grading for objective questions.
- **AI Tutor**: a chat panel scoped to the current lesson's content, with conversation history and prompt-injection–resistant context handling.
- **AI Quiz Generator**: generates a structured (JSON) draft of quiz questions from a lesson, which the instructor reviews, edits, and selectively saves — nothing is published automatically.
- **AI Essay Grading Assistant**: suggests a score and rubric-style breakdown for essay answers; the teacher's score is always the one that counts.
- **AI usage logging & rate limiting**: every AI call is logged (feature, tokens, duration, status) and rate-limited per user.

## Stack

- Laravel 13 (PHP 8.4)
- Inertia.js v3 + React 19 + TypeScript
- MySQL
- Vite (via `vite-plus` / `laravel-vite-plugin`)
- Laravel Fortify (authentication, 2FA, passkeys)
- CKEditor 5 (`@ckeditor/ckeditor5-react`)
- Symfony HTML Sanitizer
- Google Gemini API

## Requirements

- PHP 8.4+
- Composer
- Node.js 20+ and npm
- MySQL 8+

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create a MySQL database and set the connection details in `.env` (see
[Environment variables](#environment-variables) below), then:

```bash
php artisan migrate
php artisan storage:link
```

## Environment variables

In addition to the standard Laravel database/mail/queue variables, configure:

```
GEMINI_API_KEY=your-gemini-api-key
GEMINI_MODEL=gemini-2.5-flash
```

`config/services.php` also accepts the legacy `API_KEY` / `API_MODAL` variable
names as a fallback if that's what your environment already defines, but new
setups should use `GEMINI_API_KEY` / `GEMINI_MODEL`.

The Gemini API key is only ever read on the server (`App\Services\AI\GeminiService`)
and is never sent to the frontend, logged, or stored in the database.

## Database migrations & seed data

```bash
php artisan migrate
php artisan db:seed
```

The seeder creates three demo accounts (password for all: `password`):

| Role       | Email                    |
| ---------- | ------------------------ |
| Admin      | `admin@edu-ai.test`      |
| Instructor | `instructor@edu-ai.test` |
| Student    | `student@edu-ai.test`    |

...along with two published courses (each with sections, lessons, and a
quiz), an enrollment, some lesson progress, and a submitted quiz attempt, so
the app has realistic data to click through immediately after setup.

## Running the dev server

```bash
composer run dev
```

This runs the Laravel server, queue worker, log watcher, and Vite dev server
together. Alternatively, run `php artisan serve` and `npm run dev` in separate
terminals.

## Running tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database (see `phpunit.xml`) and never
call the real Gemini API — AI-dependent tests bind a fake `AIProvider`
(`Tests\Support\FakeAIProvider`) or fake the Gemini HTTP endpoint with
`Http::fake()`.

Other useful checks:

```bash
vendor/bin/pint            # PHP code style
vendor/bin/phpstan analyse  # static analysis
npm run check               # frontend lint + format check
npm run types:check         # TypeScript
npm run build                # production frontend build
```

## Project architecture

```
React / Inertia
      ↓
Laravel Routes  (routes/web.php, courses.php, quizzes.php, student.php, ai.php, essays.php)
      ↓
Controllers      (thin: validate via Form Requests, authorize via Policies, delegate to Services)
      ↓
Services          (business logic, incl. app/Services/AI/*)
      ↓
Models / MySQL
      ↓
Gemini API        (only from app/Services/AI/GeminiService, via the AIProvider interface)
```

Key AI-related classes:

- `App\Services\AI\AIProvider` — interface abstracting the AI backend, so Gemini can be swapped later.
- `App\Services\AI\GeminiService` — the only class that talks to the Gemini HTTP API.
- `App\Services\AI\TutorService`, `QuizGeneratorService`, `EssayGradingService` — feature-specific prompt building, response validation, and `ai_logs` logging.
- `App\Models\AiConversation` / `AiMessage` / `AiGeneration` / `AiLog` — persisted conversation history, generation records, and usage logs.

## License

MIT
