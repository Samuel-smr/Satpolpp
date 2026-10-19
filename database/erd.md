# ERD — Sistem SIPRAJA (Satuan Polisi Pamong Praja Kulon Progo)

Berdasarkan seluruh file migration pada `database/migrations` (kondisi skema final).

```mermaid
erDiagram
    USERS {
        bigint id PK
        varchar name
        varchar username UK
        varchar email UK "nullable"
        timestamp email_verified_at
        varchar password
        varchar role "admin | super_admin"
        varchar status "active | blocked"
        varchar remember_token
        timestamp created_at
        timestamp updated_at
    }

    GUEST_ACCESSES {
        bigint id PK
        char token_hash UK
        timestamp last_used_at
        timestamp expires_at
        timestamp created_at
        timestamp updated_at
    }

    REPORTS {
        bigint id PK
        varchar code UK
        bigint guest_access_id FK
        bigint created_by_user_id FK "nullable"
        varchar type "emergency | info_complaint"
        varchar category
        varchar source "online | offline"
        varchar source_platform
        varchar reporter_name
        varchar reporter_phone
        text description
        text location_address
        decimal latitude "10,7"
        decimal longitude "10,7"
        varchar status "submitted|received|verifying|in_progress|completed|rejected"
        timestamp completed_at
        unsignedtinyint feedback_rating
        text feedback_comment
        timestamp feedback_submitted_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    REPORT_STATUS_HISTORIES {
        bigint id PK
        bigint report_id FK
        bigint changed_by_user_id FK "nullable"
        varchar status
        text note
        timestamp created_at
    }

    ATTACHMENTS {
        bigint id PK
        varchar attachable_type "polimorfik"
        bigint attachable_id "polimorfik"
        bigint uploaded_by_user_id FK
        varchar file_path
        varchar original_name
        varchar file_type "image | document | video"
        varchar mime_type
        bigint file_size
        varchar audience "internal | public"
        timestamp created_at
        timestamp updated_at
    }

    ACTIVITY_TEMPLATES {
        bigint id PK
        varchar name
        varchar operational_category
        text default_description
        boolean is_active
        smallint sort_order
        timestamp created_at
        timestamp updated_at
    }

    ACTIVITIES {
        bigint id PK
        bigint report_id FK UK "nullable"
        bigint activity_template_id FK "nullable"
        bigint created_by_user_id FK
        bigint evaluated_by_user_id FK "nullable"
        varchar name
        varchar type "internal | external"
        varchar category
        date activity_date
        time start_time
        text description
        text handling_result
        text location_address
        decimal latitude "10,7"
        decimal longitude "10,7"
        text evaluation_notes
        text evaluation_obstacles
        timestamp evaluated_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    ACTIVITY_FINDINGS {
        bigint id PK
        bigint activity_id FK
        varchar type
        bigint quantity
        varchar unit
        timestamp created_at
        timestamp updated_at
    }

    OFFICERS {
        bigint id PK
        bigint user_id FK UK "nullable"
        bigint organizational_unit_id FK "nullable"
        bigint operational_zone_id FK "nullable"
        varchar name
        varchar employee_number UK
        varchar position
        varchar work_unit_name
        varchar unit_name
        varchar status "active | inactive"
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    ACTIVITY_OFFICER {
        bigint id PK
        bigint activity_id FK
        bigint officer_id FK
        varchar assignment_role
        text notes
        timestamp created_at
        timestamp updated_at
    }

    ORGANIZATIONAL_UNITS {
        bigint id PK
        bigint parent_id FK "nullable"
        bigint operational_zone_id FK "nullable"
        bigint leader_officer_id FK "nullable"
        varchar name
        varchar code UK
        varchar type
        text description
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    OPERATIONAL_ZONES {
        bigint id PK
        varchar name
        varchar code UK
        text description
        boolean is_active
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    ARCHIVES {
        bigint id PK
        bigint created_by_user_id FK
        varchar title
        varchar operational_category
        text description
        varchar confidentiality
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    ARCHIVE_FILES {
        bigint id PK
        bigint archive_id FK
        varchar file_path
        varchar original_name
        varchar mime_type
        varchar file_extension
        bigint file_size
        timestamp created_at
        timestamp updated_at
    }

    ASSETS {
        bigint id PK
        bigint created_by_user_id FK
        varchar asset_code UK
        varchar name
        varchar category
        varchar condition
        varchar location
        smallint acquisition_year
        date tax_due_date
        bigint next_oil_service_km
        date last_oil_service_date
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    AUDIT_LOGS {
        bigint id PK
        bigint actor_user_id FK "nullable"
        varchar subject_type "polimorfik"
        bigint subject_id "polimorfik"
        varchar module
        varchar action
        text description
        json old_values
        json new_values
        varchar ip_address
        text user_agent
        timestamp created_at
    }

    NOTIFICATIONS {
        uuid id PK
        varchar type
        varchar notifiable_type "polimorfik"
        bigint notifiable_id "polimorfik"
        text data
        timestamp read_at
        timestamp created_at
        timestamp updated_at
    }

    USERS ||--o{ REPORTS : "created_by"
    GUEST_ACCESSES ||--o{ REPORTS : "dilaporkan oleh"
    USERS ||--o{ OFFICERS : "akun"
    USERS ||--o{ ACTIVITIES : "created_by"
    USERS ||--o{ ARCHIVES : "created_by"
    USERS ||--o{ ASSETS : "created_by"
    USERS ||--o{ ATTACHMENTS : "uploaded_by"
    USERS ||--o{ REPORT_STATUS_HISTORIES : "changed_by"
    USERS ||--o{ AUDIT_LOGS : "actor"
    REPORTS ||--o{ REPORT_STATUS_HISTORIES : "riwayat status"
    REPORTS ||--o| ACTIVITIES : "tindak lanjut"
    REPORTS ||--o{ ATTACHMENTS : "bukti (polimorfik)"
    ACTIVITY_TEMPLATES ||--o{ ACTIVITIES : "template"
    ACTIVITIES ||--o{ ACTIVITY_FINDINGS : "temuan"
    ACTIVITIES ||--o{ ACTIVITY_OFFICER : "penugasan"
    ACTIVITIES ||--o{ ATTACHMENTS : "dokumentasi (polimorfik)"
    OFFICERS ||--o{ ACTIVITY_OFFICER : "dilibatkan"
    OFFICERS ||--o| ORGANIZATIONAL_UNITS : "leader"
    ORGANIZATIONAL_UNITS ||--o{ OFFICERS : "anggota"
    ORGANIZATIONAL_UNITS ||--o{ ORGANIZATIONAL_UNITS : "parent"
    OPERATIONAL_ZONES ||--o{ ORGANIZATIONAL_UNITS : "zona"
    OPERATIONAL_ZONES ||--o{ OFFICERS : "zona tugas"
    ARCHIVES ||--o{ ARCHIVE_FILES : "file arsip"
    USERS ||--o{ NOTIFICATIONS : "penerima (polimorfik)"
```

## Catatan relasi

| Relasi | Jenis | Keterangan |
|---|---|---|
| `users 1 ── N reports` | FK `created_by_user_id` | Laporan dibuat offline oleh admin (nodata dilaporkan via `guest_accesses`). |
| `guest_accesses 1 ── N reports` | FK `guest_access_id` | Laporan daring dari pelapor anonim (token hash). |
| `reports 1 ── N report_status_histories` | FK `report_id` | Riwayat status laporan (submitted → received → verifying → in_progress → completed, atau `rejected`). |
| `reports 1 ── 0..1 activities` | FK `report_id` (unique) | Laporan yang ditindaklanjuti menjadi kegiatan eksternal (1-ke-1 basis data). |
| `activity_templates 1 ── N activities` | FK `activity_template_id` | Template kegiatan internal (opsional). |
| `activities N ── N officers` | pivot `activity_officer` | Personel yang terlibat dalam kegiatan; kolom `assignment_role` & `notes`. |
| `activities 1 ── N activity_findings` | FK `activity_id` | Temuan/hasil lapangan per kegiatan (jenis, jumlah, satuan). |
| `attachments` | Polimorfik | Menempel ke `reports` (bukti) maupun `activities` (dokumentasi) lewat `attachable_type` + `attachable_id`. |
| `organizational_units N ── 0..1 organizational_units` | FK `parent_id` | Struktur unit bersarang (hierarchy). |
| `organizational_units 1 ── N officers` | FK `organizational_unit_id` | Keanggotaan unit. |
| `organizational_units 0..1 ── 1 officers` | FK `leader_officer_id` | Pimpinan unit (nullable, dibatasi `restrictOnDelete`). |
| `operational_zones 1 ── N organizational_units / officers` | FK | Pembagian zona operasional. |
| `archives 1 ── N archive_files` | FK `archive_id` | Arsip dapat memiliki beberapa file. |
| `audit_logs` | Polimorfik + FK `actor_user_id` | Audit aktivitas di seluruh modul. |
| `notifications` | Polimorfik | Notifikasi Laravel (target dapat user/berbagai model). |

## Relasi database (constraint nyata)

- `reports.guest_access_id` → `guest_accesses(id)` — `nullOnDelete`
- `reports.created_by_user_id` → `users(id)` — `nullOnDelete`
- `report_status_histories.report_id` → `reports(id)` — `restrictOnDelete`
- `report_status_histories.changed_by_user_id` → `users(id)` — `nullOnDelete`
- `activities.report_id` → `reports(id)` — `restrictOnDelete`, `unique`
- `activities.activity_template_id` → `activity_templates(id)` — `nullOnDelete`
- `activities.created_by_user_id` → `users(id)` — `restrictOnDelete`
- `activities.evaluated_by_user_id` → `users(id)` — `nullOnDelete`
- `activity_findings.activity_id` → `activities(id)` — `cascadeOnDelete`
- `activity_officer.activity_id` → `activities(id)` — `restrictOnDelete` (komposit unique dgn `officer_id`)
- `activity_officer.officer_id` → `officers(id)` — `restrictOnDelete`
- `officers.user_id` → `users(id)` — `nullOnDelete`, `unique`
- `officers.organizational_unit_id` → `organizational_units(id)` — `restrictOnDelete`
- `officers.operational_zone_id` → `operational_zones(id)` — `restrictOnDelete`
- `organizational_units.parent_id` → `organizational_units(id)` — `restrictOnDelete`
- `organizational_units.operational_zone_id` → `operational_zones(id)` — `restrictOnDelete`
- `organizational_units.leader_officer_id` → `officers(id)` — `restrictOnDelete`
- `attachments.uploaded_by_user_id` → `users(id)` — `nullOnDelete`
- `archives.created_by_user_id` → `users(id)` — `restrictOnDelete`
- `archive_files.archive_id` → `archives(id)` — `cascadeOnDelete`
- `assets.created_by_user_id` → `users(id)` — `restrictOnDelete`
- `audit_logs.actor_user_id` → `users(id)` — `nullOnDelete`