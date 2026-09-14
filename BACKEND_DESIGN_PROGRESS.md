# SIPRAJA Backend Design Progress

Last updated: 2026-09-10 (all primary modules integrated, exports and live header features implemented)

## Current stage

All 15 planned application tables and primary frontend modules are connected to the Laravel backend. Authentication and role authorization, citizen reporting and tracking, admin report processing, activity management and evaluation, organization/personnel management, archives, assets, dashboards, maps, feedback, audit monitoring, notifications, and filtered activity exports are implemented and covered by automated tests.

## Implementation checkpoint

- Header notifications poll the authenticated user's latest database notifications every 15 seconds. The unread badge and list update without reloading the page, and a toast announces a newly increased unread count.
- Global header search is connected on admin and super-admin layouts. It searches reports, activities, and officers with role-appropriate links, debounced requests, bounded results, validation, authentication, and rate limiting.
- New reports submitted by citizens or recorded by an admin create unread database notifications for every active admin. Blocked accounts and roles without access to admin report details are excluded.
- Admin activity exports respect search, type, month, and date-range filters. Excel uses complete Indonesian fields; PDF includes the required columns, embedded images, filtered-period heading, and dated approval signature.
- CRUD confirmation and success feedback is applied across the implemented admin and super-admin management interfaces.
- Current verification: 128 PHPUnit tests passed with 742 assertions; Blade compilation, Laravel Pint, and `git diff --check` passed.

- An admin can now complete only an `in_progress` report through the detail-page completion form. The form supports an optional editable template, operational category, date/time, location, at least one active officer, handling result, and 1-3 JPG/JPEG/PNG/WEBP evidence images up to 5 MB each.
- Completion stores one linked `external` activity, officer assignments, activity attachments, the report's `completed` state and timestamp, status history, and audit record. The original report remains present and unchanged except for completion fields.
- The operation uses a locked report row and one database transaction. Stale or duplicate requests, inactive officers, and invalid templates are rejected; uploaded files are removed when the transaction fails.
- The completed report detail displays its linked external activity, assigned officers, and evidence metadata. The Acara & Kegiatan page now reads MySQL, so a newly completed report appears there as an external activity with its report code and link back to the report detail.
- The activities list supports validated month, date-range, and allowlisted type filters, shows officer counts without N+1 queries, excludes soft-deleted rows, and paginates 15 records per page. Prototype `localStorage` activity data has been removed from this page.
- Admins can create internal activities through a database-backed form using an optional editable template, category, date/time, description, optional result, required location/coordinates, at least one active officer, and up to three optional documentation images.
- Internal activity creation atomically stores the activity, officer assignments, internal-only attachment metadata, and audit log. It always forces `type=internal`, `report_id=null`, and the authenticated admin as creator regardless of unexpected browser input.
- Internal activity detail is database-backed and available only for internal, non-deleted activities. External activities continue to use their linked report detail. The create page includes an interactive Kulon Progo map while retaining manual coordinate inputs.
- Admins can edit internal activity information, optional result, template, date/time, location, coordinates, and active officer assignments. Unexpected inputs cannot change its internal type, report relationship, or original creator.
- Editing can append documentation up to three total files while retaining existing files. Removing/replacing documentation remains deferred to the dedicated attachment-deletion workflow.
- Updates lock the activity row, re-check mutable template/officer eligibility and the total attachment count, and reject stale forms before writing. New files are cleaned up whenever the transaction fails, and successful updates write an audit record.
- Internal activity documentation can now be previewed through an authenticated, admin-only streamed response with private/no-store caching, MIME allowlisting, `nosniff`, and a restrictive content security policy. Nested ownership checks prevent access to report, external-activity, public, unsupported, or another activity's attachment.
- Admins can permanently delete one internal documentation attachment without deleting its activity. The file is moved to quarantine before the metadata transaction; failures restore it, while success removes the quarantine file and records a non-sensitive audit entry. Missing physical files can still have stale metadata cleaned safely.
- Soft-delete activity, evaluation, and export controls remain deliberately disabled until their real database endpoints are implemented.
- Current full PHPUnit result: 57 tests passed with 399 assertions. Laravel Pint and `git diff --check` passed.

- Admins can now advance report processing sequentially from `submitted` to `received`, `verifying`, and `in_progress`. Each request validates its optional note, locks the report row, and atomically updates the report, appends status history, and writes a non-sensitive audit log.
- Status forms carry the page's current status. The locked database row must still match it, preventing duplicate clicks or stale pages from advancing a report more than once.
- Generic status advancement deliberately stops at `in_progress`; reaching `completed` is handled by the transactional completion workflow that also creates an external activity, assigns officers, and stores evidence.

- The admin report detail page now resolves reports by `reports.code` through route model binding and reads report fields, masked NIK, status history, status actor, attachment metadata, and feedback from MySQL.
- `ReportStatusHistory` and `Attachment` models/factories were added with typed Eloquent relations. `Report` now exposes eager-loadable histories and polymorphic attachments; the `report` morph alias is registered centrally.
- Detail-page status and completion controls now write to the real backend. Attachment delivery and report soft-deletion endpoints remain deferred.

- Authentication is now connected to the `users` table: login accepts username or email, rejects blocked accounts, rate-limits repeated failures, regenerates the session, redirects by the exact admin/super-admin role, and invalidates the session on logout.
- Admin and super-admin route groups are protected by authentication and separate role gates. The two roles intentionally cannot access each other's route group.
- The existing login UI now posts to the real backend. Citizen registration was removed, password recovery remains deferred with an instruction to contact the super admin, and both dashboard layouts use a CSRF-protected logout form.

- `DatabaseSeeder` now calls an idempotent `DevelopmentSeeder`. It creates exactly two connected development records in each application table and small placeholder files in Laravel's local storage for seeded attachment/archive metadata. Running it repeatedly does not duplicate the known development records.
- Seeded authentication accounts represent one admin and one super admin. Their credentials are development-only and must be replaced before production.
- Laravel infrastructure tables (`sessions`, cache, queue, and `migrations`) remain available as agreed and are not manually seeded.

- Final schema migrations ran successfully in batch 6: `archives` (14 columns), `assets` (13), Laravel's `notifications` (8), and `audit_logs` (12). Direct MySQL inspection confirmed their indexes, JSON columns, soft-delete columns, and foreign keys.
- Archive/asset creator references use RESTRICT to retain ownership history. Audit actor deletion uses SET NULL. Laravel notifications use the framework's standard polymorphic recipient columns and UUID primary key.
- All planned migrations now report `Ran`. Full PHPUnit result: 4 tests passed with 14 assertions. Pint passed for all changed PHP files.
- The MySQL schema is complete for the agreed 15-table design. Models/factories now cover the report-completion path, while models and application behavior for the remaining modules are still incremental work.
- Organization/personnel migrations ran successfully in batch 5: zones (8 columns), units (12), officers (16), and activity-officer pivot (7). The leader FK was added in a separate migration after officers existed; rollback removes that FK before dropping officers.
- MySQL inspection confirmed all eight new foreign keys and unique constraints (zone/unit codes, employee number, optional officer account, activity/officer pair). Pint passed.
- Organizational references and assignment references use RESTRICT on permanent deletion to retain relationships. Officers' optional account reference uses SET NULL. Soft-delete columns exist for zones, units, and officers; models and access behavior remain to be implemented.
- Later backend validation must prevent unit hierarchy cycles and enforce eligible officer assignment. Foreign keys alone do not enforce these business rules.
- `activity_templates` (8 columns) and `activities` (21 columns) migrated in batch 4. MySQL inspection confirmed InnoDB, utf8mb4, the unique nullable report reference, type/date index, and four activity foreign keys. Pint passed.
- Activity foreign keys restrict permanent deletion of referenced reports and creator accounts; nullable template/evaluator references use SET NULL. The report uniqueness also covers soft-deleted activities.
- Activity/template models, automatic report-completion workflow, the database-backed admin activities list, internal activity create/detail/edit, and internal documentation preview/removal are implemented. Activity soft deletion, evaluation, and exports remain pending.
- `attachments` migrated successfully in batch 3. Its 12 columns, compound owner index, and uploader foreign key were inspected on MySQL; Pint passed.
- User-approved attachment deletion policy: parent soft deletion retains attachment metadata and physical files while closing access. Explicit deletion of an individual attachment permanently deletes its metadata and physical file. This policy is planned for the upload/access backend and is not implemented by the migration itself.
- `attachments.uploaded_by_user_id` uses `ON DELETE SET NULL`. The polymorphic owner pair has no database foreign key; the later application layer must validate owner existence and authorization. No attachment model, upload controller, file deletion, or frontend integration has been added at this stage.
- `report_status_histories` migrated successfully in batch 2; its six columns, compound timeline index, and both foreign keys were inspected directly on MySQL.
- User-approved deletion policy: reports use `deleted_at` soft deletion. History rows prevent permanent deletion of their report (`ON DELETE RESTRICT`). Permanently deleting an admin sets the history's `changed_by_user_id` to null (`ON DELETE SET NULL`) and retains the history.
- No history model, controller, or frontend integration has been added in this migration-only stage.
- `.env` is configured locally for the `polpp` MySQL database and remains ignored by Git.
- The originally supplied port `89` was verified to be an Apache HTTP endpoint. MySQL was detected and successfully connected on port `3306`.
- `users`, `guest_accesses`, and `reports` exist in MySQL using InnoDB and `utf8mb4_unicode_ci`.
- Foreign keys from `reports` to `users` and `guest_accesses` use `ON DELETE SET NULL`.
- Initial targeted indexes on report ownership, status, type, code, and timestamps exist.
- PHP backed enums, Eloquent models/relationships, factories, and standalone seeders exist for the initial domain.
- Reporter NIK uses an encrypted Eloquent cast and is hidden from model serialization.
- Database relationship/encryption tests pass on the isolated SQLite test connection.
- Full project test result at this checkpoint: 4 tests passed with 14 assertions.
- Laravel Pint passed for all changed PHP files.
- No domain seeders were run against the MySQL database, so it contains schema only unless pre-existing data was present.

## Fixed product decisions

- Citizens/reporters are guests. They do not register and do not log in.
- Application accounts exist only for `admin` and `super_admin`.
- Admin and super admin permissions follow the existing frontend and meeting decisions. Super admin does not automatically inherit every admin feature.
- Remove these frontend features from scope: citizen account registration, super-admin settings, and quick tasks.
- Password recovery has not yet been confirmed as in scope.
- Citizen reports are stored on the server. The browser stores a persistent opaque token used to retrieve reports created from that browser.
- Only a hash of the guest browser token is stored in the database.
- Losing the cookie, using private browsing, or changing browser/device means history is not automatically recovered. Cross-device recovery is still undecided.
- A public report code is a reference and must not be the only credential that exposes reporter personal data.
- A completed citizen report automatically creates one external activity linked to that report.
- Internal activities are created independently and have no report relationship.
- Completing a report, creating its external activity, assigning officers, saving evidence, updating report status, and creating status history must run in one database transaction.
- A report may have multiple evidence files. Files are stored through Laravel Storage; the database stores paths and metadata rather than BLOB data.
- Activity templates are optional helpers. Users may select a template, edit the populated fields, or enter a custom activity name.
- Feedback is one optional rating/comment per completed report.
- One optional evaluation is stored on an activity for the current scope.
- Use soft deletes for operational records where historical relationships must remain intact.

## Database decision

- Production database: MySQL using InnoDB, `utf8mb4`, foreign keys, and strict SQL mode.
- The local project currently uses SQLite; MySQL configuration has not been applied yet.
- Store fixed choices as `VARCHAR` columns cast to PHP backed enums and validate them in Form Requests. Do not rely on native MySQL ENUM unless this decision is revisited.
- Use pagination for list pages, eager loading for required relationships, targeted indexes, thumbnails for list views, and queues for large exports.

## Final application tables

The reviewed design contains 15 application/domain tables. Laravel framework tables such as `sessions`, `password_reset_tokens`, `cache`, and `jobs` remain separate.

### 1. users

Admin and super-admin accounts only.

- `id`: bigint PK
- `name`: varchar(255)
- `username`: varchar(100), unique
- `email`: varchar(255), nullable, unique
- `password`: varchar(255)
- `role`: varchar(30); `admin`, `super_admin`
- `status`: varchar(30); `active`, `blocked`
- `email_verified_at`: timestamp nullable
- `remember_token`: varchar(100) nullable
- timestamps

### 2. guest_accesses

Anonymous persistent browser identity; this is not a citizen account.

- `id`: bigint PK
- `token_hash`: char(64), unique
- `last_used_at`: timestamp nullable
- `expires_at`: timestamp nullable
- timestamps

Recommended initial cookie lifetime: one year. Final lifetime remains to be confirmed.

### 3. reports

- `id`: bigint PK
- `code`: varchar(30), unique
- `guest_access_id`: nullable FK to `guest_accesses`
- `created_by_user_id`: nullable FK to `users`
- `type`: varchar(30); `emergency`, `information`
- `category`: varchar(50) nullable; `public_order`, `environmental_cleanliness`, `security`
- `source`: varchar(30); `online`, `offline`
- `source_platform`: varchar(30) nullable; `website`, `whatsapp`, `instagram`, `x`, `facebook`, `tiktok`, `other`
- `reporter_name`: varchar(255) nullable
- `reporter_phone`: varchar(25)
- `reporter_nik`: text nullable, encrypted cast and hidden from serialization
- `description`: text
- `location_address`: text
- `latitude`, `longitude`: decimal(10,7) nullable
- `status`: varchar(30), default `submitted`
- `completed_at`: timestamp nullable
- `feedback_rating`: unsigned tinyint nullable, range 1-5
- `feedback_comment`: text nullable
- `feedback_submitted_at`: timestamp nullable
- timestamps and soft deletes

Report statuses: `submitted`, `received`, `verifying`, `in_progress`, `completed`. The earlier `handling_completed` status was removed because submitting the completion form directly completes the report.

Website reports have `guest_access_id`, `source=online`, and `source_platform=website`. Reports entered by an admin have `created_by_user_id`; online channels set their platform and offline reports leave `source_platform` null.

### 4. report_status_histories

- `id`: bigint PK
- `report_id`: FK to `reports`
- `changed_by_user_id`: nullable FK to `users`; null for system/guest-created initial state
- `status`: varchar(30)
- `note`: text nullable
- `created_at`: timestamp

This table is append-only and does not need `updated_at`.

### 5. attachments

Polymorphic storage metadata for report evidence and activity documentation.

- `id`: bigint PK
- `attachable_type`: varchar(50), use a morph map alias such as `report` or `activity`
- `attachable_id`: bigint
- `uploaded_by_user_id`: nullable FK to `users`; null for guest upload
- `file_path`: varchar(500)
- `original_name`: varchar(255)
- `file_type`: varchar(20); `image`, `video`
- `mime_type`: varchar(100)
- `file_size`: unsigned bigint, bytes
- `audience`: varchar(20); `reporter`, `internal`
- timestamps

`audience=reporter` does not mean globally public. Access must still be authorized through the guest token or authenticated staff permissions.

### 6. activities

- `id`: bigint PK
- `report_id`: nullable unique FK to `reports`
- `activity_template_id`: nullable FK to `activity_templates`
- `created_by_user_id`: FK to `users`
- `evaluated_by_user_id`: nullable FK to `users`
- `name`: varchar(255)
- `type`: varchar(30); `internal`, `external`
- `operational_category`: varchar(50) nullable
- `activity_date`: date
- `start_time`: time nullable
- `description`: text nullable
- `handling_result`: text nullable
- `location_address`: text
- `latitude`, `longitude`: decimal(10,7)
- `evaluation_notes`: text nullable
- `evaluation_obstacles`: text nullable
- `evaluated_at`: timestamp nullable
- timestamps and soft deletes

Internal activities have no report. External completion activities link to exactly one report, and each report can produce at most one external activity.

### 7. activity_templates

- `id`: bigint PK
- `name`: varchar(255)
- `operational_category`: varchar(50) nullable
- `default_description`: text nullable
- `is_active`: boolean, default true
- `sort_order`: unsigned smallint, default 0
- timestamps

The current development seed keeps the two agreed dummy template records. Selecting a template only pre-fills fields; `activities.name` stores the final editable name, and no additional template is added without a product decision.

### 8. operational_zones

- `id`: bigint PK
- `name`: varchar(150)
- `code`: varchar(30) nullable, unique
- `description`: text nullable
- `is_active`: boolean, default true
- timestamps and soft deletes

### 9. organizational_units

- `id`: bigint PK
- `parent_id`: nullable self-FK
- `operational_zone_id`: nullable FK to `operational_zones`
- `leader_officer_id`: nullable FK to `officers`, added after `officers` exists
- `name`: varchar(255)
- `code`: varchar(50) nullable, unique
- `type`: varchar(30); `division`, `team`, `guard_post`
- `description`: text nullable
- `is_active`: boolean, default true
- timestamps and soft deletes

Do not store a manually entered member count; derive it from officers assigned to the unit.

### 10. officers

- `id`: bigint PK
- `user_id`: nullable unique FK to `users`
- `organizational_unit_id`: nullable FK to `organizational_units`
- `operational_zone_id`: nullable FK to `operational_zones`
- `name`: varchar(255)
- `employee_number`: varchar(50), unique
- `rank`: varchar(100) nullable
- `position`: varchar(150) nullable
- `email`: varchar(255) nullable
- `phone`: varchar(25) nullable
- `address`: text nullable
- `joined_at`: date nullable
- `status`: varchar(30); `active`, `leave`, `inactive`
- timestamps and soft deletes

Field officers do not need accounts. An admin/super admin who is also personnel can link to one officer profile.

### 11. activity_officer

Many-to-many pivot between activities and officers.

- `id`: bigint PK
- `activity_id`: FK to `activities`
- `officer_id`: FK to `officers`
- `assignment_role`: varchar(150) nullable
- `notes`: text nullable
- timestamps
- unique pair: `activity_id`, `officer_id`

### 12. archives

- `id`: bigint PK
- `created_by_user_id`: FK to `users`
- `title`: varchar(255)
- `operational_category`: varchar(50)
- `description`: text nullable
- `confidentiality`: varchar(30); `public`, `confidential`
- `file_path`: varchar(500)
- `original_name`: varchar(255)
- `mime_type`: varchar(100)
- `file_extension`: varchar(20)
- `file_size`: unsigned bigint
- timestamps and soft deletes

Only the active file is retained in the current scope. Changes are recorded in `audit_logs`. Add an `archive_versions` table later only if recovery of old file versions becomes a requirement.

### 13. assets

- `id`: bigint PK
- `created_by_user_id`: FK to `users`
- `asset_code`: varchar(50) nullable, unique
- `name`: varchar(255)
- `category`: varchar(30); `vehicle`, `equipment`, `tools`, `infrastructure`, `other`
- `condition`: varchar(30); `good`, `needs_repair`, `heavily_damaged`
- `location`: varchar(255) nullable
- `acquisition_year`: unsigned smallint nullable
- `tax_due_date`: date nullable
- `next_oil_service_km`: unsigned integer nullable
- timestamps and soft deletes

Tax and oil-service fields must be null for non-vehicle assets.

### 14. notifications

Use Laravel's database notification structure.

- `id`: UUID PK
- `type`: varchar(255)
- `notifiable_type`: varchar(255)
- `notifiable_id`: bigint
- `data`: text/JSON
- `read_at`: timestamp nullable
- timestamps

Notification data can carry a category, title, message, related object ID, and destination URL. Each recipient gets a separate row so read state is per user.

### 15. audit_logs

- `id`: bigint PK
- `actor_user_id`: nullable FK to `users`; null for system actions
- `subject_type`: varchar(50) nullable, use stable morph-map aliases
- `subject_id`: bigint nullable
- `module`: varchar(50)
- `action`: varchar(50)
- `description`: text
- `old_values`: JSON nullable
- `new_values`: JSON nullable
- `ip_address`: varchar(45) nullable
- `user_agent`: text nullable
- `created_at`: timestamp

Audit logs are append-only. Never include passwords, guest tokens, remember tokens, full NIK values, session contents, or confidential file contents.

## Shared operational categories

Use these backed-enum values on activities, templates, and archives:

- `public_order_patrol`
- `official_security_escort`
- `event_security`
- `public_complaint_handling`
- `regional_regulation_compliance`
- `attribute_supervision`
- `judicial_enforcement`
- `non_judicial_enforcement`

## Main data flows

### Guest report submission

1. Resolve or create the browser's `guest_accesses` record.
2. Create a `reports` row.
3. Store uploaded files in Laravel Storage and metadata in `attachments`.
4. Append the `submitted` row to `report_status_histories`.
5. Create database notifications for appropriate admins.
6. Append a system entry to `audit_logs`.
7. Return the public report code and preserve the guest cookie.

### Report processing

Each status change updates `reports.status`, appends `report_status_histories`, and records `audit_logs`.

### Report completion

In one transaction:

1. Validate activity data, location, at least one eligible officer, and required completion evidence.
2. Create an external `activities` row linked through its unique `report_id`.
3. Insert assigned officers into `activity_officer`.
4. Store completion evidence in `attachments` attached to the activity.
5. Update the report to `completed` and set `completed_at`.
6. Append the completed status history and audit record.

### Guest history and feedback

Resolve the guest cookie, retrieve reports by `guest_access_id`, show status history and reporter-visible evidence, and allow one rating/comment after completion.

### Internal activity

Create an activity with `type=internal` and null `report_id`, optionally use a template, assign officers, upload documentation, and store its later evaluation on the activity.

## Important indexes to start with

- `reports.code` unique
- `reports(guest_access_id, created_at)`
- `reports(status, created_at)`
- `reports(type, created_at)`
- `report_status_histories(report_id, created_at)`
- `attachments(attachable_type, attachable_id)`
- `activities.report_id` unique
- `activities(type, activity_date)`
- `officers.employee_number` unique
- `officers(organizational_unit_id, status)`
- `activity_officer(activity_id, officer_id)` unique
- `notifications(notifiable_type, notifiable_id, read_at)`
- `audit_logs(module, created_at)`
- `audit_logs(subject_type, subject_id)`

Avoid speculative indexes beyond these until real query plans justify them.

## Performance notes

- The schema is not expected to be heavy for the intended operational scale.
- Media storage and delivery will dominate database row storage. Compress images, create thumbnails, lazy-load galleries, validate sizes, and consider object storage as volume grows.
- Paginate reports, activities, officers, archives, assets, notifications, and audit logs.
- Use eager loading and select only required columns for list pages.
- Cache dashboard aggregates briefly if they become expensive.
- Queue large Excel/PDF exports.
- Do not persist fake system metrics. Integrate real hosting metrics later if required.

## Explicitly deferred decisions

- Guest-history recovery after cookie loss or device change.
- Final guest-cookie expiration period.
- Final upload counts, MIME allow-lists, image size, and video size limits.
- Audit-log retention duration.
- Exact notification recipients by role and event.
- Whether password recovery remains in scope.
- Whether production needs archive file version recovery.
- Production hosting/storage topology and backup schedule.

## Recommended implementation order

1. Configure MySQL and verify connection.
2. Create migrations, PHP backed enums, models, relationships, factories, and seeders.
3. Implement authentication, roles, and route authorization.
4. Implement guest identity, public report submission, storage, history, and feedback.
5. Implement admin report workflow and transactional completion into an external activity.
6. Implement zones, organizational units, officers, activities, templates, assignment, and evaluation.
7. Implement archives and assets.
8. Implement notifications, audit logs, dashboard aggregates, and exports.
9. Replace remaining frontend dummy/localStorage data and verify all end-to-end flows.
10. Add deployment configuration, backups, security hardening, and production-like performance checks.

## Rough implementation estimate

- Core public-report-to-completion flow: roughly 10-18 focused developer-days.
- Full current scope with integration, validation, testing, notifications, exports, and hardening: roughly 25-45 developer-days for one developer.
- A one-week intensive milestone can produce a functional core, but should not be treated as a production-hardening guarantee.
