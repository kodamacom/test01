# 技術仕様書

## 文書情報
- **プロジェクト名**: DocuMind - AI-Powered Document Management System
- **文書ID**: TECH-DOCUMIND-001
- **バージョン**: 1.0
- **最終更新日**: 2025-11-04
- **作成者**: 開発チーム
- **ステータス**: Draft

---

## 1. 技術スタック

### 1.1 フロントエンド

#### 1.1.1 コア技術
| 技術 | バージョン | 用途 |
|------|----------|------|
| React | 18.2+ | UIライブラリ |
| Next.js | 14+ | フルスタックフレームワーク |
| TypeScript | 5.0+ | 型安全性 |
| Node.js | 20 LTS | ランタイム |

#### 1.1.2 状態管理・データフェッチ
| 技術 | 用途 |
|------|------|
| Zustand | グローバル状態管理 |
| React Query (TanStack Query) | サーバー状態管理、キャッシング |
| Jotai | アトミックな状態管理 |

#### 1.1.3 UIコンポーネント・スタイリング
| 技術 | 用途 |
|------|------|
| Tailwind CSS | ユーティリティファーストCSS |
| shadcn/ui | UIコンポーネントライブラリ |
| Radix UI | ヘッドレスUIコンポーネント |
| Framer Motion | アニメーション |
| Lucide React | アイコン |

#### 1.1.4 フォーム・バリデーション
| 技術 | 用途 |
|------|------|
| React Hook Form | フォーム管理 |
| Zod | スキーマバリデーション |

#### 1.1.5 その他ライブラリ
| 技術 | 用途 |
|------|------|
| react-pdf | PDFプレビュー |
| react-dropzone | ファイルアップロード |
| date-fns | 日付処理 |
| i18next | 国際化 |
| recharts | チャート・グラフ |

### 1.2 バックエンド

#### 1.2.1 言語・フレームワーク

**Node.js (TypeScript) - 主要サービス**
| 技術 | バージョン | 用途 |
|------|----------|------|
| NestJS | 10+ | フレームワーク |
| TypeScript | 5.0+ | 型安全性 |
| Node.js | 20 LTS | ランタイム |

**Python - AIサービス**
| 技術 | バージョン | 用途 |
|------|----------|------|
| FastAPI | 0.104+ | APIフレームワーク |
| Python | 3.11+ | ランタイム |

**Go - ストレージサービス（オプション）**
| 技術 | バージョン | 用途 |
|------|----------|------|
| Go | 1.21+ | 高パフォーマンス処理 |
| Gin / Fiber | - | Webフレームワーク |

#### 1.2.2 ORM・データベースクライアント
| 技術 | 用途 |
|------|------|
| Prisma | PostgreSQL ORM |
| Mongoose | MongoDB ODM |
| ioredis | Redisクライアント |

#### 1.2.3 認証・認可
| 技術 | 用途 |
|------|------|
| Passport.js | 認証戦略 |
| jsonwebtoken | JWT生成・検証 |
| bcrypt | パスワードハッシュ |
| @nestjs/jwt | NestJS JWT統合 |

#### 1.2.4 バリデーション
| 技術 | 用途 |
|------|------|
| class-validator | DTOバリデーション |
| class-transformer | DTOトランスフォーメーション |

#### 1.2.5 ファイル処理
| 技術 | 用途 |
|------|------|
| multer | ファイルアップロード |
| sharp | 画像処理 |
| pdf-parse | PDF解析 |
| mammoth | Word解析 |
| xlsx | Excel解析 |

#### 1.2.6 AI/ML
| 技術 | 用途 |
|------|------|
| OpenAI SDK | GPT-4, Embeddings |
| LangChain | LLMオーケストレーション |
| @pinecone-database/pinecone | ベクトルDB |
| qdrant-client | ベクトルDB（代替） |
| tiktoken | トークンカウント |

#### 1.2.7 バックグラウンドジョブ
| 技術 | 用途 |
|------|------|
| Bull | ジョブキュー（Redis-based） |
| node-cron | スケジュールタスク |

#### 1.2.8 その他
| 技術 | 用途 |
|------|------|
| nodemailer | メール送信 |
| winston | ロギング |
| helmet | セキュリティヘッダー |
| cors | CORS設定 |
| rate-limiter-flexible | レート制限 |

### 1.3 データベース・ストレージ

| 技術 | バージョン | 用途 |
|------|----------|------|
| PostgreSQL | 15+ | リレーショナルDB |
| MongoDB | 6+ | ドキュメントDB |
| Redis | 7+ | キャッシュ、セッション |
| Pinecone / Qdrant | - | ベクトルDB |
| Elasticsearch | 8+ | 全文検索 |
| AWS S3 / MinIO | - | オブジェクトストレージ |

### 1.4 インフラ・DevOps

#### 1.4.1 コンテナ・オーケストレーション
| 技術 | 用途 |
|------|------|
| Docker | コンテナ化 |
| Kubernetes | コンテナオーケストレーション |
| Helm | Kubernetesパッケージ管理 |

#### 1.4.2 クラウドプロバイダー
| 技術 | 用途 |
|------|------|
| AWS | メインクラウド（推奨） |
| Google Cloud | 代替クラウド |
| Azure | 代替クラウド |

#### 1.4.3 CI/CD
| 技術 | 用途 |
|------|------|
| GitHub Actions | CI/CDパイプライン |
| Docker Compose | ローカル開発環境 |

#### 1.4.4 IaC（Infrastructure as Code）
| 技術 | 用途 |
|------|------|
| Terraform | インフラ管理 |
| AWS CDK | AWS リソース管理（代替） |

#### 1.4.5 監視・ロギング
| 技術 | 用途 |
|------|------|
| Datadog / New Relic | APM |
| Prometheus + Grafana | メトリクス監視 |
| ELK Stack | ログ集約 |
| Sentry | エラートラッキング |
| Jaeger / AWS X-Ray | 分散トレーシング |

#### 1.4.6 セキュリティ
| 技術 | 用途 |
|------|------|
| AWS WAF / Cloudflare WAF | WAF |
| Snyk / Trivy | 脆弱性スキャン |
| AWS Secrets Manager | シークレット管理 |
| Let's Encrypt | SSL/TLS証明書 |

### 1.5 外部サービス

| サービス | 用途 |
|---------|------|
| OpenAI API | LLM、Embeddings |
| Anthropic Claude API | LLM（代替） |
| Stripe | 決済処理 |
| SendGrid / AWS SES | メール送信 |
| Twilio | SMS通知（オプション） |
| Auth0 / Okta | SSO（エンタープライズ） |

---

## 2. 開発環境

### 2.1 推奨開発環境

| ツール | バージョン |
|--------|----------|
| Node.js | 20 LTS |
| Python | 3.11+ |
| Docker | 24+ |
| Docker Compose | 2.20+ |
| Git | 2.40+ |

### 2.2 IDE・エディタ

推奨: **Visual Studio Code**

**推奨拡張機能**
- ESLint
- Prettier
- TypeScript and JavaScript Language Features
- Prisma
- Tailwind CSS IntelliSense
- Docker
- GitLens

### 2.3 ローカル開発セットアップ

```bash
# リポジトリクローン
git clone https://github.com/your-org/documind.git
cd documind

# 依存関係インストール
npm install

# 環境変数設定
cp .env.example .env

# Dockerコンテナ起動（DB等）
docker-compose up -d

# データベースマイグレーション
npm run db:migrate

# 開発サーバー起動
npm run dev
```

### 2.4 環境変数

**.env.example**
```bash
# Database
DATABASE_URL=postgresql://user:password@localhost:5432/documind
MONGODB_URL=mongodb://localhost:27017/documind
REDIS_URL=redis://localhost:6379

# AI Services
OPENAI_API_KEY=sk-...
PINECONE_API_KEY=...
PINECONE_ENVIRONMENT=us-west1-gcp

# Storage
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_S3_BUCKET=documind-storage
AWS_REGION=ap-northeast-1

# Authentication
JWT_SECRET=your-secret-key
JWT_EXPIRATION=3600

# External Services
STRIPE_SECRET_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
SENDGRID_API_KEY=SG....

# App Config
NODE_ENV=development
PORT=3000
API_BASE_URL=http://localhost:3000
FRONTEND_URL=http://localhost:3001
```

---

## 3. プロジェクト構成

### 3.1 モノレポ構成（推奨）

```
documind/
├── apps/
│   ├── web/                    # Next.js frontend
│   ├── api/                    # NestJS backend
│   ├── ai-service/            # FastAPI AI service
│   └── mobile/                # React Native (future)
├── packages/
│   ├── ui/                    # Shared UI components
│   ├── types/                 # Shared TypeScript types
│   ├── utils/                 # Shared utilities
│   └── config/                # Shared configs
├── infrastructure/
│   ├── terraform/             # Terraform configs
│   ├── kubernetes/            # K8s manifests
│   └── docker/                # Dockerfiles
├── docs/                      # Documentation
├── scripts/                   # Build/deploy scripts
├── .github/
│   └── workflows/             # GitHub Actions
├── docker-compose.yml
├── package.json
├── turbo.json                 # Turborepo config
└── README.md
```

### 3.2 フロントエンド（Next.js）構成

```
apps/web/
├── src/
│   ├── app/                   # App Router
│   │   ├── (auth)/           # Auth group
│   │   │   ├── login/
│   │   │   └── signup/
│   │   ├── (dashboard)/      # Dashboard group
│   │   │   ├── documents/
│   │   │   ├── search/
│   │   │   └── settings/
│   │   ├── api/              # API routes
│   │   ├── layout.tsx
│   │   └── page.tsx
│   ├── components/
│   │   ├── ui/               # shadcn/ui components
│   │   ├── features/         # Feature-specific components
│   │   └── layouts/          # Layout components
│   ├── lib/
│   │   ├── api/              # API client
│   │   ├── hooks/            # Custom hooks
│   │   ├── utils/            # Utility functions
│   │   └── store/            # State management
│   ├── types/                # TypeScript types
│   ├── styles/               # Global styles
│   └── config/               # App config
├── public/                   # Static assets
├── next.config.js
├── tailwind.config.js
├── tsconfig.json
└── package.json
```

### 3.3 バックエンド（NestJS）構成

```
apps/api/
├── src/
│   ├── modules/
│   │   ├── auth/
│   │   │   ├── auth.controller.ts
│   │   │   ├── auth.service.ts
│   │   │   ├── auth.module.ts
│   │   │   ├── dto/
│   │   │   ├── strategies/
│   │   │   └── guards/
│   │   ├── documents/
│   │   │   ├── documents.controller.ts
│   │   │   ├── documents.service.ts
│   │   │   ├── documents.module.ts
│   │   │   ├── dto/
│   │   │   └── entities/
│   │   ├── users/
│   │   ├── organizations/
│   │   ├── search/
│   │   ├── ai/
│   │   ├── storage/
│   │   ├── workflows/
│   │   └── billing/
│   ├── common/
│   │   ├── decorators/
│   │   ├── filters/
│   │   ├── guards/
│   │   ├── interceptors/
│   │   ├── pipes/
│   │   └── utils/
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeds/
│   ├── main.ts
│   └── app.module.ts
├── test/
│   ├── unit/
│   └── e2e/
├── prisma/
│   └── schema.prisma
├── nest-cli.json
├── tsconfig.json
└── package.json
```

### 3.4 AIサービス（FastAPI）構成

```
apps/ai-service/
├── app/
│   ├── main.py
│   ├── api/
│   │   ├── v1/
│   │   │   ├── endpoints/
│   │   │   │   ├── embeddings.py
│   │   │   │   ├── search.py
│   │   │   │   ├── classification.py
│   │   │   │   └── summarization.py
│   │   │   └── api.py
│   │   └── deps.py
│   ├── core/
│   │   ├── config.py
│   │   ├── security.py
│   │   └── logging.py
│   ├── models/
│   ├── schemas/
│   ├── services/
│   │   ├── openai_service.py
│   │   ├── embedding_service.py
│   │   └── vector_db_service.py
│   └── utils/
├── tests/
├── requirements.txt
├── Dockerfile
└── README.md
```

---

## 4. コーディング規約

### 4.1 TypeScript/JavaScript

#### 4.1.1 命名規則
```typescript
// クラス: PascalCase
class DocumentService {}

// インターフェース: PascalCase (接頭辞なし)
interface User {}

// 型エイリアス: PascalCase
type DocumentStatus = 'active' | 'archived';

// 関数: camelCase
function getDocument() {}

// 定数: UPPER_SNAKE_CASE
const MAX_FILE_SIZE = 100 * 1024 * 1024;

// 変数: camelCase
let documentId = 'abc123';

// コンポーネント: PascalCase
const DocumentCard = () => {}
```

#### 4.1.2 ファイル命名
```
// コンポーネント: PascalCase.tsx
DocumentCard.tsx

// ユーティリティ: camelCase.ts
dateUtils.ts

// 定数: camelCase.ts or UPPER_SNAKE_CASE.ts
constants.ts

// 型定義: camelCase.types.ts
user.types.ts
```

#### 4.1.3 コードスタイル

**ESLint + Prettier使用**

```json
// .eslintrc.json
{
  "extends": [
    "next/core-web-vitals",
    "plugin:@typescript-eslint/recommended",
    "prettier"
  ],
  "rules": {
    "no-console": "warn",
    "@typescript-eslint/explicit-module-boundary-types": "off",
    "@typescript-eslint/no-unused-vars": ["error", { "argsIgnorePattern": "^_" }]
  }
}
```

```json
// .prettierrc
{
  "semi": true,
  "trailingComma": "es5",
  "singleQuote": true,
  "printWidth": 100,
  "tabWidth": 2,
  "arrowParens": "always"
}
```

#### 4.1.4 型安全性
```typescript
// ✅ Good: 明示的な型定義
interface DocumentUploadParams {
  file: File;
  folderId: string;
  metadata?: Record<string, unknown>;
}

function uploadDocument(params: DocumentUploadParams): Promise<Document> {
  // ...
}

// ❌ Bad: any使用
function uploadDocument(params: any): any {
  // ...
}

// ✅ Good: 型ガード
function isDocument(obj: unknown): obj is Document {
  return (
    typeof obj === 'object' &&
    obj !== null &&
    'id' in obj &&
    'name' in obj
  );
}
```

### 4.2 Python

#### 4.2.1 PEP 8準拠
```python
# クラス: PascalCase
class EmbeddingService:
    pass

# 関数: snake_case
def generate_embedding(text: str) -> list[float]:
    pass

# 定数: UPPER_SNAKE_CASE
MAX_TOKENS = 8192

# 変数: snake_case
document_id = "abc123"
```

#### 4.2.2 型ヒント
```python
from typing import Optional, List, Dict

def classify_document(
    document_id: str,
    content: str,
    categories: Optional[List[str]] = None
) -> Dict[str, float]:
    """文書を分類する

    Args:
        document_id: 文書ID
        content: 文書内容
        categories: カテゴリリスト（オプション）

    Returns:
        カテゴリごとの信頼度スコア
    """
    pass
```

### 4.3 コメント・ドキュメント

```typescript
/**
 * 文書をアップロードする
 *
 * @param file - アップロードするファイル
 * @param options - アップロードオプション
 * @returns アップロードされた文書
 * @throws {ValidationError} ファイルサイズが上限を超えた場合
 *
 * @example
 * ```ts
 * const doc = await uploadDocument(file, {
 *   folderId: 'folder-123',
 *   tags: ['important']
 * });
 * ```
 */
async function uploadDocument(
  file: File,
  options: UploadOptions
): Promise<Document> {
  // 実装
}
```

---

## 5. テスト戦略

### 5.1 テストピラミッド

```
        /\
       /  \
      / E2E \          10%
     /______\
    /        \
   / Integration \     30%
  /______________\
 /                \
/   Unit Tests     \   60%
\__________________/
```

### 5.2 ユニットテスト

**フロントエンド: Vitest + React Testing Library**

```typescript
// DocumentCard.test.tsx
import { render, screen } from '@testing-library/react';
import { DocumentCard } from './DocumentCard';

describe('DocumentCard', () => {
  it('should render document name', () => {
    const document = {
      id: '1',
      name: 'test.pdf',
      fileType: 'pdf'
    };

    render(<DocumentCard document={document} />);

    expect(screen.getByText('test.pdf')).toBeInTheDocument();
  });
});
```

**バックエンド: Jest**

```typescript
// documents.service.spec.ts
describe('DocumentsService', () => {
  let service: DocumentsService;
  let repository: Repository<Document>;

  beforeEach(async () => {
    const module = await Test.createTestingModule({
      providers: [
        DocumentsService,
        {
          provide: getRepositoryToken(Document),
          useValue: mockRepository,
        },
      ],
    }).compile();

    service = module.get<DocumentsService>(DocumentsService);
  });

  it('should create a document', async () => {
    const createDto = { name: 'test.pdf', file: mockFile };
    const result = await service.create(createDto);

    expect(result.name).toBe('test.pdf');
  });
});
```

### 5.3 統合テスト

```typescript
// documents.e2e.spec.ts
describe('Documents API (e2e)', () => {
  let app: INestApplication;
  let authToken: string;

  beforeAll(async () => {
    const moduleFixture = await Test.createTestingModule({
      imports: [AppModule],
    }).compile();

    app = moduleFixture.createNestApplication();
    await app.init();

    // ログイン
    const loginRes = await request(app.getHttpServer())
      .post('/auth/login')
      .send({ email: 'test@example.com', password: 'password' });

    authToken = loginRes.body.access_token;
  });

  it('/documents (POST)', () => {
    return request(app.getHttpServer())
      .post('/documents')
      .set('Authorization', `Bearer ${authToken}`)
      .attach('file', 'test/fixtures/sample.pdf')
      .expect(201);
  });
});
```

### 5.4 E2Eテスト

**Playwright**

```typescript
// documents.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Document Management', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('[name=email]', 'test@example.com');
    await page.fill('[name=password]', 'password');
    await page.click('button[type=submit]');
  });

  test('should upload a document', async ({ page }) => {
    await page.goto('/documents');
    await page.click('text=Upload');

    const fileInput = await page.locator('input[type=file]');
    await fileInput.setInputFiles('tests/fixtures/sample.pdf');

    await page.click('text=Confirm');

    await expect(page.locator('text=sample.pdf')).toBeVisible();
  });
});
```

### 5.5 テストカバレッジ目標

| レイヤー | カバレッジ目標 |
|---------|--------------|
| ユニットテスト | 80%以上 |
| 統合テスト | 70%以上 |
| E2Eテスト | 主要フロー100% |

---

## 6. パフォーマンス最適化

### 6.1 フロントエンド

#### 6.1.1 コード分割
```typescript
// 動的インポート
const DocumentViewer = dynamic(() => import('./DocumentViewer'), {
  loading: () => <Skeleton />,
  ssr: false
});

// ルートベース分割（Next.js App Router自動）
```

#### 6.1.2 画像最適化
```tsx
import Image from 'next/image';

<Image
  src="/document-preview.jpg"
  width={800}
  height={600}
  alt="Document"
  loading="lazy"
  placeholder="blur"
/>
```

#### 6.1.3 キャッシング
```typescript
// React Query
const { data } = useQuery({
  queryKey: ['document', documentId],
  queryFn: () => fetchDocument(documentId),
  staleTime: 5 * 60 * 1000, // 5分
  cacheTime: 30 * 60 * 1000, // 30分
});
```

### 6.2 バックエンド

#### 6.2.1 データベースクエリ最適化
```typescript
// ✅ Good: 必要なフィールドのみ選択
const documents = await prisma.document.findMany({
  select: {
    id: true,
    name: true,
    createdAt: true,
  },
  where: { organizationId },
  take: 20,
});

// ✅ Good: N+1問題回避
const documents = await prisma.document.findMany({
  include: {
    tags: true,
    createdBy: { select: { id: true, name: true } },
  },
});
```

#### 6.2.2 キャッシング戦略
```typescript
@Injectable()
export class DocumentsService {
  constructor(
    @Inject(CACHE_MANAGER) private cacheManager: Cache
  ) {}

  async findOne(id: string): Promise<Document> {
    const cacheKey = `document:${id}`;

    // キャッシュから取得
    const cached = await this.cacheManager.get<Document>(cacheKey);
    if (cached) return cached;

    // DBから取得
    const document = await this.repository.findOne(id);

    // キャッシュに保存（10分）
    await this.cacheManager.set(cacheKey, document, 600);

    return document;
  }
}
```

#### 6.2.3 非同期処理
```typescript
// バックグラウンドジョブ
@Processor('document-processing')
export class DocumentProcessor {
  @Process('generate-embedding')
  async handleEmbedding(job: Job) {
    const { documentId, content } = job.data;

    // AI処理（時間がかかる）
    const embedding = await this.aiService.generateEmbedding(content);

    // ベクトルDBに保存
    await this.vectorDb.upsert(documentId, embedding);
  }
}

// 呼び出し側
await this.queue.add('generate-embedding', {
  documentId,
  content
});
```

---

## 7. セキュリティ実装

### 7.1 認証実装

```typescript
// JWT戦略
@Injectable()
export class JwtStrategy extends PassportStrategy(Strategy) {
  constructor(private configService: ConfigService) {
    super({
      jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken(),
      secretOrKey: configService.get('JWT_SECRET'),
    });
  }

  async validate(payload: JwtPayload) {
    return {
      userId: payload.sub,
      organizationId: payload.orgId,
      roles: payload.roles,
    };
  }
}
```

### 7.2 認可実装

```typescript
// RBAC Guard
@Injectable()
export class RolesGuard implements CanActivate {
  constructor(private reflector: Reflector) {}

  canActivate(context: ExecutionContext): boolean {
    const requiredRoles = this.reflector.get<string[]>(
      'roles',
      context.getHandler()
    );

    if (!requiredRoles) return true;

    const { user } = context.switchToHttp().getRequest();

    return requiredRoles.some(role => user.roles?.includes(role));
  }
}

// 使用例
@UseGuards(JwtAuthGuard, RolesGuard)
@Roles('admin')
@Delete(':id')
async delete(@Param('id') id: string) {
  return this.service.delete(id);
}
```

### 7.3 入力バリデーション

```typescript
// DTO
export class CreateDocumentDto {
  @IsString()
  @MinLength(1)
  @MaxLength(255)
  name: string;

  @IsUUID()
  @IsOptional()
  folderId?: string;

  @IsArray()
  @IsString({ each: true })
  @IsOptional()
  tags?: string[];

  @IsObject()
  @IsOptional()
  metadata?: Record<string, unknown>;
}
```

### 7.4 SQLインジェクション対策

```typescript
// ✅ Good: Prismaによるパラメータ化クエリ
const documents = await prisma.document.findMany({
  where: {
    name: {
      contains: searchTerm, // 自動的にエスケープ
    },
  },
});

// ❌ Bad: 生SQLの文字列結合
const query = `SELECT * FROM documents WHERE name LIKE '%${searchTerm}%'`;
```

### 7.5 XSS対策

```typescript
// フロントエンド: DOMPurifyで サニタイズ
import DOMPurify from 'dompurify';

const sanitizedContent = DOMPurify.sanitize(userInput);
```

### 7.6 CSRF対策

```typescript
// NestJS: CSRFミドルウェア
import * as csurf from 'csurf';

app.use(csurf());
```

---

## 8. デプロイメント

### 8.1 Docker化

**Dockerfile (Next.js)**
```dockerfile
FROM node:20-alpine AS base

# Dependencies
FROM base AS deps
WORKDIR /app
COPY package*.json ./
RUN npm ci

# Builder
FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .
RUN npm run build

# Runner
FROM base AS runner
WORKDIR /app
ENV NODE_ENV production

RUN addgroup --system --gid 1001 nodejs
RUN adduser --system --uid 1001 nextjs

COPY --from=builder /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

USER nextjs
EXPOSE 3000
ENV PORT 3000

CMD ["node", "server.js"]
```

**Dockerfile (NestJS)**
```dockerfile
FROM node:20-alpine AS builder

WORKDIR /app
COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM node:20-alpine AS runner

WORKDIR /app
COPY --from=builder /app/dist ./dist
COPY --from=builder /app/node_modules ./node_modules
COPY package*.json ./

EXPOSE 3000
CMD ["node", "dist/main"]
```

### 8.2 Kubernetes デプロイ

**deployment.yaml**
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: api-deployment
spec:
  replicas: 3
  selector:
    matchLabels:
      app: api
  template:
    metadata:
      labels:
        app: api
    spec:
      containers:
      - name: api
        image: documind/api:latest
        ports:
        - containerPort: 3000
        env:
        - name: DATABASE_URL
          valueFrom:
            secretKeyRef:
              name: db-secret
              key: url
        resources:
          requests:
            memory: "256Mi"
            cpu: "250m"
          limits:
            memory: "512Mi"
            cpu: "500m"
        livenessProbe:
          httpGet:
            path: /health
            port: 3000
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /ready
            port: 3000
          initialDelaySeconds: 5
          periodSeconds: 5
```

### 8.3 CI/CDパイプライン

**GitHub Actions**
```yaml
name: CI/CD Pipeline

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: '20'
      - run: npm ci
      - run: npm run lint
      - run: npm test
      - run: npm run test:e2e

  build:
    needs: test
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: docker/build-push-action@v4
        with:
          context: .
          push: true
          tags: ${{ secrets.DOCKER_REGISTRY }}/api:${{ github.sha }}

  deploy:
    needs: build
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to Kubernetes
        run: |
          kubectl set image deployment/api \
            api=${{ secrets.DOCKER_REGISTRY }}/api:${{ github.sha }}
```

---

## 9. 開発ワークフロー

### 9.1 Git ブランチ戦略

**Git Flow**
```
main (production)
  └── develop
       ├── feature/document-upload
       ├── feature/ai-search
       └── hotfix/security-patch
```

### 9.2 コミットメッセージ規約

**Conventional Commits**
```
feat: 文書アップロード機能を追加
fix: 検索結果の表示バグを修正
docs: API仕様書を更新
style: コードフォーマットを修正
refactor: DocumentServiceをリファクタリング
test: DocumentServiceのテストを追加
chore: 依存関係を更新
```

### 9.3 プルリクエスト

**PRテンプレート**
```markdown
## 概要
<!-- 変更内容の概要 -->

## 変更内容
<!-- 詳細な変更内容 -->

## チェックリスト
- [ ] テストを追加・更新した
- [ ] ドキュメントを更新した
- [ ] コードレビューを依頼した
- [ ] CI/CDが成功している

## スクリーンショット
<!-- UIの変更がある場合 -->

## 関連Issue
Closes #123
```

---

## 10. パフォーマンス監視

### 10.1 メトリクス

**主要指標**
- Response Time (p50, p95, p99)
- Throughput (requests/sec)
- Error Rate
- CPU Usage
- Memory Usage
- Database Query Time

### 10.2 アラート設定

```yaml
# Prometheus Alert Rules
groups:
  - name: api_alerts
    rules:
      - alert: HighErrorRate
        expr: rate(http_requests_total{status=~"5.."}[5m]) > 0.05
        for: 5m
        annotations:
          summary: "High error rate detected"

      - alert: HighResponseTime
        expr: http_request_duration_seconds{quantile="0.95"} > 2
        for: 5m
        annotations:
          summary: "High response time detected"
```

---

## 変更履歴

| バージョン | 日付 | 変更内容 | 作成者 |
|-----------|------|----------|--------|
| 1.0 | 2025-11-04 | 初版作成 | 開発チーム |
