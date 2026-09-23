-- =====================================================================
-- Ansh AI — Database Schema (MySQL 8+)
-- Charset: utf8mb4 (full Devanagari + emoji support)
-- All timestamps are stored in UTC. Display uses Asia/Kolkata.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name               VARCHAR(120) NOT NULL,
    email                   VARCHAR(190) NOT NULL,
    password_hash           VARCHAR(255) NOT NULL,
    avatar                  VARCHAR(255) DEFAULT NULL,
    status                  ENUM('active','suspended','deleted') NOT NULL DEFAULT 'active',
    is_verified             TINYINT(1) NOT NULL DEFAULT 0,
    plan                    ENUM('free','premium') NOT NULL DEFAULT 'free',
    subscription_status     ENUM('free','premium','expired','cancelled','pending') NOT NULL DEFAULT 'free',
    subscription_expires_at DATETIME DEFAULT NULL,
    language                VARCHAR(10) NOT NULL DEFAULT 'mr',
    theme                   VARCHAR(10) NOT NULL DEFAULT 'light',
    notifications_enabled   TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at           DATETIME DEFAULT NULL,
    created_at              DATETIME NOT NULL,
    updated_at              DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_status (status),
    KEY idx_users_plan (plan),
    KEY idx_users_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- email_verifications
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_verifications (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    token_hash  CHAR(64) NOT NULL,
    expires_at  DATETIME NOT NULL,
    used_at     DATETIME DEFAULT NULL,
    created_at  DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_ev_user (user_id),
    KEY idx_ev_token (token_hash),
    CONSTRAINT fk_ev_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- password_resets
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    token_hash  CHAR(64) NOT NULL,
    expires_at  DATETIME NOT NULL,
    used_at     DATETIME DEFAULT NULL,
    created_at  DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_pr_user (user_id),
    KEY idx_pr_token (token_hash),
    CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- chats
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS chats (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    title       VARCHAR(200) NOT NULL DEFAULT 'नवीन संभाषण',
    status      ENUM('active','archived','deleted') NOT NULL DEFAULT 'active',
    created_at  DATETIME NOT NULL,
    updated_at  DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_chats_user (user_id, status, updated_at),
    CONSTRAINT fk_chats_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- chat_messages
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS chat_messages (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    chat_id         BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    role            ENUM('user','model') NOT NULL,
    message         MEDIUMTEXT NOT NULL,
    model           VARCHAR(80) DEFAULT NULL,
    total_tokens    INT UNSIGNED DEFAULT 0,
    response_time_ms INT UNSIGNED DEFAULT 0,
    status          ENUM('ok','error') NOT NULL DEFAULT 'ok',
    created_at      DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_cm_chat (chat_id, created_at),
    KEY idx_cm_user (user_id),
    CONSTRAINT fk_cm_chat FOREIGN KEY (chat_id) REFERENCES chats (id) ON DELETE CASCADE,
    CONSTRAINT fk_cm_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- categories
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(80) NOT NULL,
    description VARCHAR(200) DEFAULT NULL,
    icon        VARCHAR(40) NOT NULL DEFAULT 'sparkles',
    color       VARCHAR(20) NOT NULL DEFAULT 'emerald',
    prompt      TEXT DEFAULT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cat_name (name),
    KEY idx_cat_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- ai_tools
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ai_tools (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug          VARCHAR(60) NOT NULL,
    name          VARCHAR(100) NOT NULL,
    description   VARCHAR(255) DEFAULT NULL,
    icon          VARCHAR(40) NOT NULL DEFAULT 'wand-2',
    color         VARCHAR(20) NOT NULL DEFAULT 'emerald',
    prompt        TEXT NOT NULL,
    placeholder   VARCHAR(255) DEFAULT NULL,
    is_premium    TINYINT(1) NOT NULL DEFAULT 0,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    sort_order    INT NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tools_slug (slug),
    KEY idx_tools_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- ai_usage_logs  (privacy-conscious: NO message content)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ai_usage_logs (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id           BIGINT UNSIGNED NOT NULL,
    chat_id           BIGINT UNSIGNED DEFAULT NULL,
    model             VARCHAR(80) DEFAULT NULL,
    success           TINYINT(1) NOT NULL DEFAULT 0,
    http_status       SMALLINT UNSIGNED DEFAULT 0,
    prompt_tokens     INT UNSIGNED DEFAULT 0,
    completion_tokens INT UNSIGNED DEFAULT 0,
    total_tokens      INT UNSIGNED DEFAULT 0,
    response_time_ms  INT UNSIGNED DEFAULT 0,
    error_message     VARCHAR(255) DEFAULT NULL,
    created_at        DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_usage_user_date (user_id, created_at),
    KEY idx_usage_date (created_at),
    KEY idx_usage_success (success)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- plans
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS plans (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name            VARCHAR(80) NOT NULL,
    slug            VARCHAR(60) NOT NULL,
    price           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency        VARCHAR(3) NOT NULL DEFAULT 'INR',
    billing_period  ENUM('free','monthly','yearly') NOT NULL DEFAULT 'free',
    tier            ENUM('free','premium') NOT NULL DEFAULT 'free',
    daily_limit     INT NOT NULL DEFAULT 10,
    monthly_limit   INT NOT NULL DEFAULT 200,
    max_message_len INT NOT NULL DEFAULT 4000,
    features        TEXT DEFAULT NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    is_recommended  TINYINT(1) NOT NULL DEFAULT 0,
    sort_order      INT NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_plans_slug (slug),
    KEY idx_plans_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- subscriptions
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscriptions (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      BIGINT UNSIGNED NOT NULL,
    plan_id      BIGINT UNSIGNED DEFAULT NULL,
    plan_slug    VARCHAR(60) DEFAULT NULL,
    amount       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status       ENUM('active','expired','cancelled','pending') NOT NULL DEFAULT 'pending',
    starts_at    DATETIME DEFAULT NULL,
    expires_at   DATETIME DEFAULT NULL,
    payment_id   BIGINT UNSIGNED DEFAULT NULL,
    created_at   DATETIME NOT NULL,
    updated_at   DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_sub_user (user_id, status),
    KEY idx_sub_expires (expires_at),
    CONSTRAINT fk_sub_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- payments
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id        BIGINT UNSIGNED NOT NULL,
    plan_id        BIGINT UNSIGNED DEFAULT NULL,
    plan_slug      VARCHAR(60) DEFAULT NULL,
    transaction_id VARCHAR(100) NOT NULL,
    payu_id        VARCHAR(100) DEFAULT NULL,
    amount         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    currency       VARCHAR(3) NOT NULL DEFAULT 'INR',
    status         ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(60) DEFAULT NULL,
    raw_response   MEDIUMTEXT DEFAULT NULL,
    created_at     DATETIME NOT NULL,
    updated_at     DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pay_txn (transaction_id),
    KEY idx_pay_user (user_id),
    KEY idx_pay_status (status),
    KEY idx_pay_created (created_at),
    CONSTRAINT fk_pay_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- admin_users
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(120) NOT NULL,
    email         VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('super_admin','admin','support') NOT NULL DEFAULT 'admin',
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME DEFAULT NULL,
    created_at    DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- settings  (key/value; secrets encrypted at rest, is_secret=1)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`      VARCHAR(80) NOT NULL,
    `value`    MEDIUMTEXT DEFAULT NULL,
    is_secret  TINYINT(1) NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- email_templates
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_templates (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`      VARCHAR(60) NOT NULL,
    name       VARCHAR(120) NOT NULL,
    subject    VARCHAR(255) NOT NULL,
    body       MEDIUMTEXT NOT NULL,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tpl_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- email_logs
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_logs (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    recipient     VARCHAR(190) NOT NULL,
    subject       VARCHAR(255) DEFAULT NULL,
    success       TINYINT(1) NOT NULL DEFAULT 0,
    error_message VARCHAR(255) DEFAULT NULL,
    created_at    DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_email_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- api_logs  (external API errors etc.)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS api_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    service     VARCHAR(40) NOT NULL,
    level       VARCHAR(20) NOT NULL DEFAULT 'error',
    message     VARCHAR(500) DEFAULT NULL,
    created_at  DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_api_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- activity_logs (user actions)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED DEFAULT NULL,
    action      VARCHAR(80) NOT NULL,
    ip          VARCHAR(45) DEFAULT NULL,
    meta        TEXT DEFAULT NULL,
    created_at  DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_act_user (user_id),
    KEY idx_act_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- audit_logs (admin actions)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    admin_id     BIGINT UNSIGNED DEFAULT NULL,
    action       VARCHAR(80) NOT NULL,
    target_type  VARCHAR(40) DEFAULT NULL,
    target_id    BIGINT UNSIGNED DEFAULT NULL,
    ip           VARCHAR(45) DEFAULT NULL,
    meta         TEXT DEFAULT NULL,
    created_at   DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_audit_admin (admin_id),
    KEY idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- notifications
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    type       VARCHAR(40) NOT NULL DEFAULT 'info',
    title      VARCHAR(200) NOT NULL,
    body       TEXT DEFAULT NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_notif_user (user_id, is_read),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- rate_limits
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rate_limits (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    bucket       VARCHAR(190) NOT NULL,
    count        INT UNSIGNED NOT NULL DEFAULT 0,
    window_start INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rl_bucket (bucket)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- support_tickets
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS support_tickets (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED DEFAULT NULL,
    name        VARCHAR(120) NOT NULL,
    email       VARCHAR(190) NOT NULL,
    subject     VARCHAR(200) NOT NULL,
    message     TEXT NOT NULL,
    status      ENUM('open','pending','resolved','closed') NOT NULL DEFAULT 'open',
    admin_reply TEXT DEFAULT NULL,
    created_at  DATETIME NOT NULL,
    updated_at  DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_ticket_status (status),
    KEY idx_ticket_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- SEED DATA
-- =====================================================================

-- Plans -------------------------------------------------------------
INSERT IGNORE INTO plans (name, slug, price, billing_period, tier, daily_limit, monthly_limit, max_message_len, features, is_active, is_recommended, sort_order, created_at) VALUES
('Free',            'free',    0.00,   'free',    'free',    10,   200,   4000, 'दररोज १० प्रश्न\nमूलभूत AI\nचॅट इतिहास', 1, 0, 0, UTC_TIMESTAMP()),
('Ansh AI Pro मासिक', 'premium-monthly', 49.00,  'monthly', 'premium', 1000, 30000, 12000, 'जास्त प्रश्न\nशक्तिशाली AI\nसर्व साधने\nजलद प्रतिसाद\nजाहिरातींशिवाय', 1, 0, 1, UTC_TIMESTAMP()),
('Ansh AI Pro वार्षिक','premium-yearly',  399.00, 'yearly',  'premium', 1000, 30000, 12000, 'जास्त प्रश्न\nशक्तिशाली AI\nसर्व साधने\nजलद प्रतिसाद\nजाहिरातींशिवाय\n३०% बचत', 1, 1, 2, UTC_TIMESTAMP());

-- Categories --------------------------------------------------------
INSERT IGNORE INTO categories (name, description, icon, color, prompt, sort_order, is_active, created_at) VALUES
('शिक्षण',        'अभ्यासात मदत',       'graduation-cap', 'blue',    'शिक्षणाशी संबंधित प्रश्नाचे सोप्या मराठीत उत्तर दे.', 1, 1, UTC_TIMESTAMP()),
('शेती',          'शेतीविषयी माहिती',    'sprout',         'green',   'शेतीविषयक व्यावहारिक मराठी सल्ला दे.', 2, 1, UTC_TIMESTAMP()),
('आरोग्य',        'आरोग्य सल्ला',        'heart-pulse',    'red',     'आरोग्याविषयी सामान्य माहिती मराठीत दे. वैद्यकीय सल्ल्यासाठी डॉक्टरांचा सल्ला घ्यायला सांग.', 3, 1, UTC_TIMESTAMP()),
('तंत्रज्ञान',     'नवीन तंत्रज्ञान',     'cpu',            'cyan',    'तंत्रज्ञानाविषयी सोप्या मराठीत समजावून सांग.', 4, 1, UTC_TIMESTAMP()),
('करिअर',         'नोकरी मार्गदर्शन',    'briefcase',      'amber',   'करिअर व नोकरीविषयी मराठीत मार्गदर्शन कर.', 5, 1, UTC_TIMESTAMP()),
('व्यवसाय',        'व्यवसाय कल्पना',      'store',          'orange',  'व्यवसायाच्या कल्पना व सल्ला मराठीत दे.', 6, 1, UTC_TIMESTAMP()),
('दैनंदिन जीवन',   'उपयुक्त माहिती',     'sun',            'yellow',  'दैनंदिन जीवनातील प्रश्नांची मराठीत उत्तरे दे.', 7, 1, UTC_TIMESTAMP()),
('मनोरंजन',        'प्रेरणादायी गोष्टी',  'sparkles',       'purple',  'मनोरंजन व प्रेरणादायी मजकूर मराठीत तयार कर.', 8, 1, UTC_TIMESTAMP()),
('लेखन',          'लेखन सहाय्य',         'pen-line',       'indigo',  'निबंध, पत्र, अर्ज असे लेखन मराठीत तयार कर.', 9, 1, UTC_TIMESTAMP()),
('भाषांतर',        'भाषा रूपांतर',        'languages',      'teal',    'मजकुराचे अचूक भाषांतर कर.', 10, 1, UTC_TIMESTAMP()),
('प्रश्नोत्तरे',    'कोणतेही प्रश्न',      'help-circle',    'emerald', 'कोणत्याही प्रश्नाचे सोप्या मराठीत उत्तर दे.', 11, 1, UTC_TIMESTAMP());

-- AI Tools ----------------------------------------------------------
INSERT IGNORE INTO ai_tools (slug, name, description, icon, color, prompt, placeholder, is_premium, is_active, sort_order, created_at) VALUES
('translator',  'मराठी अनुवाद', 'English ↔ मराठी', 'languages', 'blue',
 'तू एक तज्ज्ञ भाषांतरकार आहेस. वापरकर्त्याने दिलेला मजकूर इंग्रजी असल्यास मराठीत आणि मराठी असल्यास इंग्रजीत अचूक भाषांतर कर. फक्त भाषांतर दे.',
 'भाषांतरासाठी मजकूर लिहा...', 0, 1, 1, UTC_TIMESTAMP()),
('essay',       'निबंध जनरेटर', 'कोणत्याही विषयावर', 'pen-line', 'purple',
 'तू निबंध लेखक आहेस. दिलेल्या विषयावर सुसंगत, मुद्देसूद मराठी निबंध लिही (प्रस्तावना, मुख्य भाग, निष्कर्ष).',
 'निबंधाचा विषय लिहा...', 0, 1, 2, UTC_TIMESTAMP()),
('resume',      'Resume Builder', 'मराठी / इंग्रजी', 'file-text', 'emerald',
 'तू रेझ्युमे तज्ज्ञ आहेस. वापरकर्त्याच्या माहितीवरून व्यावसायिक रेझ्युमे मजकूर तयार कर.',
 'तुमची माहिती लिहा (नाव, शिक्षण, अनुभव, कौशल्ये)...', 0, 1, 3, UTC_TIMESTAMP()),
('qa',          'प्रश्नोत्तरी', 'स्पर्धा परीक्षांसाठी', 'help-circle', 'amber',
 'तू स्पर्धा परीक्षा मार्गदर्शक आहेस. दिलेल्या विषयावर महत्त्वाची प्रश्नोत्तरे मराठीत तयार कर.',
 'विषय किंवा प्रश्न लिहा...', 0, 1, 4, UTC_TIMESTAMP()),
('ideas',       'Ideas Generator', 'नवीन कल्पना', 'lightbulb', 'orange',
 'तू सर्जनशील सल्लागार आहेस. दिलेल्या क्षेत्रात नवीन कल्पना मराठीत सुचव.',
 'कोणत्या विषयावर कल्पना हव्यात?', 0, 1, 5, UTC_TIMESTAMP()),
('summary',     'सारांश बनवा', 'लांब मजकुराचा', 'align-left', 'teal',
 'तू सारांश तज्ज्ञ आहेस. दिलेल्या मजकुराचा थोडक्यात, मुद्देसूद मराठी सारांश तयार कर.',
 'सारांशासाठी मजकूर पेस्ट करा...', 0, 1, 6, UTC_TIMESTAMP()),
('writing',     'लेखन सहाय्यक', 'पत्र, अर्ज, ईमेल, पोस्ट', 'edit-3', 'indigo',
 'तू लेखन सहाय्यक आहेस. वापरकर्त्याच्या गरजेनुसार पत्र, अर्ज, ईमेल, कॅप्शन किंवा पोस्ट मराठीत लिही.',
 'काय लिहायचे आहे ते सांगा...', 0, 1, 7, UTC_TIMESTAMP()),
('image',       'प्रतिमा समजवा', 'Upload करून विचारा', 'image', 'green',
 'तू प्रतिमा विश्लेषक आहेस. दिलेल्या प्रतिमेचे वर्णन कर आणि प्रश्नाचे मराठीत उत्तर दे.',
 'प्रतिमेबद्दल प्रश्न विचारा...', 1, 1, 8, UTC_TIMESTAMP());

-- Email templates ---------------------------------------------------
INSERT IGNORE INTO email_templates (`key`, name, subject, body, is_active, updated_at) VALUES
('verification', 'ईमेल सत्यापन', 'तुमचा Ansh AI ईमेल सत्यापित करा',
 '<p>नमस्कार {{name}},</p><p>Ansh AI मध्ये स्वागत आहे! खाली दिलेल्या बटणावर क्लिक करून तुमचा ईमेल सत्यापित करा.</p><p style="text-align:center;margin:28px 0"><a href="{{verification_link}}" style="background:#059669;color:#fff;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700">ईमेल सत्यापित करा</a></p><p style="color:#6b7280;font-size:13px">ही लिंक {{expiry_date}} पर्यंत वैध आहे. जर तुम्ही खाते तयार केले नसेल, तर हा ईमेल दुर्लक्षित करा.</p>', 1, UTC_TIMESTAMP()),
('welcome', 'स्वागत ईमेल', 'Ansh AI मध्ये स्वागत आहे! 🌿',
 '<p>नमस्कार {{name}},</p><p>तुमचे खाते यशस्वीरित्या सत्यापित झाले आहे. आता तुम्ही मराठीत प्रश्न विचारू शकता आणि Ansh AI कडून उत्तरे मिळवू शकता.</p><p>चला सुरुवात करूया!</p>', 1, UTC_TIMESTAMP()),
('password_reset', 'पासवर्ड रीसेट', 'तुमचा पासवर्ड रीसेट करा',
 '<p>नमस्कार {{name}},</p><p>तुमचा पासवर्ड रीसेट करण्यासाठी खालील बटणावर क्लिक करा.</p><p style="text-align:center;margin:28px 0"><a href="{{reset_link}}" style="background:#059669;color:#fff;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700">पासवर्ड रीसेट करा</a></p><p style="color:#6b7280;font-size:13px">ही लिंक {{expiry_date}} पर्यंत वैध आहे. जर तुम्ही ही विनंती केली नसेल, तर हा ईमेल दुर्लक्षित करा.</p>', 1, UTC_TIMESTAMP()),
('password_changed', 'पासवर्ड बदलला', 'तुमचा पासवर्ड बदलण्यात आला',
 '<p>नमस्कार {{name}},</p><p>तुमचा Ansh AI पासवर्ड नुकताच बदलण्यात आला आहे. जर हे तुम्ही केले नसेल, तर कृपया तात्काळ आमच्याशी संपर्क साधा.</p>', 1, UTC_TIMESTAMP()),
('subscription_success', 'सदस्यत्व यशस्वी', 'Ansh AI Pro सक्रिय झाले! 🎉',
 '<p>नमस्कार {{name}},</p><p>तुमचे <strong>{{plan}}</strong> सदस्यत्व सक्रिय झाले आहे.</p><p>रक्कम: {{amount}}<br>व्यवहार क्रमांक: {{transaction_id}}<br>वैधता: {{expiry_date}} पर्यंत</p><p>Ansh AI Pro चा आनंद घ्या!</p>', 1, UTC_TIMESTAMP()),
('subscription_expiring', 'सदस्यत्व संपत आहे', 'तुमचे Ansh AI Pro लवकरच संपेल',
 '<p>नमस्कार {{name}},</p><p>तुमचे <strong>{{plan}}</strong> सदस्यत्व {{expiry_date}} रोजी संपणार आहे. सेवा सुरू ठेवण्यासाठी नूतनीकरण करा.</p>', 1, UTC_TIMESTAMP()),
('subscription_expired', 'सदस्यत्व संपले', 'तुमचे Ansh AI Pro संपले आहे',
 '<p>नमस्कार {{name}},</p><p>तुमचे Pro सदस्यत्व संपले आहे. पुन्हा अपग्रेड करून सर्व वैशिष्ट्ये परत मिळवा.</p>', 1, UTC_TIMESTAMP()),
('payment_failed', 'पेमेंट अयशस्वी', 'तुमचे पेमेंट अयशस्वी झाले',
 '<p>नमस्कार {{name}},</p><p>तुमचे {{amount}} चे पेमेंट (व्यवहार {{transaction_id}}) पूर्ण होऊ शकले नाही. कृपया पुन्हा प्रयत्न करा.</p>', 1, UTC_TIMESTAMP()),
('account_suspended', 'खाते निलंबित', 'तुमचे Ansh AI खाते निलंबित करण्यात आले',
 '<p>नमस्कार {{name}},</p><p>तुमचे खाते निलंबित करण्यात आले आहे. अधिक माहितीसाठी आमच्याशी संपर्क साधा.</p>', 1, UTC_TIMESTAMP());

-- Settings (non-secret defaults) ------------------------------------
INSERT IGNORE INTO settings (`key`, `value`, is_secret, updated_at) VALUES
('app_name', 'Ansh AI', 0, UTC_TIMESTAMP()),
('app_tagline', 'तुमचा मराठी AI साथी', 0, UTC_TIMESTAMP()),
('primary_color', '#059669', 0, UTC_TIMESTAMP()),
('secondary_color', '#065f46', 0, UTC_TIMESTAMP()),
('support_email', 'support@ansh.ai', 0, UTC_TIMESTAMP()),
('contact_email', 'hello@ansh.ai', 0, UTC_TIMESTAMP()),
('maintenance_mode', '0', 0, UTC_TIMESTAMP()),
('maintenance_message', 'Ansh AI सध्या देखभालीमध्ये आहे. कृपया थोड्या वेळाने पुन्हा भेट द्या.', 0, UTC_TIMESTAMP()),
('registration_enabled', '1', 0, UTC_TIMESTAMP()),
('email_verification_enabled', '1', 0, UTC_TIMESTAMP()),
('verification_ttl_hours', '24', 0, UTC_TIMESTAMP()),
('reset_ttl_hours', '2', 0, UTC_TIMESTAMP()),
-- AI
('ai_enabled', '1', 0, UTC_TIMESTAMP()),
('gemini_model', 'gemini-1.5-flash', 0, UTC_TIMESTAMP()),
('ai_temperature', '0.7', 0, UTC_TIMESTAMP()),
('ai_max_tokens', '1024', 0, UTC_TIMESTAMP()),
('ai_request_timeout', '30', 0, UTC_TIMESTAMP()),
('ai_system_prompt', 'तुम्ही Ansh AI आहात — भारतातील खास मराठी AI साथी. वापरकर्त्याशी शक्यतो नैसर्गिक, सोप्या आणि स्पष्ट मराठी भाषेत संवाद साधा. उत्तरे मुद्देसूद, उपयुक्त आणि सभ्य ठेवा. वापरकर्त्याने दुसरी भाषा वापरल्यास त्यानुसार उत्तर द्या.', 0, UTC_TIMESTAMP()),
('ai_fallback_message', 'माफ करा, सध्या मी उत्तर देऊ शकत नाही. कृपया थोड्या वेळाने पुन्हा प्रयत्न करा.', 0, UTC_TIMESTAMP()),
('image_input_enabled', '1', 0, UTC_TIMESTAMP()),
-- Limits
('free_daily_limit', '10', 0, UTC_TIMESTAMP()),
('free_monthly_limit', '200', 0, UTC_TIMESTAMP()),
('premium_daily_limit', '1000', 0, UTC_TIMESTAMP()),
('premium_monthly_limit', '30000', 0, UTC_TIMESTAMP()),
('max_message_length', '4000', 0, UTC_TIMESTAMP()),
('limit_reached_message', 'आजचा मोफत AI वापर पूर्ण झाला आहे. Ansh AI Pro वर अपग्रेड करा आणि अधिक वापरा.', 0, UTC_TIMESTAMP()),
-- Pricing (editable)
('price_monthly', '49', 0, UTC_TIMESTAMP()),
('price_yearly', '399', 0, UTC_TIMESTAMP()),
-- Payment
('payu_env', 'test', 0, UTC_TIMESTAMP()),
-- SMTP
('smtp_port', '587', 0, UTC_TIMESTAMP()),
('smtp_encryption', 'tls', 0, UTC_TIMESTAMP()),
('smtp_from_name', 'Ansh AI', 0, UTC_TIMESTAMP()),
-- Retention (days)
('retention_chat_days', '0', 0, UTC_TIMESTAMP()),
('retention_analytics_days', '365', 0, UTC_TIMESTAMP()),
('retention_apilog_days', '30', 0, UTC_TIMESTAMP()),
('retention_activity_days', '90', 0, UTC_TIMESTAMP()),
('retention_email_days', '90', 0, UTC_TIMESTAMP());
