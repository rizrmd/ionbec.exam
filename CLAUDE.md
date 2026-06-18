- to deploy copy file to ssh root@cf.avolut.com in docker container app-okksscs4w0s8oc0go0k4cg8k
- this project runs on the server as a Coolify app
- to query use local psql; PostgreSQL connection info is in `.coolify.env` (DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD)
- when querying mysql use local mysql

## CRITICAL: PostgreSQL sequences go out of sync after data import

### Symptom
Inserts fail with a duplicate-key error, e.g.:
```
SQLSTATE[23505]: Unique violation: 7 ERROR: duplicate key value violates unique constraint "takers_pkey"
DETAIL: Key (id)=(291) already exists.
```
Seen on **input peserta** (`takers`), **save soal** (`items`/`questions`/`answers`), groups, etc.

### Cause
The database was populated by importing rows with **explicit ids**, which does NOT advance the
auto-increment sequence. The next INSERT then reuses an existing id and violates the PK.
This is NOT a deploy/app-code bug — it is a database state issue.

### Fix (run after ANY data import, and whenever a `*_pkey` duplicate-key error appears)
Run the idempotent, safe-to-rerun reset script:
```bash
export PGPASSWORD=$(grep -iE "^DB_PASSWORD" .coolify.env | cut -d= -f2)
psql -h <DB_HOST> -p <DB_PORT> -U <DB_USERNAME> -d <DB_DATABASE> -f scripts/maintenance/reset_sequences.sql
```
It resets every `<table>_id_seq` to `MAX(id)` and skips empty tables. Single table only:
```sql
SELECT setval('public.<table>_id_seq', (SELECT MAX(id) FROM <table>));
```

### Note
- `attachments.id` and `sessions.id` have NO sequence/default attached (skipped by the script).
  If an `attachments_pkey` duplicate-key appears, it needs separate handling (no auto-increment),
  not a sequence reset.

## File Organization Guidelines

### IMPORTANT: Keep Root Directory Clean
- **NEVER create files in the root directory** - only Laravel core files should remain there
- All utility scripts go in appropriate `scripts/` subdirectories:
  - `scripts/debug/` - Debug and analysis scripts
  - `scripts/utils/` - Utility scripts
  - `scripts/maintenance/` - Maintenance scripts
  - `scripts/imports/` - Import scripts
  - `scripts/shell/` - Shell scripts
- All data files go in `data/` subdirectories:
  - `data/csv/` - CSV files
  - `data/sql/` - SQL backup files
  - `data/` - JSON and other data files
- Documentation goes in `docs/`
- Backups go in `data/sql/`

## Route Generation Fix for Relative URLs

### Problem
Links were generating absolute URLs with localhost:8000 instead of relative URLs, causing broken links across different domains.

### Solution
1. **Custom Route Helper** (`resources/js/Libs/ziggy.js`):
   - Forces `absolute = false` for all route calls
   - Converts any absolute URLs to relative URLs
   - Handles undefined route names gracefully

2. **Ziggy Configuration** (`resources/views/app.blade.php`):
   - Sets Ziggy URL to current request scheme and host
   - Removes port to ensure relative URL generation

### Deployment Process
When deploying route generation fixes:
```bash
# Copy files to correct container location (/var/www/ not /var/www/html/)
scp resources/js/Libs/ziggy.js root@cf.avolut.com:/tmp/ziggy.js
ssh root@cf.avolut.com "docker cp /tmp/ziggy.js app-okksscs4w0s8oc0go0k4cg8k:/var/www/resources/js/Libs/ziggy.js"

# Rebuild assets from correct directory
ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && npm run production'"

# Clear Laravel caches
ssh root@cf.avolut.com "docker exec app-okksscs4w0s8oc0go0k4cg8k bash -c 'cd /var/www && php artisan cache:clear && php artisan config:clear && php artisan view:clear && php artisan route:clear'"
```

### Key Points
- Laravel app is in `/var/www/` not `/var/www/html/`
- Nginx serves from port 3000 internally
- Always test with console logs to verify route helper is working
- Check Ziggy config shows correct domain, not localhost
- VERY IMPORTANT: do not run database seed, do not truncate any table.
- **CRITICAL: Never create files in root directory - use proper organized folders!**
- to rebuild rust-service we just need to commit and push the project, it will automatically build the rust service

## Exam Token Submission and Waiting Room Logic

### Frontend Exam Token Submission Flow

#### 1. Token Entry Points
There are two main ways users can submit exam tokens:

**B. Welcome Page (`resources/js/Pages/Welcome.vue`)**
- Token input field on the main welcome page
- Supports Enter key submission: `@keydown.enter="loginExam()"`
- Uses Inertia.js form submission: `form.post(route('exam.login'))`

#### 2. Client-Side Validation
Before submission, the frontend:
- Trims whitespace from the token
- Ensures token is not empty
- Converts to uppercase format
- Shows validation errors if needed

#### 3. Submission Methods

**Primary Method (Direct URL Navigation):**
```javascript
window.location.href = `/exam/${form.token.trim()}`
```
- Redirects to `/exam/{token}` route
- No AJAX call - uses direct URL navigation
- Handled by `PublicTokenLoginController::tokenLogin()`

**Alternative Method (Form POST):**
```javascript
form.post(route('exam.login'))
```
- Uses Inertia.js for form submission
- POST request to `exam.login` route
- Handled by `ExamController::login()`

### Waiting Room Determination Logic

#### 1. Primary Decision Logic (PublicTokenLoginController)

The main decision logic is located in `app/Http/Controllers/PublicTokenLoginController.php` at lines 114-125:

```php
// Check if should redirect to waiting room or directly to exam
if ($delivery->automatic_start && strtotime($delivery->scheduled_at) > strtotime('now')) {
    return redirect()->route('exam.waiting-room');
}
return redirect('/exam');
```

#### 2. Key Conditions for Waiting Room Access

The user is sent to the waiting room **ONLY if BOTH conditions are met**:
1. **`$delivery->automatic_start` is `true`**
2. **`strtotime($delivery->scheduled_at) > strtotime('now')`** (scheduled time is in the future)

#### 3. Decision Flow
```
User enters token → Validate delivery_taker record
├── Is delivery expired? → Show error
├── Already logged in? → Preserve session
└── Check routing conditions:
    ├── automatic_start = false → Direct to exam
    ├── scheduled_at is in past → Direct to exam
    ├── scheduled_at is in future AND automatic_start = true → Waiting room
    └── Otherwise → Direct to exam
```

### Delivery Field Confusion: automatic_start vs is_anytime

#### 1. The Contradiction
The `automatic_start` and `is_anytime` fields in the `deliveries` table are confusing and appear contradictory due to inverse relationship during creation:

```php
// From DeliveryController.php lines 115-116
$delivery->automatic_start = $request->automatic_start;
$delivery->is_anytime = !$request->automatic_start;  // Inverted!
```

#### 2. Intended Meaning

**`automatic_start`**: Controls **when the timer starts**
- `TRUE`: Timer starts automatically at scheduled time
- `FALSE`: Timer starts when user begins exam

**`is_anytime`**: Controls **when you can take the exam**
- `TRUE`: Can take anytime within a window
- `FALSE`: Must take at specific scheduled time

#### 3. The System's Actual Behavior

The inverse logic means only these two combinations work properly:
- **Scheduled Mode**: `automatic_start=TRUE, is_anytime=FALSE`
- **Flexible Mode**: `automatic_start=FALSE, is_anytime=TRUE`

#### 4. Logical Combinations That Should Exist

| Mode | automatic_start | is_anytime | Description |
|------|-----------------|------------|-------------|
| **Scheduled Proctored** | `TRUE` | `FALSE` | Must start at exact time, auto-timer |
| **Take-Home Exam** | `FALSE` | `TRUE` | Start anytime, manual timer |
| **Flexible Window** | `TRUE` | `TRUE` | Start anytime, auto-timer |
| **Manual Scheduled** | `FALSE` | `FALSE` | Must start at time, manual timer |

#### 5. Waiting Room Logic Based on Fields

- **Waiting Room Eligible**: `automatic_start=TRUE` AND `scheduled_at > now()`
- **Direct Exam Access**: All other combinations
- **Is Anytime Override**: When `is_anytime=TRUE`, scheduled time constraints are relaxed

#### 6. Common Usage Patterns

From the codebase analysis, the system is commonly used for:
- **Scheduled Proctored Exams**: `automatic_start=TRUE, is_anytime=FALSE`
- **Take-Home Exams**: `automatic_start=FALSE, is_anytime=TRUE`
- **Interviews**: `automatic_start=FALSE, is_anytime=FALSE`

#### 7. Recommendation

The fields should be decoupled to allow all four combinations, or replaced with a single `exam_mode` enum field with clearer options like:
- `scheduled_proctored`
- `take_home`
- `flexible_window`
- `interview_mode`

### Route Structure

- **`/exam/{token}`** (`exam.token.login`): Token validation and initial routing decision
- **`/exam/waiting-room`** (`exam.waiting-room`): Waiting room interface
- **`/exam`** (`exam.main`): Direct exam access
- **`/exam-login`**: Shows the token input form
- **`/exam-logout`**: Handles logout and token invalidation

### Key File Locations

| Component | File Path | Purpose |
|-----------|-----------|---------|
| Welcome Page Token | `/resources/js/Pages/Welcome.vue` | Alternative token input on main page |
| Public Token Controller | `/app/Http/Controllers/PublicTokenLoginController.php` | Backend token processing and waiting room logic |
| Waiting Room Controller | `/app/Http/Controllers/Exam/WaitingRoomController.php` | Waiting room interface and time monitoring |
| Exam Controller | `/app/Http/Controllers/Exam/ExamController.php` | Alternative exam login handling |
| Delivery Model | `/app/Models/Deliveries/Delivery.php` | Delivery data model and attributes |
| Exam Middleware | `/app/Http/Middleware/ExamMiddleware.php` | Session validation and route protection |