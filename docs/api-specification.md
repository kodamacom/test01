# API仕様書

## 文書情報
- **プロジェクト名**: DocuMind - AI-Powered Document Management System
- **文書ID**: API-DOCUMIND-001
- **バージョン**: 1.0
- **最終更新日**: 2025-11-04
- **作成者**: 開発チーム
- **ステータス**: Draft

---

## 1. API概要

### 1.1 基本情報
- **ベースURL**:
  - Production: `https://api.documind.io/v1`
  - Staging: `https://api-staging.documind.io/v1`
  - Development: `http://localhost:3000/api/v1`
- **プロトコル**: HTTPS (TLS 1.3)
- **データフォーマット**: JSON
- **認証方式**: Bearer Token (JWT), API Key
- **文字エンコーディング**: UTF-8

### 1.2 API設計原則
- RESTful API設計
- リソース指向
- HTTPメソッドの適切な使用
- ステートレス
- バージョニング対応（URLパス: `/v1`, `/v2`）

### 1.3 HTTPメソッド

| メソッド | 用途 | 冪等性 | 安全性 |
|---------|------|-------|-------|
| GET | リソース取得 | ✓ | ✓ |
| POST | リソース作成 | ✗ | ✗ |
| PUT | リソース全体更新 | ✓ | ✗ |
| PATCH | リソース部分更新 | ✗ | ✗ |
| DELETE | リソース削除 | ✓ | ✗ |

---

## 2. 認証・認可

### 2.1 JWT認証

#### 2.1.1 ログイン

```http
POST /auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "SecurePassword123!",
  "remember_me": false
}
```

**レスポンス成功 (200 OK)**
```json
{
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "email": "user@example.com",
    "first_name": "太郎",
    "last_name": "山田",
    "organization": {
      "id": "660e8400-e29b-41d4-a716-446655440000",
      "name": "株式会社サンプル"
    }
  }
}
```

**レスポンス失敗 (401 Unauthorized)**
```json
{
  "error": {
    "code": "INVALID_CREDENTIALS",
    "message": "メールアドレスまたはパスワードが正しくありません",
    "details": null
  }
}
```

#### 2.1.2 トークンリフレッシュ

```http
POST /auth/refresh
Content-Type: application/json

{
  "refresh_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**レスポンス成功 (200 OK)**
```json
{
  "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "Bearer",
  "expires_in": 3600
}
```

#### 2.1.3 ログアウト

```http
POST /auth/logout
Authorization: Bearer {access_token}
```

**レスポンス成功 (204 No Content)**

### 2.2 APIキー認証

```http
GET /documents
X-API-Key: doc_live_abc123def456ghi789
```

### 2.3 認可ヘッダー

すべての認証が必要なエンドポイントには以下のヘッダーが必須：

```http
Authorization: Bearer {access_token}
```

---

## 3. 共通仕様

### 3.1 リクエストヘッダー

| ヘッダー | 必須 | 説明 | 例 |
|---------|------|------|-----|
| Content-Type | ✓ | コンテンツタイプ | application/json |
| Authorization | * | 認証トークン | Bearer {token} |
| X-API-Key | * | APIキー | doc_live_xxx |
| X-Organization-ID | - | 組織ID（マルチテナント） | uuid |
| Accept-Language | - | 言語設定 | ja, en |

*認証が必要なエンドポイントでは必須

### 3.2 レスポンス形式

#### 3.2.1 成功レスポンス

**単一リソース**
```json
{
  "data": {
    "id": "uuid",
    "name": "example",
    ...
  }
}
```

**リソースリスト（ページネーション）**
```json
{
  "data": [
    { "id": "1", ... },
    { "id": "2", ... }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5,
    "has_next": true,
    "has_prev": false
  },
  "links": {
    "first": "/documents?page=1",
    "last": "/documents?page=5",
    "next": "/documents?page=2",
    "prev": null
  }
}
```

#### 3.2.2 エラーレスポンス

```json
{
  "error": {
    "code": "ERROR_CODE",
    "message": "エラーメッセージ",
    "details": {
      "field": "email",
      "reason": "invalid_format"
    },
    "request_id": "req_abc123"
  }
}
```

### 3.3 HTTPステータスコード

| コード | 意味 | 用途 |
|-------|------|------|
| 200 | OK | 成功 |
| 201 | Created | リソース作成成功 |
| 204 | No Content | 成功（レスポンスボディなし） |
| 400 | Bad Request | リクエストが不正 |
| 401 | Unauthorized | 認証エラー |
| 403 | Forbidden | 権限エラー |
| 404 | Not Found | リソースが存在しない |
| 409 | Conflict | リソースの競合 |
| 422 | Unprocessable Entity | バリデーションエラー |
| 429 | Too Many Requests | レート制限超過 |
| 500 | Internal Server Error | サーバーエラー |
| 503 | Service Unavailable | サービス利用不可 |

### 3.4 エラーコード

| コード | HTTPステータス | 説明 |
|-------|---------------|------|
| INVALID_REQUEST | 400 | リクエストが不正 |
| UNAUTHORIZED | 401 | 認証が必要 |
| INVALID_CREDENTIALS | 401 | 認証情報が無効 |
| TOKEN_EXPIRED | 401 | トークンの有効期限切れ |
| FORBIDDEN | 403 | アクセス権限なし |
| NOT_FOUND | 404 | リソースが存在しない |
| CONFLICT | 409 | リソースの競合 |
| VALIDATION_ERROR | 422 | バリデーションエラー |
| RATE_LIMIT_EXCEEDED | 429 | レート制限超過 |
| INTERNAL_ERROR | 500 | 内部エラー |
| SERVICE_UNAVAILABLE | 503 | サービス利用不可 |

### 3.5 ページネーション

**クエリパラメータ**
- `page`: ページ番号（デフォルト: 1）
- `per_page`: 1ページあたりの件数（デフォルト: 20、最大: 100）

**例**
```http
GET /documents?page=2&per_page=50
```

### 3.6 フィルタリング・ソート

**フィルタリング**
```http
GET /documents?file_type=pdf&created_after=2025-01-01
```

**ソート**
```http
GET /documents?sort_by=created_at&order=desc
```

**複数ソート**
```http
GET /documents?sort_by=name,created_at&order=asc,desc
```

### 3.7 フィールド選択（Sparse Fieldsets）

```http
GET /documents?fields=id,name,created_at
```

### 3.8 レート制限

**ヘッダー**
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 995
X-RateLimit-Reset: 1640995200
```

**制限値**
- Free: 100リクエスト/時間
- Standard: 1,000リクエスト/時間
- Premium: 5,000リクエスト/時間
- Enterprise: 10,000リクエスト/時間

---

## 4. エンドポイント仕様

### 4.1 文書管理

#### 4.1.1 文書一覧取得

```http
GET /documents
Authorization: Bearer {token}
```

**クエリパラメータ**
| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| page | integer | - | ページ番号 |
| per_page | integer | - | 1ページあたりの件数 |
| folder_id | uuid | - | フォルダID |
| file_type | string | - | ファイルタイプ（pdf, docx, etc.） |
| tags | string | - | タグ（カンマ区切り） |
| created_after | date | - | 作成日以降 |
| created_before | date | - | 作成日以前 |
| sort_by | string | - | ソート項目 |
| order | string | - | asc/desc |

**レスポンス (200 OK)**
```json
{
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "契約書_2025年度.pdf",
      "description": "2025年度の契約書",
      "file_type": "pdf",
      "file_size_bytes": 1024000,
      "mime_type": "application/pdf",
      "folder": {
        "id": "660e8400-e29b-41d4-a716-446655440000",
        "name": "契約書"
      },
      "tags": [
        { "id": "tag1", "name": "重要", "color": "#FF0000" }
      ],
      "version": 1,
      "ai_summary": "本契約書は...",
      "ai_classification": "契約書",
      "created_by": {
        "id": "user1",
        "name": "山田太郎"
      },
      "created_at": "2025-11-04T10:00:00Z",
      "updated_at": "2025-11-04T10:00:00Z",
      "view_count": 10,
      "download_count": 5
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5,
    "has_next": true,
    "has_prev": false
  }
}
```

#### 4.1.2 文書詳細取得

```http
GET /documents/{document_id}
Authorization: Bearer {token}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "契約書_2025年度.pdf",
    "description": "2025年度の契約書",
    "file_type": "pdf",
    "file_size_bytes": 1024000,
    "mime_type": "application/pdf",
    "storage_key": "orgs/660e8400/docs/550e8400.pdf",
    "checksum": "sha256:abc123...",
    "folder": {
      "id": "660e8400-e29b-41d4-a716-446655440000",
      "name": "契約書",
      "path": "/契約書"
    },
    "tags": [
      { "id": "tag1", "name": "重要", "color": "#FF0000" }
    ],
    "metadata": {
      "author": "山田太郎",
      "department": "営業部"
    },
    "version": 1,
    "ai_summary": "本契約書は2025年度の取引に関する...",
    "ai_classification": "契約書",
    "permissions": {
      "can_view": true,
      "can_edit": true,
      "can_delete": false,
      "can_share": true
    },
    "created_by": {
      "id": "user1",
      "name": "山田太郎",
      "email": "yamada@example.com"
    },
    "created_at": "2025-11-04T10:00:00Z",
    "updated_at": "2025-11-04T10:00:00Z",
    "view_count": 10,
    "download_count": 5,
    "last_accessed_at": "2025-11-04T15:00:00Z"
  }
}
```

#### 4.1.3 文書アップロード

```http
POST /documents
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: (binary)
folder_id: "660e8400-e29b-41d4-a716-446655440000"
name: "契約書_2025年度.pdf"
description: "2025年度の契約書"
tags: ["重要", "契約"]
metadata: {"author": "山田太郎"}
```

**レスポンス (201 Created)**
```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "契約書_2025年度.pdf",
    "status": "processing",
    "message": "ファイルのアップロードが完了しました。AI処理中です。"
  }
}
```

#### 4.1.4 文書更新

```http
PATCH /documents/{document_id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "契約書_2025年度_更新版.pdf",
  "description": "2025年度の契約書（更新版）",
  "folder_id": "660e8400-e29b-41d4-a716-446655440000",
  "metadata": {
    "author": "山田太郎",
    "department": "営業部"
  }
}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "契約書_2025年度_更新版.pdf",
    "updated_at": "2025-11-04T11:00:00Z"
  }
}
```

#### 4.1.5 文書削除

```http
DELETE /documents/{document_id}
Authorization: Bearer {token}
```

**レスポンス (204 No Content)**

#### 4.1.6 文書ダウンロード

```http
GET /documents/{document_id}/download
Authorization: Bearer {token}
```

**レスポンス (302 Redirect)**
```http
Location: https://s3.amazonaws.com/signed-url...
```

または

**レスポンス (200 OK)**
```http
Content-Type: application/pdf
Content-Disposition: attachment; filename="contract.pdf"

(binary data)
```

#### 4.1.7 文書プレビュー

```http
GET /documents/{document_id}/preview
Authorization: Bearer {token}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "preview_url": "https://cdn.documind.io/previews/550e8400.png",
    "pages": [
      {
        "page_number": 1,
        "thumbnail_url": "https://cdn.documind.io/thumbnails/550e8400_p1.png"
      }
    ]
  }
}
```

#### 4.1.8 文書バージョン一覧

```http
GET /documents/{document_id}/versions
Authorization: Bearer {token}
```

**レスポンス (200 OK)**
```json
{
  "data": [
    {
      "id": "version1",
      "version": 2,
      "file_size_bytes": 1024000,
      "change_summary": "契約金額を更新",
      "created_by": {
        "id": "user1",
        "name": "山田太郎"
      },
      "created_at": "2025-11-04T11:00:00Z"
    },
    {
      "id": "version0",
      "version": 1,
      "file_size_bytes": 1020000,
      "change_summary": "初版",
      "created_by": {
        "id": "user1",
        "name": "山田太郎"
      },
      "created_at": "2025-11-04T10:00:00Z"
    }
  ]
}
```

### 4.2 検索

#### 4.2.1 通常検索

```http
GET /search
Authorization: Bearer {token}
```

**クエリパラメータ**
| パラメータ | 型 | 必須 | 説明 |
|-----------|-----|------|------|
| q | string | ✓ | 検索クエリ |
| type | string | - | 検索タイプ（keyword/semantic） |
| file_types | string | - | ファイルタイプフィルタ |
| folder_id | uuid | - | フォルダID |
| tags | string | - | タグフィルタ |
| page | integer | - | ページ番号 |
| per_page | integer | - | 1ページあたりの件数 |

**レスポンス (200 OK)**
```json
{
  "data": {
    "query": "契約書",
    "results": [
      {
        "document": {
          "id": "doc1",
          "name": "契約書_2025年度.pdf",
          "snippet": "本<mark>契約書</mark>は2025年度の...",
          "relevance_score": 0.95
        },
        "highlights": [
          "本<mark>契約書</mark>は..."
        ]
      }
    ],
    "total_results": 50,
    "search_time_ms": 120
  }
}
```

#### 4.2.2 AIセマンティック検索

```http
POST /search/semantic
Authorization: Bearer {token}
Content-Type: application/json

{
  "query": "昨年の売上報告書はどこ？",
  "filters": {
    "file_types": ["pdf", "xlsx"],
    "date_range": {
      "from": "2024-01-01",
      "to": "2024-12-31"
    }
  },
  "limit": 10
}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "query": "昨年の売上報告書はどこ？",
    "interpreted_query": "2024年度の売上レポート",
    "results": [
      {
        "document": {
          "id": "doc1",
          "name": "2024年度_売上報告書.xlsx",
          "similarity_score": 0.92,
          "reason": "クエリと文書の意味的な関連性が高い"
        }
      }
    ],
    "total_results": 5,
    "search_time_ms": 350
  }
}
```

### 4.3 AI機能

#### 4.3.1 文書要約

```http
POST /ai/summarize
Authorization: Bearer {token}
Content-Type: application/json

{
  "document_id": "550e8400-e29b-41d4-a716-446655440000",
  "length": "medium",
  "format": "bullets"
}
```

**パラメータ**
- `length`: short (100文字), medium (300文字), long (500文字)
- `format`: paragraph, bullets

**レスポンス (200 OK)**
```json
{
  "data": {
    "summary": "- 本契約書は2025年度の取引に関する内容\n- 契約期間は2025年4月1日〜2026年3月31日\n- 契約金額は1,000万円",
    "length": "medium",
    "format": "bullets",
    "generated_at": "2025-11-04T10:00:00Z"
  }
}
```

#### 4.3.2 文書分類

```http
POST /ai/classify
Authorization: Bearer {token}
Content-Type: application/json

{
  "document_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "classification": "契約書",
    "confidence": 0.95,
    "categories": [
      {
        "name": "契約書",
        "confidence": 0.95
      },
      {
        "name": "法務文書",
        "confidence": 0.85
      }
    ]
  }
}
```

#### 4.3.3 タグ自動生成

```http
POST /ai/generate-tags
Authorization: Bearer {token}
Content-Type: application/json

{
  "document_id": "550e8400-e29b-41d4-a716-446655440000",
  "max_tags": 5
}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "tags": [
      { "name": "契約", "confidence": 0.95 },
      { "name": "法務", "confidence": 0.90 },
      { "name": "2025年度", "confidence": 0.85 }
    ]
  }
}
```

#### 4.3.4 重複文書検出

```http
POST /ai/detect-duplicates
Authorization: Bearer {token}
Content-Type: application/json

{
  "document_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "duplicates": [
      {
        "document": {
          "id": "doc2",
          "name": "契約書_2025年度_コピー.pdf"
        },
        "similarity": 0.98,
        "type": "exact_match"
      }
    ],
    "total_duplicates": 1
  }
}
```

### 4.4 フォルダ管理

#### 4.4.1 フォルダ一覧取得

```http
GET /folders
Authorization: Bearer {token}
```

**クエリパラメータ**
- `parent_id`: 親フォルダID（指定しない場合はルート）

**レスポンス (200 OK)**
```json
{
  "data": [
    {
      "id": "folder1",
      "name": "契約書",
      "path": "/契約書",
      "parent_id": null,
      "document_count": 50,
      "created_at": "2025-11-04T10:00:00Z"
    }
  ]
}
```

#### 4.4.2 フォルダ作成

```http
POST /folders
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "2025年度契約書",
  "parent_id": "folder1",
  "description": "2025年度の契約書を格納"
}
```

**レスポンス (201 Created)**
```json
{
  "data": {
    "id": "new_folder_id",
    "name": "2025年度契約書",
    "path": "/契約書/2025年度契約書",
    "created_at": "2025-11-04T10:00:00Z"
  }
}
```

### 4.5 ユーザー管理

#### 4.5.1 ユーザー一覧取得

```http
GET /users
Authorization: Bearer {token}
```

**レスポンス (200 OK)**
```json
{
  "data": [
    {
      "id": "user1",
      "email": "yamada@example.com",
      "first_name": "太郎",
      "last_name": "山田",
      "roles": [
        { "id": "role1", "name": "管理者" }
      ],
      "is_active": true,
      "last_login_at": "2025-11-04T09:00:00Z",
      "created_at": "2025-01-01T00:00:00Z"
    }
  ]
}
```

#### 4.5.2 ユーザー招待

```http
POST /users/invite
Authorization: Bearer {token}
Content-Type: application/json

{
  "email": "newuser@example.com",
  "first_name": "花子",
  "last_name": "佐藤",
  "role_ids": ["role1"],
  "send_email": true
}
```

**レスポンス (201 Created)**
```json
{
  "data": {
    "id": "new_user_id",
    "email": "newuser@example.com",
    "invitation_sent": true,
    "invitation_expires_at": "2025-11-11T10:00:00Z"
  }
}
```

### 4.6 権限管理

#### 4.6.1 権限設定

```http
POST /permissions
Authorization: Bearer {token}
Content-Type: application/json

{
  "resource_type": "document",
  "resource_id": "550e8400-e29b-41d4-a716-446655440000",
  "grantee_type": "user",
  "grantee_id": "user2",
  "permission_level": "edit",
  "can_share": true,
  "expires_at": "2025-12-31T23:59:59Z"
}
```

**レスポンス (201 Created)**
```json
{
  "data": {
    "id": "perm1",
    "resource_type": "document",
    "resource_id": "550e8400-e29b-41d4-a716-446655440000",
    "grantee": {
      "type": "user",
      "id": "user2",
      "name": "佐藤花子"
    },
    "permission_level": "edit",
    "granted_at": "2025-11-04T10:00:00Z"
  }
}
```

### 4.7 ワークフロー

#### 4.7.1 承認依頼

```http
POST /workflows
Authorization: Bearer {token}
Content-Type: application/json

{
  "workflow_type": "approval",
  "document_id": "550e8400-e29b-41d4-a716-446655440000",
  "approvers": [
    {
      "step": 1,
      "assignee_type": "user",
      "assignee_id": "user2"
    },
    {
      "step": 2,
      "assignee_type": "role",
      "assignee_id": "manager_role"
    }
  ],
  "message": "承認をお願いします"
}
```

**レスポンス (201 Created)**
```json
{
  "data": {
    "id": "workflow1",
    "status": "pending",
    "current_step": 1,
    "created_at": "2025-11-04T10:00:00Z"
  }
}
```

#### 4.7.2 承認/却下

```http
POST /workflows/{workflow_id}/approve
Authorization: Bearer {token}
Content-Type: application/json

{
  "action": "approve",
  "comment": "承認します"
}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "id": "workflow1",
    "status": "approved",
    "completed_at": "2025-11-04T11:00:00Z"
  }
}
```

### 4.8 サブスクリプション・請求

#### 4.8.1 サブスクリプション情報取得

```http
GET /subscriptions/current
Authorization: Bearer {token}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "id": "sub1",
    "plan_type": "premium",
    "status": "active",
    "quantity": 10,
    "unit_price_cents": 250000,
    "current_period_start": "2025-11-01T00:00:00Z",
    "current_period_end": "2025-12-01T00:00:00Z",
    "cancel_at": null
  }
}
```

#### 4.8.2 プランアップグレード

```http
POST /subscriptions/upgrade
Authorization: Bearer {token}
Content-Type: application/json

{
  "plan_type": "enterprise",
  "quantity": 50
}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "id": "sub1",
    "plan_type": "enterprise",
    "status": "active",
    "prorated_charge_cents": 500000,
    "next_invoice_date": "2025-12-01"
  }
}
```

#### 4.8.3 請求書一覧

```http
GET /invoices
Authorization: Bearer {token}
```

**レスポンス (200 OK)**
```json
{
  "data": [
    {
      "id": "inv1",
      "invoice_number": "INV-2025-001",
      "status": "paid",
      "total_cents": 250000,
      "currency": "JPY",
      "invoice_date": "2025-11-01",
      "paid_at": "2025-11-02T10:00:00Z",
      "pdf_url": "https://api.documind.io/invoices/inv1/pdf"
    }
  ]
}
```

### 4.9 通知

#### 4.9.1 通知一覧取得

```http
GET /notifications
Authorization: Bearer {token}
```

**クエリパラメータ**
- `is_read`: true/false
- `type`: comment, mention, approval_request, etc.

**レスポンス (200 OK)**
```json
{
  "data": [
    {
      "id": "notif1",
      "type": "comment",
      "title": "新しいコメント",
      "content": "山田太郎さんがコメントしました",
      "link_type": "document",
      "link_id": "doc1",
      "is_read": false,
      "created_at": "2025-11-04T10:00:00Z"
    }
  ],
  "unread_count": 5
}
```

#### 4.9.2 通知を既読にする

```http
PATCH /notifications/{notification_id}/read
Authorization: Bearer {token}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "id": "notif1",
    "is_read": true,
    "read_at": "2025-11-04T11:00:00Z"
  }
}
```

### 4.10 分析・レポート

#### 4.10.1 ダッシュボード統計

```http
GET /analytics/dashboard
Authorization: Bearer {token}
```

**レスポンス (200 OK)**
```json
{
  "data": {
    "total_documents": 1000,
    "total_storage_bytes": 5368709120,
    "active_users": 50,
    "documents_this_month": 100,
    "ai_searches_this_month": 500,
    "popular_documents": [
      {
        "id": "doc1",
        "name": "契約書_2025年度.pdf",
        "view_count": 100
      }
    ]
  }
}
```

#### 4.10.2 使用量レポート

```http
GET /analytics/usage
Authorization: Bearer {token}
```

**クエリパラメータ**
- `start_date`: 開始日
- `end_date`: 終了日

**レスポンス (200 OK)**
```json
{
  "data": {
    "period": {
      "start": "2025-11-01",
      "end": "2025-11-30"
    },
    "storage": {
      "current_bytes": 5368709120,
      "limit_bytes": 107374182400,
      "usage_percent": 5
    },
    "ai_usage": {
      "searches": 500,
      "limit": -1,
      "summarizations": 100
    },
    "users": {
      "active": 50,
      "total": 60
    }
  }
}
```

---

## 5. Webhook

### 5.1 Webhook設定

```http
POST /webhooks
Authorization: Bearer {token}
Content-Type: application/json

{
  "url": "https://your-app.com/webhooks/documind",
  "events": [
    "document.created",
    "document.deleted",
    "workflow.approved"
  ],
  "secret": "your_webhook_secret"
}
```

### 5.2 Webhookイベント

**document.created**
```json
{
  "id": "evt_abc123",
  "type": "document.created",
  "created_at": "2025-11-04T10:00:00Z",
  "data": {
    "document": {
      "id": "doc1",
      "name": "契約書.pdf",
      "created_by": "user1"
    }
  }
}
```

**検証用ヘッダー**
```http
X-DocuMind-Signature: sha256=abc123...
X-DocuMind-Event: document.created
```

---

## 6. SDK・サンプルコード

### 6.1 JavaScript/TypeScript

```typescript
import { DocuMindClient } from '@documind/sdk';

const client = new DocuMindClient({
  apiKey: 'your_api_key'
});

// 文書一覧取得
const documents = await client.documents.list({
  page: 1,
  per_page: 20
});

// 文書アップロード
const document = await client.documents.upload({
  file: fileBuffer,
  name: 'contract.pdf',
  folderId: 'folder_id'
});

// AI検索
const results = await client.search.semantic({
  query: '昨年の売上報告書',
  limit: 10
});
```

### 6.2 Python

```python
from documind import DocuMindClient

client = DocuMindClient(api_key='your_api_key')

# 文書一覧取得
documents = client.documents.list(page=1, per_page=20)

# 文書アップロード
with open('contract.pdf', 'rb') as f:
    document = client.documents.upload(
        file=f,
        name='contract.pdf',
        folder_id='folder_id'
    )

# AI検索
results = client.search.semantic(
    query='昨年の売上報告書',
    limit=10
)
```

---

## 7. レート制限とクォータ

### 7.1 APIレート制限

| プラン | リクエスト/時間 | バースト |
|--------|---------------|---------|
| Free | 100 | 10 |
| Standard | 1,000 | 50 |
| Premium | 5,000 | 100 |
| Enterprise | 10,000 | 200 |

### 7.2 AI機能制限

| プラン | セマンティック検索/月 | 要約/月 |
|--------|---------------------|---------|
| Free | 10 | 5 |
| Standard | 500 | 100 |
| Premium | 無制限 | 無制限 |
| Enterprise | 無制限 | 無制限 |

---

## 8. セキュリティ

### 8.1 HTTPS必須
すべてのAPIリクエストはHTTPS経由で行う必要があります。

### 8.2 APIキーの保護
- APIキーは環境変数に保存
- クライアントサイドコードに含めない
- 定期的なローテーション

### 8.3 IPホワイトリスト（Enterprise）
Enterprise プランでは特定IPアドレスからのみアクセス可能に設定可能。

---

## 9. バージョニング

### 9.1 APIバージョン
- 現行バージョン: v1
- URLパスでバージョン指定: `/v1/documents`
- 非推奨機能は6ヶ月前に通知
- メジャーバージョンは最低1年間サポート

### 9.2 変更履歴
- 変更履歴はChangelog（https://docs.documind.io/changelog）で公開
- 破壊的変更は新バージョンでのみ実施

---

## 10. サポート・問い合わせ

### 10.1 APIステータス
https://status.documind.io

### 10.2 ドキュメント
https://docs.documind.io

### 10.3 サポート
- Email: support@documind.io
- チャット（Premium以上）

---

## 変更履歴

| バージョン | 日付 | 変更内容 | 作成者 |
|-----------|------|----------|--------|
| 1.0 | 2025-11-04 | 初版作成 | 開発チーム |
