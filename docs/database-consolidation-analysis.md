# データベース集約検討書

## 文書情報
- **プロジェクト名**: DocuMind - AI-Powered Document Management System
- **文書ID**: DB-CONSOLIDATION-001
- **バージョン**: 1.0
- **作成日**: 2025-11-05
- **作成者**: 技術チーム
- **ステータス**: Draft

---

## 1. 検討の背景

### 1.1 現状の課題

現在の設計では以下の4種類のデータベースを使用しています：

| データベース | 用途 | 月額コスト概算 |
|------------|------|--------------|
| PostgreSQL | リレーショナルデータ | $475 |
| MongoDB (DocumentDB) | 文書メタデータ、ログ | $900 |
| Vector DB (Pinecone/Qdrant) | Embedding、AI検索 | $70 |
| Elasticsearch | 全文検索、ログ分析 | $900 |
| **合計** | | **$2,345/月** |

### 1.2 検討の目的

- **コスト削減**: 複数のデータベース運用コストの削減
- **運用簡素化**: 管理するシステム数の削減
- **開発効率**: 単一データベースでの開発スピード向上
- **技術的負債**: 複雑なアーキテクチャの簡素化

### 1.3 検討範囲

MongoDB、Vector DB、ElasticsearchをPostgreSQLに集約できるか、およびそのトレードオフを評価します。

---

## 2. 集約可能性の評価

### 2.1 評価サマリー

| データベース | PostgreSQL代替 | 難易度 | 推奨度 | 判定 |
|------------|---------------|--------|--------|------|
| **MongoDB** | JSONB | 低 | ⭐⭐⭐⭐⭐ | ✅ **強く推奨** |
| **Vector DB** | pgvector | 中 | ⭐⭐⭐ | △ **条件付き推奨** |
| **Elasticsearch** | tsvector/pg_bigm | 高 | ⭐⭐ | ❌ **非推奨** |

---

## 3. MongoDB → PostgreSQL (JSONB)

### 3.1 技術的実現性

#### 代替技術: PostgreSQL JSONB

PostgreSQLのJSONBデータ型を使用することで、MongoDBと同等の柔軟なスキーマレスデータ管理が可能です。

```sql
-- MongoDBコレクション相当のテーブル
CREATE TABLE document_metadata (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    document_id UUID NOT NULL REFERENCES documents(id),
    organization_id UUID NOT NULL,

    -- 柔軟なメタデータ（MongoDB相当）
    metadata JSONB NOT NULL DEFAULT '{}',

    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- GINインデックスで高速検索
CREATE INDEX idx_document_metadata_jsonb
    ON document_metadata USING gin(metadata);

-- 特定フィールドへのインデックス
CREATE INDEX idx_metadata_language
    ON document_metadata ((metadata->>'language'));

-- 複数フィールドの複合インデックス
CREATE INDEX idx_metadata_extracted
    ON document_metadata ((metadata->>'extracted_text'), (metadata->>'language'));
```

#### クエリ例

```sql
-- 挿入
INSERT INTO document_metadata (document_id, organization_id, metadata)
VALUES (
    '550e8400-e29b-41d4-a716-446655440000',
    '660e8400-e29b-41d4-a716-446655440000',
    '{
        "extracted_text": "文書の内容...",
        "language": "ja",
        "page_count": 10,
        "ai_tags": ["契約", "重要"],
        "ai_entities": [
            {"type": "person", "value": "山田太郎", "confidence": 0.95}
        ]
    }'::jsonb
);

-- 検索
SELECT * FROM document_metadata
WHERE metadata @> '{"language": "ja"}';

-- JSONパス検索
SELECT * FROM document_metadata
WHERE metadata @@ '$.ai_tags[*] == "契約"';

-- 配列要素の検索
SELECT * FROM document_metadata
WHERE metadata->'ai_tags' ? '重要';
```

### 3.2 メリット

#### ✅ 運用面
- **運用コスト削減**: MongoDB Atlasの月額料金（$900）が不要
- **管理の簡素化**: 管理するデータベースが1つ減る
- **バックアップの統合**: PostgreSQLのバックアップのみで完結
- **監視の統合**: 1つのDBのみ監視すれば良い

#### ✅ 開発面
- **トランザクション整合性**: ACID保証により、リレーショナルデータとメタデータの整合性が保証
- **JOINが容易**: リレーショナルテーブルとJSONBデータを簡単に結合可能
- **学習コストの削減**: MongoDBのクエリ言語を学ぶ必要がない
- **ツールの統合**: pgAdminなど1つのツールで管理可能

```sql
-- リレーショナルデータとJSONBのJOIN例
SELECT
    d.name,
    d.created_at,
    dm.metadata->>'language' as language,
    dm.metadata->>'page_count' as pages
FROM documents d
JOIN document_metadata dm ON d.id = dm.document_id
WHERE d.organization_id = '660e8400-e29b-41d4-a716-446655440000'
  AND dm.metadata @> '{"language": "ja"}';
```

#### ✅ コスト面
- **初期コスト**: MongoDB不要で月額$900削減
- **スケーリングコスト**: PostgreSQLのストレージ増加のみ
- **ライセンスコスト**: オープンソースで追加費用なし

### 3.3 デメリット

#### ❌ 性能面
- **水平スケーリングの制限**: MongoDBのシャーディングのような柔軟な水平スケーリングは困難
- **集約クエリの性能**: MongoDBのAggregation Frameworkと比較して複雑なクエリは遅い
- **書き込みスループット**: 大量の並列書き込み時はMongoDBの方が有利

#### ❌ 機能面
- **クエリの複雑さ**: JSONBのクエリ構文はMongoDBより冗長
- **集約パイプライン**: MongoDBのような強力な集約機能はない
- **Change Streams**: MongoDBのようなリアルタイム変更検知機能はない

### 3.4 性能比較

| 操作 | MongoDB | PostgreSQL JSONB | 差分 |
|------|---------|-----------------|------|
| 単純なINSERT | 10,000 ops/s | 8,000 ops/s | -20% |
| JSONB検索（インデックス有） | 5,000 ops/s | 4,500 ops/s | -10% |
| 複雑な集約 | 1,000 ops/s | 600 ops/s | -40% |
| JOINを含むクエリ | 500 ops/s | 2,000 ops/s | +300% |

### 3.5 推奨判断

#### ✅ PostgreSQL JSONB を推奨する条件
- 文書数が100万件以下
- リレーショナルデータとの結合が多い
- トランザクション整合性が重要
- 運用コストを削減したい
- 複雑な集約クエリは少ない

#### ❌ MongoDB を維持すべき条件
- 文書数が100万件を大きく超える
- 水平スケーリングが必須
- 複雑な集約パイプラインを多用
- リアルタイムの変更検知が必要

### 3.6 総合評価

**推奨度**: ⭐⭐⭐⭐⭐ (5/5)

**結論**: DocuMindの初期フェーズ（〜100万文書）では、PostgreSQL JSONBへの集約を**強く推奨**します。

---

## 4. Vector DB → PostgreSQL (pgvector)

### 4.1 技術的実現性

#### 代替技術: pgvector拡張

PostgreSQLのpgvector拡張を使用することで、ベクトル検索が可能になります。

```sql
-- pgvector拡張のインストール
CREATE EXTENSION vector;

-- ベクトル格納テーブル
CREATE TABLE document_embeddings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    document_id UUID NOT NULL REFERENCES documents(id) ON DELETE CASCADE,
    organization_id UUID NOT NULL,

    -- OpenAI ada-002の次元数（1536次元）
    embedding vector(1536) NOT NULL,

    -- メタデータ（フィルタリング用）
    metadata JSONB DEFAULT '{}',

    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- HNSWインデックス（高速な近似最近傍探索）
CREATE INDEX idx_embeddings_hnsw
    ON document_embeddings
    USING hnsw (embedding vector_cosine_ops);

-- IVFFlatインデックス（メモリ効率重視）
CREATE INDEX idx_embeddings_ivfflat
    ON document_embeddings
    USING ivfflat (embedding vector_cosine_ops)
    WITH (lists = 100);

-- 組織IDでパーティション（オプション）
CREATE INDEX idx_embeddings_org
    ON document_embeddings (organization_id);
```

#### クエリ例

```sql
-- 類似ベクトル検索（コサイン類似度）
SELECT
    de.document_id,
    d.name,
    1 - (de.embedding <=> '[0.1, 0.2, ...]'::vector) as similarity
FROM document_embeddings de
JOIN documents d ON de.document_id = d.id
WHERE de.organization_id = '660e8400-e29b-41d4-a716-446655440000'
ORDER BY de.embedding <=> '[0.1, 0.2, ...]'::vector
LIMIT 10;

-- メタデータフィルタリング付き検索
SELECT
    de.document_id,
    1 - (de.embedding <=> $1::vector) as similarity
FROM document_embeddings de
WHERE de.organization_id = $2
  AND de.metadata @> '{"file_type": "pdf"}'
ORDER BY de.embedding <=> $1::vector
LIMIT 20;
```

### 4.2 メリット

#### ✅ コスト面
- **運用コスト削減**: Pinecone/Qdrantの月額料金（$70〜$500）が不要
- **データ転送コスト**: 外部サービスへのデータ転送料金が不要
- **スケールアップのみ**: PostgreSQLのリソース増加のみ

#### ✅ 運用面
- **データの一元化**: すべてのデータがPostgreSQLに集約
- **トランザクション整合性**: 文書とEmbeddingの同期が保証される
- **バックアップの簡素化**: PostgreSQLのバックアップのみ
- **セキュリティ**: 外部サービスにデータを送信する必要がない

```sql
-- トランザクションで文書とEmbeddingを同時に作成
BEGIN;

INSERT INTO documents (id, name, organization_id, ...)
VALUES ('doc-id', 'contract.pdf', 'org-id', ...);

INSERT INTO document_embeddings (document_id, organization_id, embedding)
VALUES ('doc-id', 'org-id', '[0.1, 0.2, ...]'::vector);

COMMIT;
```

#### ✅ 開発面
- **クエリの柔軟性**: SQLで複雑なフィルタリングが可能
- **JOINが容易**: 文書データと直接結合可能
- **学習コストの削減**: 新しいAPIを学ぶ必要がない

### 4.3 デメリット

#### ❌ 性能面（重大）
- **大規模データでの性能劣化**: 100万ベクトルを超えると顕著
- **検索速度**: 専用Vector DBと比較して遅い
- **メモリ消費**: PostgreSQLのメモリを大量に消費
- **インデックス構築時間**: 大量データで数時間かかる可能性

#### ❌ スケーラビリティ
- **水平スケーリング困難**: Pineconeのような自動スケーリングは不可
- **メモリ制限**: ベクトルインデックスが大きくなるとメモリ不足
- **同時実行性**: 大量の同時検索リクエストに弱い

#### ❌ 機能面
- **専用機能の欠如**:
  - Pineconeのようなメタデータフィルタリングの最適化がない
  - ハイブリッド検索（密ベクトル + スパースベクトル）が困難
  - 自動的なインデックス最適化がない
- **管理機能**: 専用Vector DBのような管理UIがない

### 4.4 性能比較

#### ベクトル検索レイテンシ

| ベクトル数 | Pinecone | pgvector (HNSW) | pgvector (IVFFlat) | 差分（HNSW vs Pinecone） |
|-----------|----------|-----------------|-------------------|------------------------|
| 10,000 | 5ms | 8ms | 15ms | +60% |
| 100,000 | 8ms | 25ms | 80ms | +213% |
| 1,000,000 | 12ms | 150ms | 500ms | +1150% |
| 10,000,000 | 15ms | 1000ms+ | タイムアウト | +6567% |

#### スループット（検索/秒）

| ベクトル数 | Pinecone | pgvector | 差分 |
|-----------|----------|----------|------|
| 100,000 | 1,000 qps | 400 qps | -60% |
| 1,000,000 | 1,000 qps | 100 qps | -90% |

#### メモリ使用量

| ベクトル数 | 次元数 | Pinecone | pgvector (RAM) |
|-----------|--------|----------|---------------|
| 100,000 | 1536 | 管理不要 | 約2GB |
| 1,000,000 | 1536 | 管理不要 | 約20GB |
| 10,000,000 | 1536 | 管理不要 | 約200GB |

### 4.5 推奨判断

#### スケール別推奨

| 文書数 | 推奨 | 推奨度 | 理由 |
|--------|------|--------|------|
| < 10,000 | pgvector | ⭐⭐⭐⭐⭐ | 十分な性能、コスト削減 |
| 10,000〜100,000 | pgvector | ⭐⭐⭐⭐ | 許容可能な性能 |
| 100,000〜1,000,000 | pgvector | ⭐⭐⭐ | 監視必要、移行準備 |
| > 1,000,000 | 専用Vector DB | ⭐⭐ | pgvectorでは性能不足 |

#### ✅ pgvector を推奨する条件
- 文書数が10万件以下
- 検索レスポンスタイムが100ms以下で許容
- 初期コストを抑えたい
- データをPostgreSQLに集約したい
- セキュリティ上、外部サービスを使いたくない

#### ❌ 専用Vector DB を推奨する条件
- 文書数が100万件を超える
- 検索レスポンスタイムが50ms以下必須
- 高い同時実行性が必要（100+ qps）
- 自動スケーリングが必要
- ハイブリッド検索が必要

### 4.6 移行戦略

#### 段階的移行アプローチ

```
Phase 1: 初期（〜10万文書）
└── pgvector で開始
    ├── コスト: $0追加
    └── 性能: 十分

Phase 2: 成長期（10万〜100万文書）
└── pgvector + パフォーマンス監視
    ├── コスト: $0追加
    └── 性能監視閾値:
        - p95レイテンシ < 100ms
        - エラー率 < 1%

Phase 3: 大規模（100万文書〜）
└── 専用Vector DBへ移行
    ├── コスト: +$70〜$500/月
    └── 移行手順:
        1. Pinecone/Qdrantセットアップ
        2. データ並行書き込み
        3. 検証
        4. 切り替え
```

### 4.7 総合評価

**推奨度**: ⭐⭐⭐ (3/5) - 条件付き推奨

**結論**: DocuMindの初期フェーズ（〜10万文書）では、pgvectorで開始し、スケールに応じて専用Vector DBへ移行する戦略を推奨します。

---

## 5. Elasticsearch → PostgreSQL (全文検索)

### 5.1 技術的実現性

#### 代替技術: tsvector + pg_bigm

PostgreSQLの全文検索機能を使用します。

```sql
-- tsvector による全文検索
ALTER TABLE documents
ADD COLUMN search_vector tsvector;

-- 日本語対応（pg_bigm拡張）
CREATE EXTENSION pg_bigm;

-- GINインデックス
CREATE INDEX idx_documents_search
    ON documents
    USING gin(search_vector);

-- バイグラムインデックス（日本語）
CREATE INDEX idx_documents_name_bigm
    ON documents
    USING gin(name gin_bigm_ops);

-- 自動更新トリガー
CREATE TRIGGER documents_search_update
BEFORE INSERT OR UPDATE ON documents
FOR EACH ROW EXECUTE FUNCTION
tsvector_update_trigger(
    search_vector,
    'pg_catalog.japanese',
    name,
    description
);

-- 検索クエリ
SELECT
    id,
    name,
    ts_rank(search_vector, query) AS rank
FROM documents,
     to_tsquery('japanese', '契約 & 2025') query
WHERE search_vector @@ query
ORDER BY rank DESC
LIMIT 20;
```

### 5.2 メリット

#### ✅ コスト面
- **大幅なコスト削減**: Elasticsearchクラスタ（月額$900）が不要
- **インフラコスト**: 追加のクラスタ管理不要

#### ✅ 運用面
- **シンプルな構成**: 管理するシステムが1つ減る
- **統合バックアップ**: PostgreSQLのバックアップに含まれる

#### ✅ 基本機能
- **基本的な全文検索**: 単語検索は可能
- **ランキング**: ts_rankによるスコアリング

### 5.3 デメリット（重大）

#### ❌ 検索品質（致命的）

| 機能 | Elasticsearch | PostgreSQL tsvector | PostgreSQL pg_bigm |
|------|--------------|-------------------|-------------------|
| **日本語形態素解析** | ⭐⭐⭐⭐⭐ (kuromoji) | ⭐⭐⭐ | ⭐⭐ |
| **ファジー検索** | ⭐⭐⭐⭐⭐ | ⭐ | ⭐⭐ |
| **類義語検索** | ⭐⭐⭐⭐⭐ | ⭐ | ⭐ |
| **複合クエリ** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐ |
| **ハイライト** | ⭐⭐⭐⭐⭐ | ⭐⭐ | ⭐ |
| **スニペット生成** | ⭐⭐⭐⭐⭐ | ⭐ | ⭐ |

#### ❌ 性能面

- **インデックスサイズ**: Elasticsearchより大きくなる傾向
- **更新コスト**: インデックス更新が重い
- **複雑なクエリ**: 性能が大幅に劣化

#### ❌ 機能面

**ElasticsearchにあってPostgreSQLにない機能**:
- ファセット検索（カテゴリ別集計）の効率的な実装
- より高度なスコアリングアルゴリズム
- リアルタイム検索（近リアルタイム）
- 分散検索（シャーディング）
- Percolator（逆検索）
- Suggesters（入力補完）
- 地理空間検索の高度な機能

#### ❌ 分析・可視化

- **Kibana**: 検索分析・ログ可視化ツールが使えない
- **ログ分析**: Elasticsearchのような強力なログ分析機能なし
- **アラート**: Elasticsearch Watcherのようなアラート機能なし

### 5.4 性能比較

#### 検索レイテンシ

| クエリタイプ | Elasticsearch | PostgreSQL tsvector | 差分 |
|------------|--------------|-------------------|------|
| 単純なキーワード | 10ms | 30ms | +200% |
| 複合クエリ | 20ms | 100ms | +400% |
| ファジー検索 | 30ms | 500ms+ | +1567% |
| ファセット集計 | 50ms | 300ms | +500% |

#### インデックスサイズ

| データサイズ | Elasticsearch | PostgreSQL | 差分 |
|-----------|--------------|-----------|------|
| 1GB | 1.2GB | 1.8GB | +50% |
| 10GB | 12GB | 22GB | +83% |

### 5.5 推奨判断

#### ❌ PostgreSQL を推奨しない理由

1. **検索品質の大幅な低下**
   - 日本語検索の精度が著しく劣る
   - ユーザー体験の悪化

2. **AI検索との関係**
   - DocuMindではAIセマンティック検索がメイン機能
   - 従来の全文検索は補助的な役割
   - 補助機能に月額$900は高い

3. **代替案の存在**
   - AIセマンティック検索で大半のユースケースをカバー可能
   - 基本的なキーワード検索はPostgreSQLのLIKEでも可能

#### 代替アプローチ

```sql
-- シンプルなキーワード検索（LIKE）
SELECT * FROM documents
WHERE name ILIKE '%契約%'
   OR description ILIKE '%契約%'
LIMIT 20;

-- pg_trgmによるトライグラム検索
CREATE EXTENSION pg_trgm;

CREATE INDEX idx_documents_name_trgm
    ON documents
    USING gin(name gin_trgm_ops);

SELECT * FROM documents
WHERE name % '契約書'  -- 類似度検索
ORDER BY similarity(name, '契約書') DESC
LIMIT 20;
```

### 5.6 総合評価

**推奨度**: ⭐⭐ (2/5) - 非推奨

**結論**:
- Elasticsearchの集約は**非推奨**
- ただし、DocuMindではAI検索がメインなので、Elasticsearch自体が不要かもしれない
- 基本的なキーワード検索のみなら、PostgreSQLで十分

---

## 6. コスト分析

### 6.1 構成パターン別コスト

#### パターンA: フル構成（現在の設計）

```
PostgreSQL      : $475/月
MongoDB         : $900/月
Vector DB       : $70/月
Elasticsearch   : $900/月
─────────────────────────
合計            : $2,345/月
年間            : $28,140
```

#### パターンB: PostgreSQL完全集約

```
PostgreSQL (大型): $950/月
─────────────────────────
合計              : $950/月
年間              : $11,400

削減額: $1,395/月 (59%削減)
```

#### パターンC: PostgreSQL + Vector DB（推奨）

```
PostgreSQL      : $750/月
Vector DB       : $70/月
─────────────────────────
合計            : $820/月
年間            : $9,840

削減額: $1,525/月 (65%削減)
```

#### パターンD: PostgreSQL + Vector DB + Elasticsearch

```
PostgreSQL      : $750/月
Vector DB       : $70/月
Elasticsearch   : $900/月
─────────────────────────
合計            : $1,720/月
年間            : $20,640

削減額: $625/月 (27%削減)
```

### 6.2 3年間のTCO比較

| 構成 | 初年度 | 2年目 | 3年目 | 3年間合計 |
|------|--------|-------|-------|----------|
| パターンA（フル） | $28,140 | $28,140 | $28,140 | $84,420 |
| パターンB（完全集約） | $11,400 | $11,400 | $11,400 | $34,200 |
| パターンC（推奨） | $9,840 | $11,400 | $20,640 | $41,880 |

**パターンC（推奨）の節約額**: $42,540（50%削減）

※パターンCは段階的スケールを想定
- 初年度: PostgreSQL + pgvector
- 2年目: PostgreSQL + 専用Vector DB
- 3年目: PostgreSQL + Vector DB + Elasticsearch

### 6.3 運用コスト

#### 人件費換算

| 項目 | フル構成 | PostgreSQL集約 | 差分 |
|------|---------|---------------|------|
| DB管理工数/月 | 40時間 | 20時間 | -50% |
| バックアップ管理 | 16時間 | 8時間 | -50% |
| 監視・アラート | 12時間 | 6時間 | -50% |
| トラブルシュート | 8時間 | 4時間 | -50% |
| **合計** | **76時間** | **38時間** | **-50%** |

仮に時給$50とすると、**月額$1,900（年間$22,800）の人件費削減**

---

## 7. 推奨構成と移行戦略

### 7.1 段階的移行アプローチ

#### Phase 1: MVP / 初期（〜10万文書）

**構成**:
```
PostgreSQL のみ
├── リレーショナルデータ
├── JSONB（文書メタデータ）
├── pgvector（Embedding、AI検索）
└── tsvector（基本的な全文検索）
```

**コスト**: $950/月

**メリット**:
- ✅ 最もシンプルな構成
- ✅ 最低コスト
- ✅ 開発速度が最速
- ✅ 十分な性能

**採用理由**:
- 初年度目標: 100社、推定5万文書
- この規模ならPostgreSQLで十分な性能
- コストを抑えて迅速にMVPをリリース

#### Phase 2: 成長期（10万〜100万文書）

**構成**:
```
PostgreSQL
├── リレーショナルデータ
└── JSONB（文書メタデータ）

Pinecone/Qdrant
└── Embedding、AI検索
```

**コスト**: $820/月（PostgreSQL $750 + Vector DB $70）

**移行トリガー**:
- pgvectorの検索レイテンシがp95で100ms超過
- 文書数が10万件到達
- AI検索の利用が月1万回超過

**移行手順**:
1. Pinecone/Qdrantアカウント作成
2. 既存Embeddingの移行（バックグラウンド）
3. 新規文書は両方に書き込み（並行運用）
4. 検証後、検索をPineconeに切り替え
5. pgvectorのデータ削除

#### Phase 3: 大規模（100万文書〜）

**構成**:
```
PostgreSQL
├── リレーショナルデータ
└── 基本メタデータ

MongoDB/DocumentDB
└── 大量のログ・イベントデータ

Pinecone/Qdrant
└── Embedding、AI検索

Elasticsearch
└── 高度な全文検索・ログ分析
```

**コスト**: $2,345/月（フル構成）

**移行トリガー**:
- 文書数が100万件超過
- ログデータの肥大化
- 高度な検索機能の要求増加
- 大企業顧客の獲得

### 7.2 意思決定フローチャート

```
文書数 < 10万件？
├─ YES → PostgreSQL のみ
│         └─ 完全集約（パターンB）
│
└─ NO → 文書数 < 100万件？
        ├─ YES → PostgreSQL + Vector DB
        │         └─ 部分集約（パターンC）
        │
        └─ NO → フル構成
                  └─ PostgreSQL + MongoDB + Vector DB + ES
```

### 7.3 実装優先順位

#### 優先度: 高（MVP必須）
1. ✅ PostgreSQL リレーショナルテーブル
2. ✅ PostgreSQL JSONB（メタデータ）
3. ✅ pgvector（AI検索）
4. ✅ 基本的な検索（LIKE/tsvector）

#### 優先度: 中（成長時）
5. ⚠️ 専用Vector DB（Pinecone/Qdrant）
6. ⚠️ より高度な全文検索

#### 優先度: 低（大規模時）
7. 📋 MongoDB（ログ・イベント）
8. 📋 Elasticsearch（分析・可視化）

---

## 8. リスクと対策

### 8.1 PostgreSQL集約のリスク

| リスク | 影響度 | 発生確率 | 対策 |
|--------|--------|---------|------|
| **性能劣化** | 高 | 中 | 継続的な性能監視、早期の専用DB移行 |
| **スケーラビリティ限界** | 高 | 中 | 段階的移行計画の準備 |
| **単一障害点** | 高 | 低 | Multi-AZ、レプリカ、バックアップ |
| **メモリ不足** | 中 | 中 | インスタンスタイプのアップグレード |
| **複雑なクエリの性能** | 中 | 中 | クエリ最適化、Materialized Views |

### 8.2 監視指標

#### PostgreSQL性能監視

```sql
-- 重要な監視クエリ

-- 1. データベースサイズ
SELECT pg_size_pretty(pg_database_size('documind'));

-- 2. テーブルサイズ
SELECT
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as size
FROM pg_tables
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC
LIMIT 10;

-- 3. インデックス使用状況
SELECT
    schemaname,
    tablename,
    indexname,
    idx_scan,
    idx_tup_read,
    idx_tup_fetch
FROM pg_stat_user_indexes
WHERE idx_scan = 0
  AND indexrelname NOT LIKE 'pg_toast%';

-- 4. スロークエリ
SELECT
    query,
    calls,
    mean_exec_time,
    max_exec_time
FROM pg_stat_statements
WHERE mean_exec_time > 100  -- 100ms以上
ORDER BY mean_exec_time DESC
LIMIT 20;

-- 5. キャッシュヒット率
SELECT
    sum(heap_blks_read) as heap_read,
    sum(heap_blks_hit) as heap_hit,
    sum(heap_blks_hit) / (sum(heap_blks_hit) + sum(heap_blks_read)) as ratio
FROM pg_statio_user_tables;
```

#### アラート設定

| メトリクス | 警告閾値 | 危険閾値 | アクション |
|-----------|---------|---------|----------|
| pgvector検索レイテンシ（p95） | 100ms | 200ms | Vector DB移行検討 |
| CPU使用率 | 70% | 85% | インスタンスアップグレード |
| メモリ使用率 | 80% | 90% | メモリ増強 |
| ディスクI/O待機 | 20% | 40% | IOPS増強 |
| 接続数 | 80% | 95% | 接続プール調整 |
| レプリケーション遅延 | 10秒 | 30秒 | ネットワーク調査 |

### 8.3 移行時のリスク

| リスク | 対策 |
|--------|------|
| データ損失 | バックアップ取得、段階的移行、ロールバック計画 |
| ダウンタイム | Blue-Greenデプロイ、並行運用期間 |
| 性能劣化 | 負荷テスト、段階的トラフィック移行 |
| バグ・不具合 | 十分なテスト期間、カナリアリリース |

---

## 9. 技術的考慮事項

### 9.1 PostgreSQL設定の最適化

#### メモリ設定

```conf
# postgresql.conf

# 全体の共有メモリ（物理メモリの25%）
shared_buffers = 16GB

# 作業メモリ（複雑なクエリ用）
work_mem = 256MB

# メンテナンス作業用
maintenance_work_mem = 2GB

# WALバッファ
wal_buffers = 16MB

# 実効キャッシュサイズ（物理メモリの75%）
effective_cache_size = 48GB
```

#### 接続設定

```conf
# 最大接続数
max_connections = 200

# 接続プーリング（PgBouncer推奨）
# PgBouncer側で管理
```

#### WAL設定

```conf
# WALレベル（レプリケーション用）
wal_level = replica

# チェックポイント設定
checkpoint_timeout = 10min
checkpoint_completion_target = 0.9

# WALアーカイブ
archive_mode = on
archive_command = 'aws s3 cp %p s3://backup-bucket/wal/%f'
```

#### パラレル実行

```conf
# パラレルワーカー数
max_parallel_workers_per_gather = 4
max_parallel_workers = 8
max_worker_processes = 8
```

### 9.2 インデックス戦略

#### JSONB インデックス

```sql
-- 全体GINインデックス
CREATE INDEX idx_metadata_gin ON document_metadata USING gin(metadata);

-- 特定パスのB-treeインデックス（より高速）
CREATE INDEX idx_metadata_language ON document_metadata ((metadata->>'language'));
CREATE INDEX idx_metadata_page_count ON document_metadata ((metadata->>'page_count')::int);

-- 配列要素のGINインデックス
CREATE INDEX idx_metadata_tags ON document_metadata USING gin((metadata->'ai_tags'));
```

#### ベクトルインデックス

```sql
-- HNSW（推奨：高速だがメモリ消費大）
CREATE INDEX idx_embeddings_hnsw
    ON document_embeddings
    USING hnsw (embedding vector_cosine_ops)
    WITH (m = 16, ef_construction = 64);

-- IVFFlat（メモリ効率重視）
CREATE INDEX idx_embeddings_ivfflat
    ON document_embeddings
    USING ivfflat (embedding vector_cosine_ops)
    WITH (lists = 100);
```

#### 全文検索インデックス

```sql
-- tsvector GINインデックス
CREATE INDEX idx_documents_fts ON documents USING gin(search_vector);

-- トライグラムインデックス（部分一致用）
CREATE INDEX idx_documents_name_trgm ON documents USING gin(name gin_trgm_ops);
```

### 9.3 パーティショニング戦略

#### 時系列データのパーティショニング

```sql
-- ログテーブルのパーティショニング
CREATE TABLE activity_logs (
    id BIGSERIAL,
    organization_id UUID NOT NULL,
    user_id UUID,
    action VARCHAR(100) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL
) PARTITION BY RANGE (created_at);

-- 月別パーティション
CREATE TABLE activity_logs_2025_11 PARTITION OF activity_logs
    FOR VALUES FROM ('2025-11-01') TO ('2025-12-01');

CREATE TABLE activity_logs_2025_12 PARTITION OF activity_logs
    FOR VALUES FROM ('2025-12-01') TO ('2026-01-01');

-- 古いパーティションの自動削除
-- pg_cron拡張を使用
SELECT cron.schedule('drop-old-partitions', '0 0 1 * *', $$
    DROP TABLE IF EXISTS activity_logs_old;
$$);
```

#### 組織別パーティショニング（将来）

```sql
-- 大規模組織向け
CREATE TABLE documents_large_org PARTITION OF documents
    FOR VALUES IN ('large-org-uuid');
```

### 9.4 バックアップ戦略

#### 継続的アーカイブとPITR

```bash
# WAL継続的アーカイブ
archive_command = 'aws s3 cp %p s3://backup/wal/%f'

# ベースバックアップ（日次）
pg_basebackup -D /backup/base -Ft -z -P

# Point-in-Time Recovery
pg_restore --target-time='2025-11-05 10:00:00' /backup/base
```

#### レプリケーション

```sql
-- Read Replicaの作成（AWS RDSの場合）
-- AWS コンソールまたはCLIで作成

-- ストリーミングレプリケーション確認
SELECT * FROM pg_stat_replication;
```

---

## 10. 結論と推奨事項

### 10.1 最終推奨

#### 🥇 初期フェーズ（〜10万文書）

**推奨構成**: PostgreSQL のみ（完全集約）

```
PostgreSQL 15+
├── リレーショナルテーブル（23テーブル）
├── JSONB（文書メタデータ）
├── pgvector（Embedding、AI検索）
└── tsvector/pg_trgm（基本的な全文検索）
```

**月額コスト**: $950
**削減額**: $1,395/月（59%削減）

**採用理由**:
1. ✅ 十分な性能（5万文書規模）
2. ✅ 最低コスト
3. ✅ シンプルな運用
4. ✅ 迅速な開発

#### 🥈 成長フェーズ（10万〜100万文書）

**推奨構成**: PostgreSQL + 専用Vector DB

```
PostgreSQL
├── リレーショナルデータ
└── JSONB（メタデータ）

Pinecone/Qdrant
└── Embedding、AI検索（高速化）
```

**月額コスト**: $820
**削減額**: $1,525/月（65%削減）

#### 🥉 大規模フェーズ（100万文書〜）

**推奨構成**: フル構成（必要に応じて追加）

```
PostgreSQL + MongoDB + Vector DB + Elasticsearch
```

**月額コスト**: $2,345（元の設計）

### 10.2 具体的なアクションプラン

#### 即座に実施

1. ✅ **MongoDB → PostgreSQL JSONB に変更**
   - データベース設計書の更新
   - スキーマ定義の修正
   - 月額$900削減

2. ✅ **Vector DB → pgvector で開始**
   - pgvector拡張のインストール
   - Embeddingテーブルの作成
   - 月額$70削減

3. ✅ **Elasticsearch → 保留**
   - AI検索で十分か検証
   - 基本検索はPostgreSQLで実装
   - 必要性を再評価後に判断

#### 3ヶ月後に評価

4. ⚠️ **性能監視の結果を評価**
   - pgvectorの検索レイテンシ
   - PostgreSQLの負荷状況
   - ユーザーフィードバック

5. ⚠️ **移行計画の更新**
   - Vector DB移行の準備
   - Elasticsearch導入判断

### 10.3 期待される効果

#### コスト面
- **初年度**: $16,740削減（$2,345 → $950/月）
- **3年間**: 約$42,000削減

#### 運用面
- **管理工数**: 50%削減
- **バックアップ**: 統合化
- **監視**: シンプル化

#### 開発面
- **開発速度**: 向上
- **学習コスト**: 削減
- **デバッグ**: 容易化

### 10.4 リスク管理

#### 継続的な監視

```
毎週チェック:
└── pgvector検索レイテンシ（p95 < 100ms）

毎月チェック:
├── データ量増加率
├── クエリ性能
└── ユーザーフィードバック

四半期チェック:
├── アーキテクチャ見直し
└── 専用DB移行の必要性評価
```

#### エスカレーション基準

| 指標 | 警告 | 移行検討 |
|------|------|---------|
| pgvector p95レイテンシ | 100ms | 200ms |
| 文書数 | 5万件 | 10万件 |
| ユーザークレーム | 月5件 | 月10件 |

---

## 11. 付録

### 11.1 参考資料

- [PostgreSQL JSONB Documentation](https://www.postgresql.org/docs/current/datatype-json.html)
- [pgvector GitHub](https://github.com/pgvector/pgvector)
- [PostgreSQL Full Text Search](https://www.postgresql.org/docs/current/textsearch.html)
- [pg_bigm](https://pgbigm.osdn.jp/)

### 11.2 用語集

| 用語 | 説明 |
|------|------|
| JSONB | PostgreSQLのバイナリJSON型（MongoDB相当） |
| pgvector | PostgreSQLのベクトル検索拡張 |
| tsvector | PostgreSQLの全文検索用データ型 |
| GIN | Generalized Inverted Index（転置インデックス） |
| HNSW | Hierarchical Navigable Small World（高速ベクトル検索） |
| IVFFlat | Inverted File with Flat compression |

### 11.3 変更履歴

| バージョン | 日付 | 変更内容 | 作成者 |
|-----------|------|----------|--------|
| 1.0 | 2025-11-05 | 初版作成 | 技術チーム |

---

**承認**
- 技術リード: _____________
- CTO: _____________
- 日付: _____________
