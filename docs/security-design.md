# セキュリティ設計書

## 文書情報
- **プロジェクト名**: DocuMind - AI-Powered Document Management System
- **文書ID**: SEC-DOCUMIND-001
- **バージョン**: 1.0
- **最終更新日**: 2025-11-04
- **作成者**: セキュリティチーム
- **ステータス**: Draft
- **機密度**: 社外秘

---

## 1. セキュリティ概要

### 1.1 セキュリティ目標

| 目標 | 説明 |
|------|------|
| 機密性 (Confidentiality) | 認可されたユーザーのみがデータにアクセスできること |
| 完全性 (Integrity) | データが改ざんされないこと |
| 可用性 (Availability) | システムが必要な時に利用可能であること |
| 真正性 (Authenticity) | ユーザーやデータの正当性が検証できること |
| 否認防止 (Non-Repudiation) | 操作の証跡が記録され否認できないこと |

### 1.2 コンプライアンス要件

- **GDPR** (General Data Protection Regulation)
- **個人情報保護法** (日本)
- **電子帳簿保存法** (日本)
- **ISO/IEC 27001** (情報セキュリティマネジメント)
- **SOC 2 Type II** (将来)

### 1.3 セキュリティ原則

1. **最小権限の原則**: 必要最小限の権限のみ付与
2. **深層防御**: 多層的なセキュリティ対策
3. **ゼロトラスト**: すべてのアクセスを検証
4. **セキュアバイデザイン**: 設計段階からセキュリティを組み込む
5. **継続的改善**: 定期的なセキュリティ評価と改善

---

## 2. 脅威モデリング

### 2.1 STRIDE分析

| 脅威カテゴリ | 脅威例 | 対策 |
|------------|--------|------|
| **Spoofing (なりすまし)** | 不正ログイン、セッション乗っ取り | MFA、JWT検証、セッション管理 |
| **Tampering (改ざん)** | データベース改ざん、通信内容改ざん | TLS、署名検証、監査ログ |
| **Repudiation (否認)** | 操作の否認 | 監査ログ、デジタル署名 |
| **Information Disclosure (情報漏洩)** | データベース漏洩、通信傍受 | 暗号化、アクセス制御 |
| **Denial of Service (サービス拒否)** | DDoS攻撃 | WAF、レート制限、Auto Scaling |
| **Elevation of Privilege (権限昇格)** | 管理者権限の不正取得 | RBAC、最小権限、入力検証 |

### 2.2 資産と脅威

#### 2.2.1 重要資産

| 資産 | 機密度 | 脅威 | 影響 |
|------|-------|------|------|
| ユーザー認証情報 | 極めて高 | 漏洩、クラッキング | サービス全体の侵害 |
| 文書ファイル | 高 | 漏洩、改ざん、削除 | 顧客情報の流出 |
| 個人情報 | 高 | 漏洩 | GDPR違反、信用失墜 |
| APIキー | 高 | 漏洩 | 不正利用 |
| データベース | 高 | 漏洩、破壊 | サービス停止 |
| ソースコード | 中 | 漏洩 | 脆弱性の露呈 |

---

## 3. 認証 (Authentication)

### 3.1 認証方式

#### 3.1.1 パスワード認証

**要件**
- 最小文字数: 12文字
- 複雑性要件: 大文字、小文字、数字、記号のうち3種類以上
- パスワード履歴: 過去5回分は再使用不可
- パスワード有効期限: 90日（推奨、強制しない）
- アカウントロック: 5回失敗で15分ロック

**実装**
```typescript
import * as bcrypt from 'bcrypt';

const SALT_ROUNDS = 12;

async function hashPassword(password: string): Promise<string> {
  return bcrypt.hash(password, SALT_ROUNDS);
}

async function verifyPassword(password: string, hash: string): Promise<boolean> {
  return bcrypt.compare(password, hash);
}

// パスワード強度チェック
function validatePasswordStrength(password: string): boolean {
  const minLength = 12;
  const hasUpperCase = /[A-Z]/.test(password);
  const hasLowerCase = /[a-z]/.test(password);
  const hasNumber = /\d/.test(password);
  const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

  const complexityCount = [hasUpperCase, hasLowerCase, hasNumber, hasSpecialChar]
    .filter(Boolean).length;

  return password.length >= minLength && complexityCount >= 3;
}
```

#### 3.1.2 多要素認証 (MFA)

**対応方式**
- TOTP (Time-based One-Time Password): Google Authenticator、Authyなど
- SMS（推奨しない、フォールバックのみ）
- バックアップコード（10個生成、1回のみ使用可能）

**実装**
```typescript
import * as speakeasy from 'speakeasy';
import * as QRCode from 'qrcode';

// MFAシークレット生成
async function generateMFASecret(userEmail: string) {
  const secret = speakeasy.generateSecret({
    name: `DocuMind (${userEmail})`,
    length: 32,
  });

  const qrCodeUrl = await QRCode.toDataURL(secret.otpauth_url);

  return {
    secret: secret.base32,
    qrCode: qrCodeUrl,
  };
}

// TOTP検証
function verifyTOTP(token: string, secret: string): boolean {
  return speakeasy.totp.verify({
    secret,
    encoding: 'base32',
    token,
    window: 2, // ±2ステップの時間窓を許可
  });
}
```

#### 3.1.3 SSO (Single Sign-On)

**対応プロトコル**
- SAML 2.0
- OAuth 2.0 / OpenID Connect

**対応IdP**
- Okta
- Azure AD (Microsoft Entra ID)
- Google Workspace
- Auth0

**実装例 (SAML)**
```typescript
import { Strategy as SamlStrategy } from 'passport-saml';

passport.use(new SamlStrategy(
  {
    entryPoint: process.env.SAML_ENTRY_POINT,
    issuer: 'documind',
    cert: process.env.SAML_CERT,
    callbackUrl: 'https://app.documind.io/auth/saml/callback',
  },
  function(profile, done) {
    // ユーザー検索または作成
    const user = await findOrCreateUser(profile);
    return done(null, user);
  }
));
```

### 3.2 セッション管理

#### 3.2.1 JWT設計

**アクセストークン**
- 有効期限: 1時間
- 格納情報: user_id, organization_id, roles
- 署名アルゴリズム: RS256（非対称暗号化）

**リフレッシュトークン**
- 有効期限: 30日
- Rotation: リフレッシュごとに新しいトークン発行
- 保存場所: HTTPOnly Cookie（推奨）またはSecure Storage

**実装**
```typescript
import * as jwt from 'jsonwebtoken';

interface JWTPayload {
  sub: string; // user_id
  orgId: string;
  roles: string[];
  email: string;
}

function generateAccessToken(payload: JWTPayload): string {
  return jwt.sign(payload, PRIVATE_KEY, {
    algorithm: 'RS256',
    expiresIn: '1h',
    issuer: 'documind',
    audience: 'documind-api',
  });
}

function generateRefreshToken(userId: string): string {
  return jwt.sign({ sub: userId }, REFRESH_SECRET, {
    expiresIn: '30d',
  });
}

function verifyAccessToken(token: string): JWTPayload {
  return jwt.verify(token, PUBLIC_KEY, {
    algorithms: ['RS256'],
    issuer: 'documind',
    audience: 'documind-api',
  }) as JWTPayload;
}
```

#### 3.2.2 セッション無効化

- ログアウト時: Redisのブラックリストに追加
- パスワード変更時: すべてのセッション無効化
- 管理者による強制ログアウト

```typescript
// セッション無効化（Redisブラックリスト）
async function revokeToken(token: string) {
  const decoded = jwt.decode(token) as { exp: number };
  const ttl = decoded.exp - Math.floor(Date.now() / 1000);

  if (ttl > 0) {
    await redis.setex(`blacklist:${token}`, ttl, '1');
  }
}

// トークン検証時のチェック
async function isTokenBlacklisted(token: string): Promise<boolean> {
  const result = await redis.get(`blacklist:${token}`);
  return result !== null;
}
```

---

## 4. 認可 (Authorization)

### 4.1 RBAC (Role-Based Access Control)

#### 4.1.1 ロール定義

| ロール | 権限 |
|--------|------|
| **SuperAdmin** | システム全体の管理 |
| **OrgAdmin** | 組織内の全リソース管理 |
| **Manager** | 部署/チーム管理、承認権限 |
| **Editor** | 文書の作成・編集・削除 |
| **Viewer** | 文書の閲覧のみ |
| **Guest** | 共有された文書のみ閲覧 |

#### 4.1.2 権限マトリクス

| リソース | SuperAdmin | OrgAdmin | Manager | Editor | Viewer | Guest |
|---------|-----------|----------|---------|--------|--------|-------|
| 文書作成 | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ |
| 文書編集（自分） | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ |
| 文書編集（他人） | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |
| 文書削除 | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |
| 文書閲覧 | ✓ | ✓ | ✓ | ✓ | ✓ | ✓* |
| ユーザー管理 | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| 組織設定 | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| 承認ワークフロー | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ |

*共有された文書のみ

#### 4.1.3 実装

```typescript
// 権限チェック
@Injectable()
export class AuthorizationService {
  canAccessDocument(user: User, document: Document, action: Action): boolean {
    // SuperAdminは全権限
    if (user.roles.includes('SuperAdmin')) return true;

    // 組織が異なる場合は拒否
    if (user.organizationId !== document.organizationId) return false;

    // OrgAdminは組織内全権限
    if (user.roles.includes('OrgAdmin')) return true;

    // リソースレベルの権限チェック
    const permission = this.getPermission(user.id, document.id);

    if (!permission) {
      // 明示的な権限がない場合は、作成者のみ許可
      if (action === 'view') {
        return document.createdBy === user.id;
      }
      return false;
    }

    // 権限レベルによるアクセス制御
    switch (permission.level) {
      case 'admin':
        return true;
      case 'edit':
        return ['view', 'edit', 'comment'].includes(action);
      case 'comment':
        return ['view', 'comment'].includes(action);
      case 'view':
        return action === 'view';
      default:
        return false;
    }
  }
}
```

### 4.2 ABAC (Attribute-Based Access Control)

より細かいアクセス制御のためにABACを併用：

```typescript
interface AccessPolicy {
  subject: {
    roles?: string[];
    department?: string;
    location?: string;
  };
  resource: {
    type: string;
    classification?: string;
    department?: string;
  };
  environment?: {
    timeRange?: [string, string];
    ipWhitelist?: string[];
  };
  action: string;
}

function evaluatePolicy(
  user: User,
  resource: Resource,
  action: string,
  policy: AccessPolicy
): boolean {
  // ロールチェック
  if (policy.subject.roles) {
    if (!user.roles.some(r => policy.subject.roles.includes(r))) {
      return false;
    }
  }

  // 部署チェック
  if (policy.subject.department) {
    if (user.department !== policy.subject.department) {
      return false;
    }
  }

  // リソースの機密度チェック
  if (policy.resource.classification === 'confidential') {
    if (!user.roles.includes('Manager')) {
      return false;
    }
  }

  // 時間帯チェック
  if (policy.environment?.timeRange) {
    const now = new Date();
    const [start, end] = policy.environment.timeRange;
    // 時間帯の範囲内かチェック
  }

  return true;
}
```

---

## 5. データ保護

### 5.1 暗号化

#### 5.1.1 転送時の暗号化 (Encryption in Transit)

**TLS 1.3設定**
```nginx
# Nginx設定例
ssl_protocols TLSv1.3 TLSv1.2;
ssl_ciphers 'ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
ssl_prefer_server_ciphers on;
ssl_session_cache shared:SSL:10m;
ssl_session_timeout 10m;
ssl_stapling on;
ssl_stapling_verify on;

# HSTS
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
```

**内部通信の暗号化**
- サービス間通信: mTLS (Mutual TLS)
- データベース接続: SSL/TLS

#### 5.1.2 保存時の暗号化 (Encryption at Rest)

**データベース**
- PostgreSQL: Transparent Data Encryption (TDE)
- MongoDB: Encryption at Rest
- Redis: RDB/AOF暗号化

**オブジェクトストレージ (S3)**
- SSE-S3 (Server-Side Encryption with S3-Managed Keys)
- SSE-KMS (Server-Side Encryption with AWS KMS)

**アプリケーションレベル暗号化**
```typescript
import * as crypto from 'crypto';

const ALGORITHM = 'aes-256-gcm';
const KEY = Buffer.from(process.env.ENCRYPTION_KEY, 'hex'); // 32 bytes

function encrypt(plaintext: string): string {
  const iv = crypto.randomBytes(16);
  const cipher = crypto.createCipheriv(ALGORITHM, KEY, iv);

  let encrypted = cipher.update(plaintext, 'utf8', 'hex');
  encrypted += cipher.final('hex');

  const authTag = cipher.getAuthTag();

  return `${iv.toString('hex')}:${authTag.toString('hex')}:${encrypted}`;
}

function decrypt(ciphertext: string): string {
  const [ivHex, authTagHex, encrypted] = ciphertext.split(':');

  const iv = Buffer.from(ivHex, 'hex');
  const authTag = Buffer.from(authTagHex, 'hex');

  const decipher = crypto.createDecipheriv(ALGORITHM, KEY, iv);
  decipher.setAuthTag(authTag);

  let decrypted = decipher.update(encrypted, 'hex', 'utf8');
  decrypted += decipher.final('utf8');

  return decrypted;
}

// 使用例: 機密フィールドの暗号化
const encryptedSSN = encrypt(user.ssn);
await prisma.user.update({
  where: { id: user.id },
  data: { ssnEncrypted: encryptedSSN },
});
```

### 5.2 個人情報の取り扱い

#### 5.2.1 データ分類

| 分類 | 例 | 保管要件 |
|------|-----|---------|
| **公開** | 公開文書 | 通常保管 |
| **内部** | 社内文書 | アクセス制御 |
| **機密** | 契約書、個人情報 | 暗号化 + 厳密なアクセス制御 |
| **極秘** | クレジットカード情報 | 暗号化 + トークン化 |

#### 5.2.2 個人情報のマスキング

```typescript
// ログ出力時のマスキング
function maskEmail(email: string): string {
  const [local, domain] = email.split('@');
  const maskedLocal = local.substring(0, 2) + '***';
  return `${maskedLocal}@${domain}`;
}

function maskCreditCard(cardNumber: string): string {
  return cardNumber.replace(/\d(?=\d{4})/g, '*');
}

// ロギング時に自動マスキング
logger.info('User login', {
  email: maskEmail(user.email),
  ipAddress: user.ipAddress,
});
```

#### 5.2.3 データ保持・削除

**GDPR対応**
- データポータビリティ: エクスポート機能
- 忘れられる権利: 完全削除機能
- 保持期間: プランごとに設定

```typescript
// データ削除（論理削除 → 物理削除）
@Cron('0 0 * * *') // 毎日実行
async function permanentlyDeleteOldData() {
  const thirtyDaysAgo = new Date();
  thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);

  // 論理削除から30日経過したデータを物理削除
  await prisma.document.deleteMany({
    where: {
      deletedAt: {
        lt: thirtyDaysAgo,
      },
    },
  });
}

// ユーザーデータのエクスポート（GDPR）
async function exportUserData(userId: string): Promise<Buffer> {
  const user = await prisma.user.findUnique({
    where: { id: userId },
    include: {
      documents: true,
      comments: true,
      auditLogs: true,
    },
  });

  return createZip(user); // JSON + ファイルをZIP化
}
```

---

## 6. アプリケーションセキュリティ

### 6.1 入力検証

#### 6.1.1 バリデーション

```typescript
import { IsString, IsEmail, MaxLength, MinLength, Matches } from 'class-validator';

export class CreateUserDto {
  @IsEmail()
  email: string;

  @IsString()
  @MinLength(12)
  @MaxLength(128)
  @Matches(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/)
  password: string;

  @IsString()
  @MaxLength(100)
  @Matches(/^[a-zA-Z\s]+$/)
  firstName: string;
}
```

#### 6.1.2 サニタイゼーション

```typescript
import DOMPurify from 'isomorphic-dompurify';

function sanitizeHTML(input: string): string {
  return DOMPurify.sanitize(input, {
    ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'p', 'br'],
    ALLOWED_ATTR: [],
  });
}

function sanitizeFilename(filename: string): string {
  // ディレクトリトラバーサル対策
  return filename.replace(/[^a-zA-Z0-9._-]/g, '_');
}
```

### 6.2 SQLインジェクション対策

**ORMの使用（Prisma）**
```typescript
// ✅ Good: パラメータ化クエリ
const users = await prisma.user.findMany({
  where: {
    email: {
      contains: searchTerm, // 自動的にエスケープ
    },
  },
});

// ❌ Bad: 生SQL
const users = await prisma.$queryRawUnsafe(
  `SELECT * FROM users WHERE email LIKE '%${searchTerm}%'`
);

// ✅ Good: パラメータ化された生SQL
const users = await prisma.$queryRaw`
  SELECT * FROM users WHERE email LIKE ${'%' + searchTerm + '%'}
`;
```

### 6.3 XSS (Cross-Site Scripting) 対策

#### 6.3.1 Content Security Policy (CSP)

```typescript
// Helmet設定
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      scriptSrc: ["'self'", "'unsafe-inline'", "https://cdn.documind.io"],
      styleSrc: ["'self'", "'unsafe-inline'"],
      imgSrc: ["'self'", "data:", "https:"],
      connectSrc: ["'self'", "https://api.documind.io"],
      fontSrc: ["'self'", "https://fonts.gstatic.com"],
      objectSrc: ["'none'"],
      upgradeInsecureRequests: [],
    },
  },
}));
```

#### 6.3.2 出力エスケープ

```tsx
// React自動エスケープ
<div>{userInput}</div> // 自動的にエスケープ

// dangerouslySetInnerHTMLは避ける
// 必要な場合はDOMPurifyでサニタイズ
<div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(html) }} />
```

### 6.4 CSRF (Cross-Site Request Forgery) 対策

```typescript
import * as csurf from 'csurf';

// CSRFトークン検証
app.use(csurf({
  cookie: {
    httpOnly: true,
    secure: true,
    sameSite: 'strict',
  },
}));

// Next.jsでのトークン送信
export async function getServerSideProps(context) {
  return {
    props: {
      csrfToken: context.req.csrfToken(),
    },
  };
}
```

### 6.5 ファイルアップロードセキュリティ

```typescript
import * as fileType from 'file-type';
import * as crypto from 'crypto';

async function validateUpload(file: Express.Multer.File) {
  // ファイルサイズチェック
  const maxSize = 100 * 1024 * 1024; // 100MB
  if (file.size > maxSize) {
    throw new Error('File too large');
  }

  // MIMEタイプ検証（Content-Typeだけでなく実際の内容を確認）
  const type = await fileType.fromBuffer(file.buffer);
  const allowedTypes = ['application/pdf', 'image/png', 'image/jpeg'];

  if (!type || !allowedTypes.includes(type.mime)) {
    throw new Error('Invalid file type');
  }

  // ファイル名サニタイズ
  const sanitizedName = sanitizeFilename(file.originalname);

  // ウイルススキャン（ClamAV等）
  await scanForViruses(file.buffer);

  // ランダムなファイル名生成
  const randomName = crypto.randomBytes(16).toString('hex');
  const ext = type.ext;
  const storageName = `${randomName}.${ext}`;

  return storageName;
}
```

---

## 7. インフラセキュリティ

### 7.1 ネットワークセキュリティ

#### 7.1.1 ネットワーク分離

```
Internet
    │
    ▼
┌─────────┐
│   WAF   │
└────┬────┘
     │
     ▼
┌─────────────────────┐
│  Public Subnet      │
│  (ALB, API Gateway) │
└────┬────────────────┘
     │
     ▼
┌─────────────────────┐
│  Private Subnet     │
│  (Application)      │
│  10.0.2.0/24        │
└────┬────────────────┘
     │
     ▼
┌─────────────────────┐
│  Data Subnet        │
│  (Databases)        │
│  10.0.3.0/24        │
└─────────────────────┘
```

#### 7.1.2 セキュリティグループ設定

```hcl
# Terraform設定例

# ALBセキュリティグループ
resource "aws_security_group" "alb" {
  name = "alb-sg"

  ingress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# アプリケーションセキュリティグループ
resource "aws_security_group" "app" {
  name = "app-sg"

  ingress {
    from_port       = 3000
    to_port         = 3000
    protocol        = "tcp"
    security_groups = [aws_security_group.alb.id]
  }
}

# データベースセキュリティグループ
resource "aws_security_group" "db" {
  name = "db-sg"

  ingress {
    from_port       = 5432
    to_port         = 5432
    protocol        = "tcp"
    security_groups = [aws_security_group.app.id]
  }
}
```

### 7.2 コンテナセキュリティ

#### 7.2.1 イメージスキャン

```yaml
# GitHub Actions
- name: Build Docker image
  run: docker build -t documind/api:${{ github.sha }} .

- name: Scan image for vulnerabilities
  uses: aquasecurity/trivy-action@master
  with:
    image-ref: documind/api:${{ github.sha }}
    format: 'sarif'
    output: 'trivy-results.sarif'
    severity: 'CRITICAL,HIGH'
```

#### 7.2.2 最小権限実行

```dockerfile
# Dockerfileのセキュリティベストプラクティス

FROM node:20-alpine AS base

# セキュリティアップデート
RUN apk update && apk upgrade

# 非rootユーザー作成
RUN addgroup -g 1001 -S nodejs
RUN adduser -S nextjs -u 1001

# ...ビルド...

# 非rootユーザーで実行
USER nextjs

# 読み取り専用ルートファイルシステム
# docker run --read-only ...
```

### 7.3 シークレット管理

```typescript
// AWS Secrets Manager使用例
import { SecretsManager } from 'aws-sdk';

const secretsManager = new SecretsManager({ region: 'ap-northeast-1' });

async function getSecret(secretName: string): Promise<string> {
  const response = await secretsManager.getSecretValue({
    SecretId: secretName,
  }).promise();

  return response.SecretString;
}

// 使用例
const dbPassword = await getSecret('prod/db/password');
```

**環境変数での機密情報は避ける**
- ✅ Good: AWS Secrets Manager, HashiCorp Vault
- ❌ Bad: .env ファイル、環境変数（本番環境）

---

## 8. 監査とコンプライアンス

### 8.1 監査ログ

#### 8.1.1 記録対象

- 認証イベント（ログイン、ログアウト、失敗）
- 文書操作（作成、閲覧、編集、削除、ダウンロード）
- 権限変更
- ユーザー管理操作
- 設定変更
- API呼び出し（重要なもの）

#### 8.1.2 ログフォーマット

```typescript
interface AuditLog {
  id: string;
  timestamp: Date;
  organizationId: string;
  userId: string | null;
  action: string; // 'document.view', 'user.create', etc.
  resourceType: string;
  resourceId: string;
  details: Record<string, unknown>;
  ipAddress: string;
  userAgent: string;
  result: 'success' | 'failure';
  errorMessage?: string;
}

// 監査ログ記録
async function logAudit(log: AuditLog) {
  await prisma.auditLog.create({ data: log });

  // 重要なイベントはリアルタイムアラート
  if (isCriticalEvent(log.action)) {
    await sendAlert(log);
  }
}
```

#### 8.1.3 ログの改ざん防止

```typescript
import * as crypto from 'crypto';

// ログチェーン（Blockchain風）
interface AuditLogChain {
  id: string;
  previousHash: string;
  timestamp: Date;
  data: AuditLog;
  hash: string;
}

function calculateHash(log: AuditLogChain): string {
  const data = JSON.stringify({
    previousHash: log.previousHash,
    timestamp: log.timestamp,
    data: log.data,
  });

  return crypto.createHash('sha256').update(data).digest('hex');
}

// ログ検証
function verifyLogChain(logs: AuditLogChain[]): boolean {
  for (let i = 1; i < logs.length; i++) {
    const currentLog = logs[i];
    const previousLog = logs[i - 1];

    // ハッシュ検証
    if (currentLog.hash !== calculateHash(currentLog)) {
      return false;
    }

    // チェーン検証
    if (currentLog.previousHash !== previousLog.hash) {
      return false;
    }
  }

  return true;
}
```

### 8.2 コンプライアンス

#### 8.2.1 GDPR対応

**データ主体の権利**
1. **アクセス権**: データのコピーを取得
2. **修正権**: 不正確なデータの修正
3. **削除権（忘れられる権利）**: データの完全削除
4. **データポータビリティ権**: データのエクスポート
5. **処理の制限権**: 処理の一時停止

**実装例**
```typescript
// データエクスポート（GDPR Article 20）
@Get('export-data')
@UseGuards(JwtAuthGuard)
async exportUserData(@CurrentUser() user: User) {
  const userData = {
    personal_info: await this.getUserInfo(user.id),
    documents: await this.getUserDocuments(user.id),
    activity_logs: await this.getUserLogs(user.id),
  };

  const zip = await createZip(userData);

  return {
    download_url: await this.generateSignedUrl(zip),
    expires_at: addHours(new Date(), 24),
  };
}

// データ削除（GDPR Article 17）
@Delete('delete-account')
@UseGuards(JwtAuthGuard)
async deleteAccount(@CurrentUser() user: User) {
  // 削除予約（30日の猶予期間）
  await prisma.user.update({
    where: { id: user.id },
    data: {
      deletedAt: new Date(),
      deleteScheduledAt: addDays(new Date(), 30),
    },
  });

  // 通知送信
  await this.sendDeletionConfirmationEmail(user);
}
```

#### 8.2.2 電子帳簿保存法対応（日本）

**要件**
- タイムスタンプ
- 検索機能
- 真実性の確保
- 可視性の確保

```typescript
// タイムスタンプ（RFC 3161準拠のTSA使用）
async function applyTimestamp(documentId: string, hash: string) {
  // タイムスタンプ局にリクエスト
  const timestamp = await tsaClient.createTimestamp(hash);

  await prisma.document.update({
    where: { id: documentId },
    data: {
      timestamp: timestamp.token,
      timestampedAt: timestamp.generatedAt,
    },
  });
}
```

---

## 9. インシデント対応

### 9.1 セキュリティインシデント対応計画

#### 9.1.1 インシデントレベル

| レベル | 説明 | 対応時間 |
|-------|------|---------|
| P0 - Critical | データ漏洩、サービス全停止 | 即時 |
| P1 - High | 一部機能停止、セキュリティ脆弱性 | 1時間以内 |
| P2 - Medium | パフォーマンス劣化 | 4時間以内 |
| P3 - Low | 軽微な問題 | 24時間以内 |

#### 9.1.2 対応フロー

```
1. 検知 (Detection)
   ↓
2. トリアージ (Triage)
   ↓
3. 封じ込め (Containment)
   ↓
4. 根絶 (Eradication)
   ↓
5. 復旧 (Recovery)
   ↓
6. 事後分析 (Post-Incident Analysis)
```

#### 9.1.3 連絡体制

```
┌──────────────┐
│ 自動アラート │
│  (Datadog)   │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│ オンコール   │
│  エンジニア  │
└──────┬───────┘
       │
       ├─ P0/P1 ──► CISO / CTO
       ├─ P2 ──────► Team Lead
       └─ P3 ──────► 通常対応
```

### 9.2 データ漏洩時の対応

1. **即座の封じ込め**
   - 影響範囲の特定
   - アクセス遮断
   - 認証情報のリセット

2. **影響評価**
   - 漏洩したデータの特定
   - 影響を受けるユーザーの特定

3. **通知**
   - 影響を受けるユーザーへの通知（72時間以内、GDPR）
   - 監督当局への報告
   - 公表（必要に応じて）

4. **再発防止**
   - 根本原因分析
   - セキュリティ強化
   - 再発防止策の実施

---

## 10. セキュリティテスト

### 10.1 脆弱性診断

#### 10.1.1 定期診断

- **頻度**: 四半期ごと
- **範囲**: Webアプリケーション、API、インフラ
- **手法**: 自動スキャン + 手動診断

#### 10.1.2 ペネトレーションテスト

- **頻度**: 年1回
- **範囲**: システム全体
- **実施者**: 第三者セキュリティ企業

### 10.2 セキュリティツール

| ツール | 用途 |
|--------|------|
| Snyk | 依存関係の脆弱性スキャン |
| Trivy | コンテナイメージスキャン |
| OWASP ZAP | 動的アプリケーションスキャン |
| SonarQube | 静的コード解析 |
| Burp Suite | ペネトレーションテスト |

### 10.3 バグバウンティプログラム

- プラットフォーム: HackerOne / Bugcrowd
- 対象範囲: 本番環境
- 報奨金: 脆弱性の深刻度に応じて

---

## 11. セキュリティ教育

### 11.1 従業員教育

- **新入社員研修**: セキュリティ基礎
- **定期トレーニング**: 年2回
- **フィッシング訓練**: 四半期ごと

### 11.2 セキュアコーディングガイドライン

- OWASP Top 10の理解
- セキュアコーディング規約の遵守
- コードレビューでのセキュリティチェック

---

## 12. セキュリティチェックリスト

### 12.1 開発時

- [ ] 入力バリデーション実装
- [ ] 出力エスケープ実装
- [ ] 認証・認可の実装
- [ ] エラーハンドリング（情報漏洩防止）
- [ ] ログ記録
- [ ] ユニットテスト・セキュリティテスト

### 12.2 デプロイ前

- [ ] 脆弱性スキャン実施
- [ ] 依存関係の最新化
- [ ] シークレットの環境変数化
- [ ] HTTPS設定
- [ ] セキュリティヘッダー設定
- [ ] レート制限設定

### 12.3 本番運用

- [ ] 監視・アラート設定
- [ ] バックアップ設定
- [ ] 監査ログ記録
- [ ] インシデント対応計画
- [ ] 定期的なセキュリティレビュー

---

## 変更履歴

| バージョン | 日付 | 変更内容 | 作成者 |
|-----------|------|----------|--------|
| 1.0 | 2025-11-04 | 初版作成 | セキュリティチーム |
