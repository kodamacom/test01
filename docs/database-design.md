# データベース設計書

## 文書情報
- **プロジェクト名**: DocuMind - AI-Powered Document Management System
- **文書ID**: DB-DOCUMIND-001
- **バージョン**: 1.0
- **最終更新日**: 2025-11-04
- **作成者**: 開発チーム
- **ステータス**: Draft

---

## 1. データベース概要

### 1.1 データベース構成

| データベース | 種類 | 用途 | 技術 |
|------------|------|------|------|
| Main DB | Relational | トランザクションデータ、ユーザー、組織、文書メタデータ | PostgreSQL 15+ |
| Document DB | NoSQL | 文書メタデータ、ログ、イベント | MongoDB 6+ |
| Cache DB | In-Memory | セッション、キャッシュ、レート制限 | Redis 7+ |
| Vector DB | Vector | Embedding、セマンティック検索 | Pinecone / Qdrant |
| Search Engine | Full-Text | 全文検索、ログ分析 | Elasticsearch 8+ |

### 1.2 データベース選定理由

#### PostgreSQL
- ACID準拠による高いデータ整合性
- 豊富な機能（JSONB、全文検索、パーティション）
- 成熟したエコシステム
- 優れたパフォーマンス

#### MongoDB
- 柔軟なスキーマ（文書の多様なメタデータ）
- 水平スケーリング対応
- 集約パイプライン

#### Redis
- 超高速（メモリベース）
- 多様なデータ構造
- Pub/Sub対応

#### Pinecone / Qdrant
- ベクトル検索に特化
- 高速なANN（近似最近傍）検索
- スケーラブル

---

## 2. PostgreSQL スキーマ設計

### 2.1 ER図（概要）

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│Organizations │────┬────│    Users     │────┬────│    Roles     │
└──────────────┘    │    └──────────────┘    │    └──────────────┘
                    │                        │
                    │    ┌──────────────┐    │
                    └────│ Departments  │    │
                         └──────────────┘    │
                                             │
┌──────────────┐                             │
│   Documents  │─────────────────────────────┘
└───────┬──────┘
        │
        ├────┌──────────────────┐
        │    │Document Versions │
        │    └──────────────────┘
        │
        ├────┌──────────────┐
        │    │    Tags      │
        │    └──────────────┘
        │
        ├────┌──────────────┐
        │    │  Comments    │
        │    └──────────────┘
        │
        └────┌──────────────┐
             │ Permissions  │
             └──────────────┘

┌──────────────┐         ┌──────────────┐
│  Workflows   │────────│Workflow Steps│
└──────────────┘         └──────────────┘

┌──────────────┐         ┌──────────────┐
│Subscriptions │────────│   Invoices   │
└──────────────┘         └──────────────┘
```

### 2.2 テーブル定義

#### 2.2.1 organizations（組織）

```sql
CREATE TABLE organizations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    domain VARCHAR(255), -- Email domain for auto-join
    plan_type VARCHAR(50) NOT NULL DEFAULT 'free', -- free, standard, premium, enterprise
    storage_limit_gb INTEGER NOT NULL DEFAULT 2,
    storage_used_bytes BIGINT NOT NULL DEFAULT 0,
    settings JSONB DEFAULT '{}',
    branding JSONB DEFAULT '{}', -- logo_url, primary_color, etc.
    is_active BOOLEAN NOT NULL DEFAULT true,
    trial_ends_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_organizations_slug ON organizations(slug);
CREATE INDEX idx_organizations_domain ON organizations(domain);
CREATE INDEX idx_organizations_plan_type ON organizations(plan_type);
```

#### 2.2.2 users（ユーザー）

```sql
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP WITH TIME ZONE,
    password_hash VARCHAR(255), -- NULL for SSO-only users
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    avatar_url TEXT,
    locale VARCHAR(10) DEFAULT 'ja',
    timezone VARCHAR(50) DEFAULT 'Asia/Tokyo',
    last_login_at TIMESTAMP WITH TIME ZONE,
    last_login_ip INET,
    is_active BOOLEAN NOT NULL DEFAULT true,
    is_sso_user BOOLEAN NOT NULL DEFAULT false,
    mfa_enabled BOOLEAN NOT NULL DEFAULT false,
    mfa_secret VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_users_organization_id ON users(organization_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_is_active ON users(is_active);
```

#### 2.2.3 roles（ロール）

```sql
CREATE TABLE roles (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSONB NOT NULL DEFAULT '[]', -- Array of permission strings
    is_system BOOLEAN NOT NULL DEFAULT false, -- System roles cannot be deleted
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    UNIQUE(organization_id, name)
);

CREATE INDEX idx_roles_organization_id ON roles(organization_id);
```

#### 2.2.4 user_roles（ユーザーロール関連）

```sql
CREATE TABLE user_roles (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role_id UUID NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    assigned_by UUID REFERENCES users(id),
    assigned_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    UNIQUE(user_id, role_id)
);

CREATE INDEX idx_user_roles_user_id ON user_roles(user_id);
CREATE INDEX idx_user_roles_role_id ON user_roles(role_id);
```

#### 2.2.5 departments（部署）

```sql
CREATE TABLE departments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    parent_id UUID REFERENCES departments(id) ON DELETE SET NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    manager_id UUID REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_departments_organization_id ON departments(organization_id);
CREATE INDEX idx_departments_parent_id ON departments(parent_id);
```

#### 2.2.6 user_departments（ユーザー部署関連）

```sql
CREATE TABLE user_departments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    department_id UUID NOT NULL REFERENCES departments(id) ON DELETE CASCADE,
    is_primary BOOLEAN NOT NULL DEFAULT false,
    joined_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    UNIQUE(user_id, department_id)
);

CREATE INDEX idx_user_departments_user_id ON user_departments(user_id);
CREATE INDEX idx_user_departments_department_id ON user_departments(department_id);
```

#### 2.2.7 folders（フォルダ）

```sql
CREATE TABLE folders (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    parent_id UUID REFERENCES folders(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    path TEXT NOT NULL, -- Full path for easy querying: /folder1/folder2/folder3
    description TEXT,
    created_by UUID NOT NULL REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_folders_organization_id ON folders(organization_id);
CREATE INDEX idx_folders_parent_id ON folders(parent_id);
CREATE INDEX idx_folders_path ON folders USING gin(to_tsvector('simple', path));
```

#### 2.2.8 documents（文書）

```sql
CREATE TABLE documents (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    folder_id UUID REFERENCES folders(id) ON DELETE SET NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    file_type VARCHAR(50) NOT NULL, -- pdf, docx, xlsx, etc.
    file_size_bytes BIGINT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    storage_key VARCHAR(500) NOT NULL, -- S3 key
    storage_provider VARCHAR(50) NOT NULL DEFAULT 's3',
    checksum VARCHAR(64) NOT NULL, -- SHA-256

    -- AI関連
    ai_processed BOOLEAN NOT NULL DEFAULT false,
    ai_summary TEXT,
    ai_classification VARCHAR(100),
    embedding_id VARCHAR(255), -- Reference to vector DB

    -- メタデータ
    metadata JSONB DEFAULT '{}',
    custom_fields JSONB DEFAULT '{}',

    -- バージョン管理
    version INTEGER NOT NULL DEFAULT 1,
    is_latest_version BOOLEAN NOT NULL DEFAULT true,
    parent_document_id UUID REFERENCES documents(id) ON DELETE SET NULL,

    -- ステータス
    status VARCHAR(50) NOT NULL DEFAULT 'active', -- active, archived, deleted

    -- 監査
    created_by UUID NOT NULL REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_by UUID REFERENCES users(id),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    deleted_by UUID REFERENCES users(id),
    deleted_at TIMESTAMP WITH TIME ZONE,

    -- 統計
    view_count INTEGER NOT NULL DEFAULT 0,
    download_count INTEGER NOT NULL DEFAULT 0,
    last_accessed_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_documents_organization_id ON documents(organization_id);
CREATE INDEX idx_documents_folder_id ON documents(folder_id);
CREATE INDEX idx_documents_created_by ON documents(created_by);
CREATE INDEX idx_documents_status ON documents(status);
CREATE INDEX idx_documents_file_type ON documents(file_type);
CREATE INDEX idx_documents_checksum ON documents(checksum);
CREATE INDEX idx_documents_created_at ON documents(created_at DESC);
CREATE INDEX idx_documents_name_trgm ON documents USING gin(name gin_trgm_ops);
CREATE INDEX idx_documents_metadata ON documents USING gin(metadata);
```

#### 2.2.9 document_versions（文書バージョン）

```sql
CREATE TABLE document_versions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    document_id UUID NOT NULL REFERENCES documents(id) ON DELETE CASCADE,
    version INTEGER NOT NULL,
    storage_key VARCHAR(500) NOT NULL,
    file_size_bytes BIGINT NOT NULL,
    checksum VARCHAR(64) NOT NULL,
    change_summary TEXT,
    created_by UUID NOT NULL REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    UNIQUE(document_id, version)
);

CREATE INDEX idx_document_versions_document_id ON document_versions(document_id);
CREATE INDEX idx_document_versions_created_at ON document_versions(created_at DESC);
```

#### 2.2.10 tags（タグ）

```sql
CREATE TABLE tags (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7), -- Hex color code
    description TEXT,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    UNIQUE(organization_id, name)
);

CREATE INDEX idx_tags_organization_id ON tags(organization_id);
CREATE INDEX idx_tags_name ON tags(name);
```

#### 2.2.11 document_tags（文書タグ関連）

```sql
CREATE TABLE document_tags (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    document_id UUID NOT NULL REFERENCES documents(id) ON DELETE CASCADE,
    tag_id UUID NOT NULL REFERENCES tags(id) ON DELETE CASCADE,
    added_by UUID REFERENCES users(id),
    added_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    is_auto_generated BOOLEAN NOT NULL DEFAULT false,
    UNIQUE(document_id, tag_id)
);

CREATE INDEX idx_document_tags_document_id ON document_tags(document_id);
CREATE INDEX idx_document_tags_tag_id ON document_tags(tag_id);
```

#### 2.2.12 permissions（権限）

```sql
CREATE TABLE permissions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    resource_type VARCHAR(50) NOT NULL, -- document, folder
    resource_id UUID NOT NULL,

    -- 権限付与対象
    grantee_type VARCHAR(50) NOT NULL, -- user, department, role, organization
    grantee_id UUID NOT NULL,

    -- 権限レベル
    permission_level VARCHAR(50) NOT NULL, -- view, comment, edit, admin

    -- 追加設定
    can_share BOOLEAN NOT NULL DEFAULT false,
    can_download BOOLEAN NOT NULL DEFAULT true,
    expires_at TIMESTAMP WITH TIME ZONE,

    granted_by UUID NOT NULL REFERENCES users(id),
    granted_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),

    UNIQUE(resource_type, resource_id, grantee_type, grantee_id)
);

CREATE INDEX idx_permissions_resource ON permissions(resource_type, resource_id);
CREATE INDEX idx_permissions_grantee ON permissions(grantee_type, grantee_id);
CREATE INDEX idx_permissions_expires_at ON permissions(expires_at) WHERE expires_at IS NOT NULL;
```

#### 2.2.13 comments（コメント）

```sql
CREATE TABLE comments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    document_id UUID NOT NULL REFERENCES documents(id) ON DELETE CASCADE,
    parent_comment_id UUID REFERENCES comments(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    mentioned_users UUID[], -- Array of user IDs
    is_resolved BOOLEAN NOT NULL DEFAULT false,
    resolved_by UUID REFERENCES users(id),
    resolved_at TIMESTAMP WITH TIME ZONE,
    created_by UUID NOT NULL REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    deleted_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_comments_document_id ON comments(document_id);
CREATE INDEX idx_comments_parent_comment_id ON comments(parent_comment_id);
CREATE INDEX idx_comments_created_by ON comments(created_by);
CREATE INDEX idx_comments_is_resolved ON comments(is_resolved);
```

#### 2.2.14 favorites（お気に入り）

```sql
CREATE TABLE favorites (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    resource_type VARCHAR(50) NOT NULL, -- document, folder
    resource_id UUID NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    UNIQUE(user_id, resource_type, resource_id)
);

CREATE INDEX idx_favorites_user_id ON favorites(user_id);
CREATE INDEX idx_favorites_resource ON favorites(resource_type, resource_id);
```

#### 2.2.15 workflows（ワークフロー）

```sql
CREATE TABLE workflows (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(50) NOT NULL, -- approval, review
    config JSONB NOT NULL, -- Workflow configuration
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_by UUID NOT NULL REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_workflows_organization_id ON workflows(organization_id);
CREATE INDEX idx_workflows_type ON workflows(type);
```

#### 2.2.16 workflow_instances（ワークフローインスタンス）

```sql
CREATE TABLE workflow_instances (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workflow_id UUID NOT NULL REFERENCES workflows(id) ON DELETE CASCADE,
    document_id UUID NOT NULL REFERENCES documents(id) ON DELETE CASCADE,
    status VARCHAR(50) NOT NULL DEFAULT 'pending', -- pending, in_progress, approved, rejected, cancelled
    current_step INTEGER NOT NULL DEFAULT 1,
    initiated_by UUID NOT NULL REFERENCES users(id),
    initiated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    completed_at TIMESTAMP WITH TIME ZONE,
    completed_by UUID REFERENCES users(id)
);

CREATE INDEX idx_workflow_instances_workflow_id ON workflow_instances(workflow_id);
CREATE INDEX idx_workflow_instances_document_id ON workflow_instances(document_id);
CREATE INDEX idx_workflow_instances_status ON workflow_instances(status);
CREATE INDEX idx_workflow_instances_initiated_by ON workflow_instances(initiated_by);
```

#### 2.2.17 workflow_steps（ワークフローステップ）

```sql
CREATE TABLE workflow_steps (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workflow_instance_id UUID NOT NULL REFERENCES workflow_instances(id) ON DELETE CASCADE,
    step_number INTEGER NOT NULL,
    step_name VARCHAR(255) NOT NULL,
    assignee_type VARCHAR(50) NOT NULL, -- user, role, department
    assignee_id UUID NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending', -- pending, approved, rejected, skipped
    action VARCHAR(50), -- approve, reject
    comment TEXT,
    acted_by UUID REFERENCES users(id),
    acted_at TIMESTAMP WITH TIME ZONE,
    due_date TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_workflow_steps_workflow_instance_id ON workflow_steps(workflow_instance_id);
CREATE INDEX idx_workflow_steps_assignee ON workflow_steps(assignee_type, assignee_id);
CREATE INDEX idx_workflow_steps_status ON workflow_steps(status);
```

#### 2.2.18 subscriptions（サブスクリプション）

```sql
CREATE TABLE subscriptions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    plan_type VARCHAR(50) NOT NULL, -- free, standard, premium, enterprise
    status VARCHAR(50) NOT NULL, -- active, cancelled, past_due, trialing

    -- Stripe連携
    stripe_customer_id VARCHAR(255),
    stripe_subscription_id VARCHAR(255),
    stripe_price_id VARCHAR(255),

    -- 料金
    unit_price_cents INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1, -- Number of users
    billing_interval VARCHAR(20) NOT NULL, -- month, year

    -- 日付
    trial_start TIMESTAMP WITH TIME ZONE,
    trial_end TIMESTAMP WITH TIME ZONE,
    current_period_start TIMESTAMP WITH TIME ZONE NOT NULL,
    current_period_end TIMESTAMP WITH TIME ZONE NOT NULL,
    cancel_at TIMESTAMP WITH TIME ZONE,
    cancelled_at TIMESTAMP WITH TIME ZONE,
    ended_at TIMESTAMP WITH TIME ZONE,

    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_subscriptions_organization_id ON subscriptions(organization_id);
CREATE INDEX idx_subscriptions_stripe_customer_id ON subscriptions(stripe_customer_id);
CREATE INDEX idx_subscriptions_stripe_subscription_id ON subscriptions(stripe_subscription_id);
CREATE INDEX idx_subscriptions_status ON subscriptions(status);
```

#### 2.2.19 invoices（請求書）

```sql
CREATE TABLE invoices (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    subscription_id UUID REFERENCES subscriptions(id) ON DELETE SET NULL,

    -- Stripe連携
    stripe_invoice_id VARCHAR(255) UNIQUE,

    -- 金額
    subtotal_cents INTEGER NOT NULL,
    tax_cents INTEGER NOT NULL DEFAULT 0,
    total_cents INTEGER NOT NULL,
    amount_paid_cents INTEGER NOT NULL DEFAULT 0,
    currency VARCHAR(3) NOT NULL DEFAULT 'JPY',

    -- ステータス
    status VARCHAR(50) NOT NULL, -- draft, open, paid, void, uncollectible

    -- 日付
    invoice_date DATE NOT NULL,
    due_date DATE,
    paid_at TIMESTAMP WITH TIME ZONE,

    -- PDF
    invoice_pdf_url TEXT,

    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_invoices_organization_id ON invoices(organization_id);
CREATE INDEX idx_invoices_subscription_id ON invoices(subscription_id);
CREATE INDEX idx_invoices_stripe_invoice_id ON invoices(stripe_invoice_id);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_invoices_invoice_date ON invoices(invoice_date DESC);
```

#### 2.2.20 usage_records（使用量記録）

```sql
CREATE TABLE usage_records (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    record_date DATE NOT NULL,

    -- AI使用量
    ai_search_count INTEGER NOT NULL DEFAULT 0,
    ai_summarize_count INTEGER NOT NULL DEFAULT 0,
    ai_classify_count INTEGER NOT NULL DEFAULT 0,

    -- ストレージ
    storage_bytes BIGINT NOT NULL DEFAULT 0,

    -- ユーザー
    active_users_count INTEGER NOT NULL DEFAULT 0,

    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    UNIQUE(organization_id, record_date)
);

CREATE INDEX idx_usage_records_organization_date ON usage_records(organization_id, record_date DESC);
```

#### 2.2.21 audit_logs（監査ログ）

```sql
CREATE TABLE audit_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,

    -- アクション
    action VARCHAR(100) NOT NULL, -- document.view, document.edit, user.invite, etc.
    resource_type VARCHAR(50) NOT NULL,
    resource_id UUID,

    -- 詳細
    details JSONB DEFAULT '{}',

    -- メタデータ
    ip_address INET,
    user_agent TEXT,

    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
) PARTITION BY RANGE (created_at);

-- パーティション作成例（月別）
CREATE TABLE audit_logs_2025_11 PARTITION OF audit_logs
    FOR VALUES FROM ('2025-11-01') TO ('2025-12-01');

CREATE INDEX idx_audit_logs_organization_id ON audit_logs(organization_id, created_at DESC);
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id, created_at DESC);
CREATE INDEX idx_audit_logs_action ON audit_logs(action);
CREATE INDEX idx_audit_logs_resource ON audit_logs(resource_type, resource_id);
```

#### 2.2.22 notifications（通知）

```sql
CREATE TABLE notifications (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL, -- comment, mention, approval_request, etc.
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,

    -- リンク
    link_type VARCHAR(50), -- document, workflow, comment
    link_id UUID,

    -- ステータス
    is_read BOOLEAN NOT NULL DEFAULT false,
    read_at TIMESTAMP WITH TIME ZONE,

    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_notifications_user_id ON notifications(user_id, created_at DESC);
CREATE INDEX idx_notifications_is_read ON notifications(user_id, is_read);
```

#### 2.2.23 api_keys（APIキー）

```sql
CREATE TABLE api_keys (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    key_hash VARCHAR(64) NOT NULL UNIQUE, -- SHA-256 hash of the key
    key_prefix VARCHAR(10) NOT NULL, -- First 8 chars for identification

    -- 権限
    scopes TEXT[] NOT NULL, -- Array of permission scopes

    -- 制限
    rate_limit INTEGER, -- Requests per minute

    -- ステータス
    is_active BOOLEAN NOT NULL DEFAULT true,
    last_used_at TIMESTAMP WITH TIME ZONE,
    expires_at TIMESTAMP WITH TIME ZONE,

    created_by UUID NOT NULL REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    revoked_at TIMESTAMP WITH TIME ZONE,
    revoked_by UUID REFERENCES users(id)
);

CREATE INDEX idx_api_keys_organization_id ON api_keys(organization_id);
CREATE INDEX idx_api_keys_key_hash ON api_keys(key_hash);
CREATE INDEX idx_api_keys_is_active ON api_keys(is_active);
```

---

## 3. MongoDB コレクション設計

### 3.1 document_metadata コレクション

```javascript
{
  _id: ObjectId("..."),
  document_id: UUID, // Reference to PostgreSQL
  organization_id: UUID,

  // 拡張メタデータ
  extracted_text: String, // Full text for search
  ocr_text: String, // OCR extracted text
  language: String,
  page_count: Integer,

  // AI生成データ
  ai_tags: [String],
  ai_entities: [
    {
      type: String, // person, organization, location, date
      value: String,
      confidence: Float
    }
  ],
  ai_topics: [
    {
      topic: String,
      confidence: Float
    }
  ],

  // 統計情報
  word_count: Integer,
  character_count: Integer,

  created_at: ISODate,
  updated_at: ISODate
}

// インデックス
db.document_metadata.createIndex({ document_id: 1 }, { unique: true });
db.document_metadata.createIndex({ organization_id: 1, created_at: -1 });
db.document_metadata.createIndex({ extracted_text: "text", ai_tags: "text" });
```

### 3.2 search_history コレクション

```javascript
{
  _id: ObjectId("..."),
  user_id: UUID,
  organization_id: UUID,

  query: String,
  search_type: String, // semantic, keyword, advanced
  filters: Object,

  results_count: Integer,
  clicked_results: [
    {
      document_id: UUID,
      position: Integer,
      clicked_at: ISODate
    }
  ],

  created_at: ISODate
}

// インデックス
db.search_history.createIndex({ user_id: 1, created_at: -1 });
db.search_history.createIndex({ organization_id: 1, created_at: -1 });
db.search_history.createIndex({ query: "text" });

// TTL Index - 90日後に自動削除
db.search_history.createIndex({ created_at: 1 }, { expireAfterSeconds: 7776000 });
```

### 3.3 activity_logs コレクション

```javascript
{
  _id: ObjectId("..."),
  organization_id: UUID,
  user_id: UUID,

  event_type: String, // document.view, document.download, search, etc.
  resource_type: String,
  resource_id: UUID,

  metadata: {
    duration_ms: Integer,
    source: String, // web, mobile, api
    // その他のイベント固有データ
  },

  created_at: ISODate
}

// インデックス
db.activity_logs.createIndex({ organization_id: 1, created_at: -1 });
db.activity_logs.createIndex({ user_id: 1, created_at: -1 });
db.activity_logs.createIndex({ event_type: 1, created_at: -1 });

// TTL Index - 30日後に自動削除
db.activity_logs.createIndex({ created_at: 1 }, { expireAfterSeconds: 2592000 });
```

### 3.4 analytics_events コレクション

```javascript
{
  _id: ObjectId("..."),
  organization_id: UUID,

  event_name: String,
  event_category: String,

  properties: Object, // Flexible event properties

  aggregation_date: ISODate, // For daily aggregation
  count: Integer,

  created_at: ISODate
}

// インデックス
db.analytics_events.createIndex({ organization_id: 1, aggregation_date: -1 });
db.analytics_events.createIndex({ event_name: 1, aggregation_date: -1 });
```

---

## 4. Redis データ構造

### 4.1 セッション管理

```
Key: session:{session_id}
Type: Hash
TTL: 7200 (2時間)
Fields:
  - user_id: UUID
  - organization_id: UUID
  - email: String
  - roles: JSON array
  - created_at: Timestamp
  - last_activity: Timestamp
```

### 4.2 レート制限

```
Key: ratelimit:{user_id}:{endpoint}
Type: String (counter)
TTL: 60 (1分)
Value: Request count

Key: ratelimit:ai_search:{organization_id}:monthly
Type: String (counter)
TTL: End of month
Value: AI search count
```

### 4.3 キャッシュ

```
# ユーザー情報キャッシュ
Key: cache:user:{user_id}
Type: String (JSON)
TTL: 300 (5分)

# 文書メタデータキャッシュ
Key: cache:document:{document_id}
Type: String (JSON)
TTL: 600 (10分)

# 検索結果キャッシュ
Key: cache:search:{hash(query)}
Type: String (JSON)
TTL: 180 (3分)

# 組織設定キャッシュ
Key: cache:org_settings:{organization_id}
Type: Hash
TTL: 1800 (30分)
```

### 4.4 リアルタイム機能（Pub/Sub）

```
# 通知チャネル
Channel: notifications:{user_id}
Message: JSON notification

# 文書更新チャネル
Channel: document_updates:{document_id}
Message: JSON update event

# 組織イベント
Channel: org_events:{organization_id}
Message: JSON event
```

### 4.5 ジョブキュー（Bull）

```
Queue: document_processing
Jobs:
  - generate_preview
  - generate_embedding
  - extract_text
  - ocr_processing

Queue: notifications
Jobs:
  - send_email
  - send_webhook

Queue: analytics
Jobs:
  - aggregate_daily_stats
  - generate_report
```

---

## 5. Vector Database（Pinecone/Qdrant）

### 5.1 インデックス構造

```
Index Name: documents-{environment}
Dimensions: 1536 (OpenAI ada-002)
Metric: cosine

Vector Metadata:
{
  document_id: UUID,
  organization_id: UUID,
  folder_id: UUID,
  file_type: String,
  title: String,
  created_by: UUID,
  created_at: Timestamp,
  chunk_index: Integer, // For large documents split into chunks
  total_chunks: Integer
}
```

### 5.2 ベクトル管理戦略

- **チャンキング**: 大きな文書は複数のチャンクに分割（各4000トークン）
- **メタデータフィルタリング**: 組織ID、フォルダIDでのフィルタリング
- **更新戦略**: 文書更新時は古いベクトルを削除して新規登録
- **削除戦略**: 文書削除時はベクトルも削除（論理削除の場合は保持）

---

## 6. Elasticsearch インデックス設計

### 6.1 documents インデックス

```json
{
  "settings": {
    "number_of_shards": 3,
    "number_of_replicas": 1,
    "analysis": {
      "analyzer": {
        "japanese_analyzer": {
          "type": "custom",
          "tokenizer": "kuromoji_tokenizer",
          "filter": ["kuromoji_baseform", "kuromoji_part_of_speech", "cjk_width", "lowercase"]
        }
      }
    }
  },
  "mappings": {
    "properties": {
      "document_id": { "type": "keyword" },
      "organization_id": { "type": "keyword" },
      "folder_id": { "type": "keyword" },
      "name": {
        "type": "text",
        "analyzer": "japanese_analyzer",
        "fields": {
          "keyword": { "type": "keyword" }
        }
      },
      "description": {
        "type": "text",
        "analyzer": "japanese_analyzer"
      },
      "content": {
        "type": "text",
        "analyzer": "japanese_analyzer"
      },
      "file_type": { "type": "keyword" },
      "tags": { "type": "keyword" },
      "created_by": { "type": "keyword" },
      "created_at": { "type": "date" },
      "updated_at": { "type": "date" },
      "view_count": { "type": "integer" },
      "download_count": { "type": "integer" }
    }
  }
}
```

### 6.2 audit_logs インデックス

```json
{
  "settings": {
    "number_of_shards": 5,
    "number_of_replicas": 1,
    "index.lifecycle.name": "audit_logs_policy",
    "index.lifecycle.rollover_alias": "audit_logs"
  },
  "mappings": {
    "properties": {
      "organization_id": { "type": "keyword" },
      "user_id": { "type": "keyword" },
      "action": { "type": "keyword" },
      "resource_type": { "type": "keyword" },
      "resource_id": { "type": "keyword" },
      "details": { "type": "object", "enabled": false },
      "ip_address": { "type": "ip" },
      "timestamp": { "type": "date" }
    }
  }
}
```

---

## 7. データ整合性・制約

### 7.1 外部キー制約
- すべての重要な関連にFOREIGN KEY制約を設定
- ON DELETE CASCADEは慎重に使用（主に中間テーブル）
- ON DELETE SET NULLは参照先が削除されても影響が少ない場合

### 7.2 一意性制約
- メールアドレス、組織スラッグなどにUNIQUE制約
- 複合一意制約（例: organization_id + name）

### 7.3 CHECK制約

```sql
-- ストレージ使用量が制限を超えないように
ALTER TABLE organizations ADD CONSTRAINT check_storage_usage
    CHECK (storage_used_bytes <= (storage_limit_gb * 1024 * 1024 * 1024));

-- ファイルサイズが正の数
ALTER TABLE documents ADD CONSTRAINT check_file_size
    CHECK (file_size_bytes > 0);

-- バージョン番号が正の数
ALTER TABLE documents ADD CONSTRAINT check_version
    CHECK (version > 0);
```

### 7.4 データベース間の整合性
- PostgreSQL ↔ MongoDB: `document_id`での参照
- PostgreSQL ↔ Redis: キーにUUIDを使用
- PostgreSQL ↔ Vector DB: `embedding_id`での参照
- イベント駆動アーキテクチャでデータ同期

---

## 8. パフォーマンス最適化

### 8.1 インデックス戦略
- **B-tree**: 等価検索、範囲検索
- **GiST/GIN**: 全文検索、JSONB検索
- **BRIN**: 大きなテーブルの時系列データ
- **部分インデックス**: 特定条件のみ（例: WHERE deleted_at IS NULL）

### 8.2 パーティショニング
- **audit_logs**: 月別パーティション（RANGE）
- **usage_records**: 年別パーティション（RANGE）
- 古いパーティションは圧縮または削除

### 8.3 クエリ最適化
- **N+1問題**: JOINまたはバッチロード
- **Materialized Views**: 集計クエリの事前計算
- **接続プーリング**: PgBouncer使用

### 8.4 キャッシング
- **アプリケーションレベル**: Redisキャッシュ
- **クエリレベル**: PostgreSQL shared_buffers
- **オブジェクトレベル**: CDN（S3ファイル）

---

## 9. バックアップ・リカバリ

### 9.1 PostgreSQL
- **継続的アーカイブ**: WALアーカイブ
- **フルバックアップ**: 日次（pg_dump）
- **PITR**: 任意の時点への復旧可能
- **レプリカ**: Read Replica（非同期レプリケーション）

### 9.2 MongoDB
- **Oplog-based backup**: 継続的バックアップ
- **スナップショット**: 日次
- **レプリカセット**: 3ノード構成

### 9.3 Redis
- **AOF**: Append-Only File（耐久性）
- **RDB**: スナップショット（高速復旧）
- **レプリケーション**: Master-Replica構成

### 9.4 S3
- **バージョニング**: 有効化
- **Cross-Region Replication**: DR対策
- **Glacier**: 長期アーカイブ

---

## 10. セキュリティ

### 10.1 データ暗号化
- **転送時**: SSL/TLS
- **保存時**: Transparent Data Encryption (TDE)
- **アプリケーションレベル**: 機密フィールドの暗号化

### 10.2 アクセス制御
- **最小権限の原則**: アプリケーション用DBユーザーは必要最小限の権限
- **ロール分離**: 読み取り専用ユーザー、管理者ユーザー
- **IPホワイトリスト**: VPC内からのみアクセス

### 10.3 監査
- **監査ログ**: すべての重要操作を記録
- **定期レビュー**: 不審なアクセスパターンの検出

---

## 11. マイグレーション戦略

### 11.1 スキーママイグレーション
- **ツール**: Flyway / Liquibase / TypeORM Migrations
- **バージョン管理**: Gitでマイグレーションファイル管理
- **ロールバック**: 各マイグレーションにロールバックスクリプト

### 11.2 データマイグレーション
- **段階的移行**: ダウンタイムを最小化
- **検証**: 移行前後のデータ整合性チェック
- **ロールバックプラン**: 問題発生時の対策

---

## 12. 付録

### 12.1 命名規則

- **テーブル**: 小文字、複数形、アンダースコア区切り（例: `users`, `document_tags`）
- **カラム**: 小文字、アンダースコア区切り（例: `created_at`, `user_id`）
- **インデックス**: `idx_{table}_{columns}`（例: `idx_users_email`）
- **外部キー**: `fk_{table}_{referenced_table}`

### 12.2 データ型選択

| 用途 | データ型 | 理由 |
|------|---------|------|
| ID | UUID | グローバルユニーク、セキュリティ |
| タイムスタンプ | TIMESTAMP WITH TIME ZONE | タイムゾーン対応 |
| 金額 | INTEGER (cents) | 浮動小数点の精度問題回避 |
| JSON | JSONB | インデックス可能、高速 |
| 全文検索 | TEXT + tsvector | PostgreSQL全文検索機能 |

### 12.3 サンプルデータ生成

開発・テスト用のサンプルデータ生成スクリプトを用意：
- 組織: 10件
- ユーザー: 組織あたり5-20名
- 文書: 組織あたり100-500件
- タグ: 組織あたり20-50件

---

## 変更履歴

| バージョン | 日付 | 変更内容 | 作成者 |
|-----------|------|----------|--------|
| 1.0 | 2025-11-04 | 初版作成 | 開発チーム |

---

**承認**
- データベースアーキテクト: _____________
- CTO: _____________
- 日付: _____________
