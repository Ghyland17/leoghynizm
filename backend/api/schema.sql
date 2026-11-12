-- FES Emergency Report — skema database (MySQL 5.7+/MariaDB 10.3+)
-- Waktu disimpan sebagai milidetik sejak 1970 (BIGINT), sama dengan JavaScript Date.now().
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id            VARCHAR(7)   NOT NULL PRIMARY KEY,          -- BN, contoh C043225
  name          VARCHAR(80)  NOT NULL,
  rk            VARCHAR(8)   NOT NULL,                      -- crew | tl | koord | admin | super
  squad         VARCHAR(10)  NULL,                          -- Alpha | Bravo | Charlie | Delta
  station       VARCHAR(10)  NOT NULL DEFAULT 'FOX-5',
  email         VARCHAR(120) NOT NULL,
  phone         VARCHAR(20)  NULL,
  blood         VARCHAR(4)   NULL,
  birth         VARCHAR(10)  NULL,
  address       VARCHAR(200) NULL,
  ec_name       VARCHAR(80)  NULL,
  ec_phone      VARCHAR(20)  NULL,
  photo         MEDIUMTEXT   NULL,                          -- data:image/... (sudah diperkecil)
  hours         TEXT         NULL,                          -- JSON jam kerja individu
  password_hash VARCHAR(255) NOT NULL,
  must_change   TINYINT(1)   NOT NULL DEFAULT 1,
  status        VARCHAR(10)  NOT NULL DEFAULT 'active',     -- active | disabled
  failed_count  INT          NOT NULL DEFAULT 0,
  locked_until  BIGINT       NOT NULL DEFAULT 0,
  last_login_at BIGINT       NULL,
  last_device   VARCHAR(120) NULL,
  created_at    BIGINT       NOT NULL,
  updated_at    BIGINT       NOT NULL,
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessions (
  token_hash CHAR(64)    NOT NULL PRIMARY KEY,               -- SHA-256 dari token; token asli tidak disimpan
  user_id    VARCHAR(7)  NOT NULL,
  client     VARCHAR(4)  NOT NULL,                           -- web | app
  created_at BIGINT      NOT NULL,
  last_seen  BIGINT      NOT NULL,
  expires_at BIGINT      NOT NULL,
  ip         VARCHAR(45) NULL,
  ua         VARCHAR(200) NULL,
  KEY ix_sessions_user (user_id),
  CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS otp_flows (
  id         CHAR(32)    NOT NULL PRIMARY KEY,
  user_id    VARCHAR(7)  NOT NULL,
  kind       VARCHAR(6)  NOT NULL,                           -- 2fa | reset
  code_hash  VARCHAR(255) NULL,
  expires_at BIGINT      NOT NULL,
  resend_at  BIGINT      NOT NULL,
  tries      INT         NOT NULL DEFAULT 0,
  verified   TINYINT(1)  NOT NULL DEFAULT 0,
  client     VARCHAR(4)  NOT NULL DEFAULT 'web',
  created_at BIGINT      NOT NULL,
  KEY ix_otp_user (user_id),
  CONSTRAINT fk_otp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS trusted_devices (
  token_hash CHAR(64)   NOT NULL PRIMARY KEY,
  user_id    VARCHAR(7) NOT NULL,
  expires_at BIGINT     NOT NULL,
  KEY ix_trust_user (user_id),
  CONSTRAINT fk_trust_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_hits (
  k  VARCHAR(80) NOT NULL,
  at BIGINT      NOT NULL,
  KEY ix_rate (k, at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  k VARCHAR(40) NOT NULL PRIMARY KEY,
  v MEDIUMTEXT  NOT NULL                                     -- JSON
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS swaps (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  from_id    VARCHAR(7)  NOT NULL,
  to_id      VARCHAR(7)  NULL,
  kind       VARCHAR(10) NOT NULL DEFAULT 'tukar',           -- tukar | izin
  day        VARCHAR(20) NOT NULL,
  shift      VARCHAR(20) NOT NULL,
  reason     VARCHAR(300) NOT NULL,
  status     VARCHAR(10) NOT NULL DEFAULT 'pending',         -- pending | approved | rejected
  decided_by VARCHAR(7)  NULL,
  decided_at BIGINT      NULL,
  created_at BIGINT      NOT NULL,
  KEY ix_swaps_status (status),
  CONSTRAINT fk_swaps_from FOREIGN KEY (from_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS incidents (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  no            VARCHAR(40)  NOT NULL,
  rtype         VARCHAR(20)  NULL,
  itype         VARCHAR(20)  NULL,
  sub           VARCHAR(60)  NULL,
  location      VARCHAR(160) NULL,
  station       VARCHAR(60)  NULL,
  phase         VARCHAR(12)  NOT NULL DEFAULT 'enroute',
  status        VARCHAR(10)  NOT NULL DEFAULT 'active',      -- active | returned | submitted | approved | revise
  dispatch_at   BIGINT NULL, start_at BIGINT NULL, arrival_at BIGINT NULL, leave_at BIGINT NULL, return_at BIGINT NULL,
  target_min    INT NULL,
  data          LONGTEXT     NOT NULL,                       -- isi laporan lengkap (JSON)
  version       INT          NOT NULL DEFAULT 1,
  created_by    VARCHAR(7)   NOT NULL,
  updated_by    VARCHAR(7)   NOT NULL,
  created_at    BIGINT       NOT NULL,
  updated_at    BIGINT       NOT NULL,
  submitted_at  BIGINT NULL,
  approved_by   VARCHAR(7) NULL,
  approved_at   BIGINT NULL,
  approval_note VARCHAR(500) NULL,
  UNIQUE KEY uq_incident_no (no),
  KEY ix_incident_status (status),
  KEY ix_incident_dispatch (dispatch_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS photos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  incident_id INT          NOT NULL,
  data        MEDIUMTEXT   NOT NULL,
  caption     VARCHAR(200) NULL,
  taken_at    BIGINT       NOT NULL,
  created_by  VARCHAR(7)   NOT NULL,
  KEY ix_photos_incident (incident_id),
  CONSTRAINT fk_photos_incident FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  at         BIGINT       NOT NULL,
  actor_id   VARCHAR(7)   NULL,
  actor_name VARCHAR(80)  NOT NULL,
  action     VARCHAR(300) NOT NULL,
  ip         VARCHAR(45)  NULL,
  KEY ix_audit_at (at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roster_overrides (
  k          VARCHAR(40)  NOT NULL PRIMARY KEY,               -- "2026-11-7|P|FOX-5" (tanggal|shift|station)
  absent     TEXT         NOT NULL,                           -- JSON {"C043177":"Sakit"}
  extra      TEXT         NOT NULL,                           -- JSON ["C055193"] pengganti
  updated_by VARCHAR(7)   NOT NULL,
  updated_at BIGINT       NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
