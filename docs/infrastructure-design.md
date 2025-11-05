# インフラストラクチャ設計書

## 文書情報
- **プロジェクト名**: DocuMind - AI-Powered Document Management System
- **文書ID**: INFRA-DOCUMIND-001
- **バージョン**: 1.0
- **最終更新日**: 2025-11-04
- **作成者**: インフラチーム
- **ステータス**: Draft

---

## 1. インフラ概要

### 1.1 基本方針

- **クラウドネイティブ**: AWS を主要プラットフォームとして採用
- **高可用性**: Multi-AZ構成による99.9%の稼働率
- **スケーラビリティ**: Auto Scalingによる柔軟な拡張性
- **セキュリティ**: 多層防御によるセキュリティ確保
- **コスト最適化**: 適切なリソースサイジングとコスト管理

### 1.2 環境構成

| 環境 | 用途 | URL |
|------|------|-----|
| Production | 本番環境 | https://app.documind.io |
| Staging | ステージング環境 | https://staging.documind.io |
| Development | 開発環境 | https://dev.documind.io |
| Local | ローカル開発 | http://localhost:3000 |

---

## 2. AWS インフラアーキテクチャ

### 2.1 全体構成図

```
┌─────────────────────────────────────────────────────────────────────┐
│                              Internet                                │
└──────────────────────────┬──────────────────────────────────────────┘
                           │
                           ▼
                  ┌─────────────────┐
                  │  Route 53 (DNS) │
                  └────────┬─────────┘
                           │
                           ▼
                  ┌─────────────────┐
                  │   CloudFront    │ ◄── SSL/TLS証明書 (ACM)
                  │      (CDN)      │
                  └────────┬─────────┘
                           │
                           ▼
                  ┌─────────────────┐
                  │    AWS WAF      │
                  └────────┬─────────┘
                           │
        ┌──────────────────┴──────────────────┐
        │            VPC (10.0.0.0/16)        │
        │                                      │
        │  ┌────────────────────────────────┐ │
        │  │    Public Subnet (Multi-AZ)    │ │
        │  │        10.0.1.0/24             │ │
        │  │        10.0.2.0/24             │ │
        │  │  ┌──────────┐  ┌────────────┐ │ │
        │  │  │   ALB    │  │  NAT GW    │ │ │
        │  │  └────┬─────┘  └────────────┘ │ │
        │  └───────┼─────────────────────── │ │
        │          │                         │ │
        │  ┌───────┼──────────────────────┐ │ │
        │  │  Private Subnet (Multi-AZ)   │ │ │
        │  │      10.0.10.0/24            │ │ │
        │  │      10.0.11.0/24            │ │ │
        │  │  ┌───▼──────────────────┐   │ │ │
        │  │  │  EKS Cluster          │   │ │ │
        │  │  │  (Kubernetes)         │   │ │ │
        │  │  │  ┌─────────────────┐ │   │ │ │
        │  │  │  │  Web App Pods   │ │   │ │ │
        │  │  │  │  API Pods       │ │   │ │ │
        │  │  │  │  AI Service Pods│ │   │ │ │
        │  │  │  └─────────────────┘ │   │ │ │
        │  │  └──────────────────────┘   │ │ │
        │  └──────────────────────────── │ │ │
        │          │                      │ │ │
        │  ┌───────┼─────────────────────┐│ │
        │  │  Data Subnet (Multi-AZ)     ││ │
        │  │      10.0.20.0/24           ││ │
        │  │      10.0.21.0/24           ││ │
        │  │  ┌───▼────────────────────┐ ││ │
        │  │  │  RDS PostgreSQL        │ ││ │
        │  │  │  (Multi-AZ)            │ ││ │
        │  │  └────────────────────────┘ ││ │
        │  │  ┌────────────────────────┐ ││ │
        │  │  │  ElastiCache Redis     │ ││ │
        │  │  │  (Cluster Mode)        │ ││ │
        │  │  └────────────────────────┘ ││ │
        │  │  ┌────────────────────────┐ ││ │
        │  │  │  DocumentDB MongoDB    │ ││ │
        │  │  └────────────────────────┘ ││ │
        │  │  ┌────────────────────────┐ ││ │
        │  │  │  Elasticsearch         │ ││ │
        │  │  └────────────────────────┘ ││ │
        │  └────────────────────────────┘│ │
        └──────────────────────────────────┘ │
                           │
                           ▼
                  ┌─────────────────┐
                  │      S3         │ ◄── ファイルストレージ
                  │   (Buckets)     │
                  └─────────────────┘

┌─────────────────────────────────────────────────┐
│          External Services                      │
├─────────────────────────────────────────────────┤
│  - Pinecone (Vector DB)                         │
│  - OpenAI API                                   │
│  - Stripe                                       │
│  - SendGrid / SES                               │
│  - Datadog (Monitoring)                         │
└─────────────────────────────────────────────────┘
```

### 2.2 リージョン構成

**プライマリリージョン**: ap-northeast-1 (東京)
**セカンダリリージョン**: ap-northeast-3 (大阪) - DR用

---

## 3. コンピューティング

### 3.1 Amazon EKS (Kubernetes)

#### 3.1.1 クラスタ構成

```yaml
# EKS Cluster Spec
apiVersion: eksctl.io/v1alpha5
kind: ClusterConfig

metadata:
  name: documind-prod
  region: ap-northeast-1
  version: "1.28"

vpc:
  cidr: 10.0.0.0/16
  nat:
    gateway: HighlyAvailable # Multi-AZ NAT Gateway

managedNodeGroups:
  # 一般用途ノード
  - name: general
    instanceType: t3.large
    minSize: 2
    maxSize: 10
    desiredCapacity: 3
    volumeSize: 50
    privateNetworking: true
    labels:
      workload: general
    tags:
      k8s.io/cluster-autoscaler/enabled: "true"

  # AI処理用ノード（GPU）
  - name: ai-workload
    instanceType: g4dn.xlarge # NVIDIA T4 GPU
    minSize: 1
    maxSize: 5
    desiredCapacity: 2
    volumeSize: 100
    privateNetworking: true
    labels:
      workload: ai
    taints:
      - key: ai-workload
        value: "true"
        effect: NoSchedule
```

#### 3.1.2 ワークロード構成

**Web Application (Next.js)**
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: web-app
spec:
  replicas: 3
  selector:
    matchLabels:
      app: web
  template:
    metadata:
      labels:
        app: web
    spec:
      containers:
      - name: web
        image: documind/web:latest
        resources:
          requests:
            memory: "512Mi"
            cpu: "500m"
          limits:
            memory: "1Gi"
            cpu: "1000m"
        env:
        - name: NODE_ENV
          value: "production"
        - name: API_URL
          valueFrom:
            configMapKeyRef:
              name: app-config
              key: api_url
        livenessProbe:
          httpGet:
            path: /api/health
            port: 3000
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /api/ready
            port: 3000
          initialDelaySeconds: 5
          periodSeconds: 5
---
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: web-hpa
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: web-app
  minReplicas: 3
  maxReplicas: 20
  metrics:
  - type: Resource
    resource:
      name: cpu
      target:
        type: Utilization
        averageUtilization: 70
```

**API Service (NestJS)**
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: api-service
spec:
  replicas: 5
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
        resources:
          requests:
            memory: "1Gi"
            cpu: "1000m"
          limits:
            memory: "2Gi"
            cpu: "2000m"
        env:
        - name: DATABASE_URL
          valueFrom:
            secretKeyRef:
              name: db-credentials
              key: url
---
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: api-hpa
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: api-service
  minReplicas: 5
  maxReplicas: 50
  metrics:
  - type: Resource
    resource:
      name: cpu
      target:
        type: Utilization
        averageUtilization: 70
  - type: Pods
    pods:
      metric:
        name: http_requests_per_second
      target:
        type: AverageValue
        averageValue: "1000"
```

**AI Service (FastAPI)**
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: ai-service
spec:
  replicas: 2
  selector:
    matchLabels:
      app: ai
  template:
    metadata:
      labels:
        app: ai
    spec:
      nodeSelector:
        workload: ai
      tolerations:
      - key: ai-workload
        operator: Equal
        value: "true"
        effect: NoSchedule
      containers:
      - name: ai
        image: documind/ai-service:latest
        resources:
          requests:
            memory: "4Gi"
            cpu: "2000m"
            nvidia.com/gpu: 1
          limits:
            memory: "8Gi"
            cpu: "4000m"
            nvidia.com/gpu: 1
```

### 3.2 Fargate（オプション）

バッチ処理やスケジュールタスク用にFargateを使用

```yaml
apiVersion: v1
kind: Pod
metadata:
  name: batch-job
spec:
  restartPolicy: Never
  containers:
  - name: batch
    image: documind/batch:latest
    resources:
      requests:
        memory: "2Gi"
        cpu: "1000m"
```

---

## 4. データベース

### 4.1 Amazon RDS for PostgreSQL

#### 4.1.1 構成

```hcl
# Terraform設定
resource "aws_db_instance" "postgres" {
  identifier     = "documind-postgres-prod"
  engine         = "postgres"
  engine_version = "15.4"
  instance_class = "db.r6g.2xlarge"

  # ストレージ
  allocated_storage     = 500  # GB
  max_allocated_storage = 2000 # Auto Scaling上限
  storage_type          = "gp3"
  iops                  = 12000
  storage_encrypted     = true
  kms_key_id           = aws_kms_key.rds.arn

  # Multi-AZ
  multi_az = true

  # ネットワーク
  db_subnet_group_name   = aws_db_subnet_group.main.name
  vpc_security_group_ids = [aws_security_group.rds.id]
  publicly_accessible    = false

  # バックアップ
  backup_retention_period = 30
  backup_window          = "03:00-04:00"
  maintenance_window     = "mon:04:00-mon:05:00"
  copy_tags_to_snapshot  = true

  # 監視
  enabled_cloudwatch_logs_exports = ["postgresql", "upgrade"]
  performance_insights_enabled    = true
  monitoring_interval             = 60

  # パラメータグループ
  parameter_group_name = aws_db_parameter_group.postgres.name

  deletion_protection = true
  skip_final_snapshot = false
  final_snapshot_identifier = "documind-postgres-final-snapshot"
}

# リードレプリカ
resource "aws_db_instance" "postgres_replica" {
  identifier     = "documind-postgres-replica"
  replicate_source_db = aws_db_instance.postgres.identifier
  instance_class = "db.r6g.xlarge"
  publicly_accessible = false
}
```

#### 4.1.2 接続プーリング (PgBouncer)

```yaml
# PgBouncer Deployment
apiVersion: apps/v1
kind: Deployment
metadata:
  name: pgbouncer
spec:
  replicas: 2
  template:
    spec:
      containers:
      - name: pgbouncer
        image: pgbouncer/pgbouncer:1.21.0
        env:
        - name: DATABASES_HOST
          value: documind-postgres-prod.xyz.ap-northeast-1.rds.amazonaws.com
        - name: POOL_MODE
          value: transaction
        - name: MAX_CLIENT_CONN
          value: "10000"
        - name: DEFAULT_POOL_SIZE
          value: "25"
```

### 4.2 Amazon DocumentDB (MongoDB互換)

```hcl
resource "aws_docdb_cluster" "main" {
  cluster_identifier     = "documind-docdb-prod"
  engine                = "docdb"
  master_username       = "admin"
  master_password       = var.docdb_password
  backup_retention_period = 30
  preferred_backup_window = "03:00-04:00"
  skip_final_snapshot    = false
  storage_encrypted      = true
  kms_key_id            = aws_kms_key.docdb.arn

  vpc_security_group_ids = [aws_security_group.docdb.id]
  db_subnet_group_name   = aws_docdb_subnet_group.main.name

  enabled_cloudwatch_logs_exports = ["audit", "profiler"]
}

resource "aws_docdb_cluster_instance" "instances" {
  count              = 3
  identifier         = "documind-docdb-${count.index}"
  cluster_identifier = aws_docdb_cluster.main.id
  instance_class     = "db.r6g.large"
}
```

### 4.3 Amazon ElastiCache for Redis

```hcl
resource "aws_elasticache_replication_group" "redis" {
  replication_group_id       = "documind-redis-prod"
  replication_group_description = "Redis cluster for DocuMind"
  engine                     = "redis"
  engine_version             = "7.0"
  node_type                  = "cache.r6g.large"
  number_cache_clusters      = 3
  automatic_failover_enabled = true
  multi_az_enabled          = true

  subnet_group_name  = aws_elasticache_subnet_group.main.name
  security_group_ids = [aws_security_group.redis.id]

  # クラスターモード有効化
  cluster_mode {
    replicas_per_node_group = 2
    num_node_groups        = 3
  }

  # バックアップ
  snapshot_retention_limit = 7
  snapshot_window         = "03:00-05:00"

  # 暗号化
  at_rest_encryption_enabled = true
  transit_encryption_enabled = true
  auth_token_enabled        = true

  # ログ
  log_delivery_configuration {
    destination      = aws_cloudwatch_log_group.redis.name
    destination_type = "cloudwatch-logs"
    log_format       = "json"
    log_type         = "slow-log"
  }
}
```

### 4.4 Amazon Elasticsearch Service

```hcl
resource "aws_elasticsearch_domain" "main" {
  domain_name           = "documind-es-prod"
  elasticsearch_version = "8.9"

  cluster_config {
    instance_type            = "r6g.large.elasticsearch"
    instance_count           = 3
    dedicated_master_enabled = true
    dedicated_master_type    = "r6g.large.elasticsearch"
    dedicated_master_count   = 3
    zone_awareness_enabled   = true

    zone_awareness_config {
      availability_zone_count = 3
    }
  }

  ebs_options {
    ebs_enabled = true
    volume_type = "gp3"
    volume_size = 500
    iops        = 3000
  }

  encrypt_at_rest {
    enabled    = true
    kms_key_id = aws_kms_key.es.arn
  }

  node_to_node_encryption {
    enabled = true
  }

  domain_endpoint_options {
    enforce_https       = true
    tls_security_policy = "Policy-Min-TLS-1-2-2019-07"
  }

  vpc_options {
    subnet_ids         = aws_subnet.private[*].id
    security_group_ids = [aws_security_group.es.id]
  }
}
```

---

## 5. ストレージ

### 5.1 Amazon S3

#### 5.1.1 バケット構成

```hcl
# 文書ストレージバケット
resource "aws_s3_bucket" "documents" {
  bucket = "documind-documents-prod"

  tags = {
    Environment = "production"
    Purpose     = "document-storage"
  }
}

# バージョニング
resource "aws_s3_bucket_versioning" "documents" {
  bucket = aws_s3_bucket.documents.id

  versioning_configuration {
    status = "Enabled"
  }
}

# 暗号化
resource "aws_s3_bucket_server_side_encryption_configuration" "documents" {
  bucket = aws_s3_bucket.documents.id

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm     = "aws:kms"
      kms_master_key_id = aws_kms_key.s3.arn
    }
  }
}

# ライフサイクルポリシー
resource "aws_s3_bucket_lifecycle_configuration" "documents" {
  bucket = aws_s3_bucket.documents.id

  rule {
    id     = "transition-to-ia"
    status = "Enabled"

    transition {
      days          = 30
      storage_class = "STANDARD_IA"
    }

    transition {
      days          = 90
      storage_class = "GLACIER_IR"
    }
  }

  rule {
    id     = "delete-old-versions"
    status = "Enabled"

    noncurrent_version_expiration {
      noncurrent_days = 90
    }
  }
}

# Cross-Region Replication（DR用）
resource "aws_s3_bucket_replication_configuration" "documents" {
  bucket = aws_s3_bucket.documents.id
  role   = aws_iam_role.replication.arn

  rule {
    id     = "replicate-to-osaka"
    status = "Enabled"

    destination {
      bucket        = aws_s3_bucket.documents_replica.arn
      storage_class = "STANDARD_IA"

      encryption_configuration {
        replica_kms_key_id = aws_kms_key.s3_osaka.arn
      }
    }
  }
}
```

#### 5.1.2 バケット一覧

| バケット | 用途 | バージョニング | 暗号化 | レプリケーション |
|---------|------|--------------|--------|----------------|
| documind-documents-prod | 文書ファイル | ✓ | KMS | ✓ |
| documind-previews-prod | プレビュー画像 | ✗ | SSE-S3 | ✗ |
| documind-backups-prod | バックアップ | ✓ | KMS | ✓ |
| documind-logs-prod | ログ | ✗ | SSE-S3 | ✗ |
| documind-assets-prod | 静的アセット | ✗ | SSE-S3 | ✗ |

---

## 6. ネットワーク

### 6.1 VPC構成

```hcl
resource "aws_vpc" "main" {
  cidr_block           = "10.0.0.0/16"
  enable_dns_hostnames = true
  enable_dns_support   = true

  tags = {
    Name = "documind-vpc-prod"
  }
}

# パブリックサブネット
resource "aws_subnet" "public" {
  count                   = 2
  vpc_id                  = aws_vpc.main.id
  cidr_block              = "10.0.${count.index + 1}.0/24"
  availability_zone       = data.aws_availability_zones.available.names[count.index]
  map_public_ip_on_launch = true

  tags = {
    Name = "documind-public-${count.index + 1}"
    "kubernetes.io/role/elb" = "1"
  }
}

# プライベートサブネット（アプリケーション）
resource "aws_subnet" "private_app" {
  count             = 2
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.${count.index + 10}.0/24"
  availability_zone = data.aws_availability_zones.available.names[count.index]

  tags = {
    Name = "documind-private-app-${count.index + 1}"
    "kubernetes.io/role/internal-elb" = "1"
  }
}

# プライベートサブネット（データベース）
resource "aws_subnet" "private_db" {
  count             = 2
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.${count.index + 20}.0/24"
  availability_zone = data.aws_availability_zones.available.names[count.index]

  tags = {
    Name = "documind-private-db-${count.index + 1}"
  }
}

# インターネットゲートウェイ
resource "aws_internet_gateway" "main" {
  vpc_id = aws_vpc.main.id
}

# NATゲートウェイ
resource "aws_nat_gateway" "main" {
  count         = 2
  allocation_id = aws_eip.nat[count.index].id
  subnet_id     = aws_subnet.public[count.index].id
}
```

### 6.2 Application Load Balancer

```hcl
resource "aws_lb" "main" {
  name               = "documind-alb-prod"
  internal           = false
  load_balancer_type = "application"
  security_groups    = [aws_security_group.alb.id]
  subnets            = aws_subnet.public[*].id

  enable_deletion_protection = true
  enable_http2              = true
  enable_waf_fail_open     = false

  access_logs {
    bucket  = aws_s3_bucket.logs.id
    prefix  = "alb"
    enabled = true
  }
}

# HTTPSリスナー
resource "aws_lb_listener" "https" {
  load_balancer_arn = aws_lb.main.arn
  port              = "443"
  protocol          = "HTTPS"
  ssl_policy        = "ELBSecurityPolicy-TLS-1-2-2017-01"
  certificate_arn   = aws_acm_certificate.main.arn

  default_action {
    type = "forward"
    target_group_arn = aws_lb_target_group.web.arn
  }
}

# HTTPリスナー（HTTPSへリダイレクト）
resource "aws_lb_listener" "http" {
  load_balancer_arn = aws_lb.main.arn
  port              = "80"
  protocol          = "HTTP"

  default_action {
    type = "redirect"

    redirect {
      port        = "443"
      protocol    = "HTTPS"
      status_code = "HTTP_301"
    }
  }
}
```

### 6.3 CloudFront (CDN)

```hcl
resource "aws_cloudfront_distribution" "main" {
  enabled             = true
  is_ipv6_enabled     = true
  comment             = "DocuMind CDN"
  default_root_object = "index.html"
  price_class         = "PriceClass_200" # 北米、欧州、アジア

  # オリジン設定（ALB）
  origin {
    domain_name = aws_lb.main.dns_name
    origin_id   = "alb"

    custom_origin_config {
      http_port              = 80
      https_port             = 443
      origin_protocol_policy = "https-only"
      origin_ssl_protocols   = ["TLSv1.2"]
    }
  }

  # オリジン設定（S3 - 静的アセット）
  origin {
    domain_name = aws_s3_bucket.assets.bucket_regional_domain_name
    origin_id   = "s3-assets"

    s3_origin_config {
      origin_access_identity = aws_cloudfront_origin_access_identity.main.cloudfront_access_identity_path
    }
  }

  # デフォルトキャッシュ動作
  default_cache_behavior {
    allowed_methods  = ["GET", "HEAD", "OPTIONS", "PUT", "POST", "PATCH", "DELETE"]
    cached_methods   = ["GET", "HEAD", "OPTIONS"]
    target_origin_id = "alb"

    forwarded_values {
      query_string = true
      headers      = ["Host", "Authorization"]

      cookies {
        forward = "all"
      }
    }

    viewer_protocol_policy = "redirect-to-https"
    min_ttl                = 0
    default_ttl            = 0
    max_ttl                = 0
    compress               = true
  }

  # 静的アセットのキャッシュ動作
  ordered_cache_behavior {
    path_pattern     = "/assets/*"
    allowed_methods  = ["GET", "HEAD"]
    cached_methods   = ["GET", "HEAD"]
    target_origin_id = "s3-assets"

    forwarded_values {
      query_string = false
      cookies {
        forward = "none"
      }
    }

    viewer_protocol_policy = "redirect-to-https"
    min_ttl                = 0
    default_ttl            = 86400   # 1日
    max_ttl                = 31536000 # 1年
    compress               = true
  }

  # SSL証明書
  viewer_certificate {
    acm_certificate_arn      = aws_acm_certificate.cloudfront.arn
    ssl_support_method       = "sni-only"
    minimum_protocol_version = "TLSv1.2_2021"
  }

  # WAF
  web_acl_id = aws_wafv2_web_acl.main.arn

  # ログ
  logging_config {
    include_cookies = false
    bucket          = aws_s3_bucket.logs.bucket_domain_name
    prefix          = "cloudfront/"
  }

  restrictions {
    geo_restriction {
      restriction_type = "none"
    }
  }
}
```

---

## 7. セキュリティ

### 7.1 AWS WAF

```hcl
resource "aws_wafv2_web_acl" "main" {
  name  = "documind-waf-prod"
  scope = "REGIONAL"

  default_action {
    allow {}
  }

  # AWSマネージドルール: コアルールセット
  rule {
    name     = "AWSManagedRulesCommonRuleSet"
    priority = 1

    override_action {
      none {}
    }

    statement {
      managed_rule_group_statement {
        vendor_name = "AWS"
        name        = "AWSManagedRulesCommonRuleSet"
      }
    }

    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name               = "AWSManagedRulesCommonRuleSetMetric"
      sampled_requests_enabled  = true
    }
  }

  # SQLインジェクション対策
  rule {
    name     = "AWSManagedRulesSQLiRuleSet"
    priority = 2

    override_action {
      none {}
    }

    statement {
      managed_rule_group_statement {
        vendor_name = "AWS"
        name        = "AWSManagedRulesSQLiRuleSet"
      }
    }

    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name               = "AWSManagedRulesSQLiRuleSetMetric"
      sampled_requests_enabled  = true
    }
  }

  # レート制限
  rule {
    name     = "RateLimitRule"
    priority = 3

    action {
      block {}
    }

    statement {
      rate_based_statement {
        limit              = 2000
        aggregate_key_type = "IP"
      }
    }

    visibility_config {
      cloudwatch_metrics_enabled = true
      metric_name               = "RateLimitRuleMetric"
      sampled_requests_enabled  = true
    }
  }

  visibility_config {
    cloudwatch_metrics_enabled = true
    metric_name               = "documind-waf-prod"
    sampled_requests_enabled  = true
  }
}
```

### 7.2 AWS Secrets Manager

```hcl
resource "aws_secretsmanager_secret" "db_credentials" {
  name = "documind/prod/db/credentials"

  rotation_rules {
    automatically_after_days = 30
  }
}

resource "aws_secretsmanager_secret_version" "db_credentials" {
  secret_id = aws_secretsmanager_secret.db_credentials.id
  secret_string = jsonencode({
    username = "admin"
    password = random_password.db.result
    host     = aws_db_instance.postgres.address
    port     = 5432
    dbname   = "documind"
  })
}
```

---

## 8. 監視・ロギング

### 8.1 Amazon CloudWatch

#### 8.1.1 主要メトリクス

**アプリケーション**
- リクエスト数
- レスポンスタイム (p50, p95, p99)
- エラー率
- アクティブユーザー数

**インフラ**
- CPU使用率
- メモリ使用率
- ディスクI/O
- ネットワークI/O

**データベース**
- 接続数
- クエリパフォーマンス
- レプリケーション遅延

#### 8.1.2 アラーム設定

```hcl
# CPU高使用率アラーム
resource "aws_cloudwatch_metric_alarm" "high_cpu" {
  alarm_name          = "documind-high-cpu"
  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = "2"
  metric_name         = "CPUUtilization"
  namespace           = "AWS/EC2"
  period              = "300"
  statistic           = "Average"
  threshold           = "80"
  alarm_description   = "CPU使用率が80%を超えました"
  alarm_actions       = [aws_sns_topic.alerts.arn]

  dimensions = {
    ClusterName = "documind-prod"
  }
}

# エラー率アラーム
resource "aws_cloudwatch_metric_alarm" "high_error_rate" {
  alarm_name          = "documind-high-error-rate"
  comparison_operator = "GreaterThanThreshold"
  evaluation_periods  = "1"
  metric_name         = "5XXError"
  namespace           = "AWS/ApplicationELB"
  period              = "300"
  statistic           = "Sum"
  threshold           = "10"
  alarm_description   = "5xxエラーが増加しています"
  alarm_actions       = [aws_sns_topic.alerts.arn]
}
```

### 8.2 ログ集約

```hcl
# CloudWatch Logs
resource "aws_cloudwatch_log_group" "application" {
  name              = "/aws/eks/documind-prod/application"
  retention_in_days = 30
  kms_key_id       = aws_kms_key.logs.arn
}

# S3へのエクスポート（長期保存）
resource "aws_cloudwatch_log_subscription_filter" "to_s3" {
  name            = "export-to-s3"
  log_group_name  = aws_cloudwatch_log_group.application.name
  filter_pattern  = ""
  destination_arn = aws_kinesis_firehose_delivery_stream.logs.arn
}
```

### 8.3 分散トレーシング (AWS X-Ray)

```typescript
// X-Ray SDK統合
import * as AWSXRay from 'aws-xray-sdk-core';
const AWS = AWSXRay.captureAWS(require('aws-sdk'));

// Expressミドルウェア
import * as xrayExpress from 'aws-xray-sdk-express';
app.use(xrayExpress.openSegment('DocuMind'));
app.use(xrayExpress.closeSegment());
```

---

## 9. CI/CDパイプライン

### 9.1 GitHub Actions

```yaml
name: Deploy to Production

on:
  push:
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
      - uses: docker/setup-buildx-action@v2
      - uses: docker/login-action@v2
        with:
          registry: ${{ secrets.ECR_REGISTRY }}
          username: ${{ secrets.AWS_ACCESS_KEY_ID }}
          password: ${{ secrets.AWS_SECRET_ACCESS_KEY }}

      - name: Build and push
        uses: docker/build-push-action@v4
        with:
          context: .
          push: true
          tags: |
            ${{ secrets.ECR_REGISTRY }}/documind/api:${{ github.sha }}
            ${{ secrets.ECR_REGISTRY }}/documind/api:latest
          cache-from: type=gha
          cache-to: type=gha,mode=max

  deploy:
    needs: build
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Configure AWS credentials
        uses: aws-actions/configure-aws-credentials@v2
        with:
          aws-access-key-id: ${{ secrets.AWS_ACCESS_KEY_ID }}
          aws-secret-access-key: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
          aws-region: ap-northeast-1

      - name: Update kubeconfig
        run: |
          aws eks update-kubeconfig --name documind-prod --region ap-northeast-1

      - name: Deploy to EKS
        run: |
          kubectl set image deployment/api-service \
            api=${{ secrets.ECR_REGISTRY }}/documind/api:${{ github.sha }}

          kubectl rollout status deployment/api-service
```

---

## 10. バックアップ・災害復旧

### 10.1 バックアップ戦略

| リソース | 方法 | 頻度 | 保持期間 |
|---------|------|------|---------|
| RDS PostgreSQL | 自動スナップショット + WAL | 日次 + 継続的 | 30日 |
| DocumentDB | 自動スナップショット | 日次 | 30日 |
| S3 | バージョニング + CRR | リアルタイム | 無期限 |
| EKS | Velero | 日次 | 30日 |

### 10.2 災害復旧 (DR)

**目標**
- RPO: 1時間
- RTO: 4時間

**戦略**: Warm Standby

```
Primary Region (Tokyo)          Secondary Region (Osaka)
┌─────────────────────┐        ┌─────────────────────┐
│  Active Workload    │        │  Standby (Minimal)  │
│  - EKS Cluster      │        │  - EKS Cluster      │
│  - RDS (Multi-AZ)   │───┬───►│  - RDS Replica      │
│  - S3               │   │    │  - S3 (CRR)         │
└─────────────────────┘   │    └─────────────────────┘
                          │
                          └─ Cross-Region Replication
```

**DRテスト**: 四半期ごと

---

## 11. コスト見積もり（月額）

### 11.1 主要リソース

| リソース | スペック | 月額概算（USD） |
|---------|---------|---------------|
| EKS Cluster | - | $75 |
| EC2 (EKS Nodes) | 5 x t3.large | $375 |
| RDS PostgreSQL | db.r6g.2xlarge (Multi-AZ) | $950 |
| RDS Read Replica | db.r6g.xlarge | $475 |
| DocumentDB | 3 x db.r6g.large | $900 |
| ElastiCache Redis | Cluster (3 nodes) | $450 |
| Elasticsearch | 3 x r6g.large | $900 |
| S3 | 5TB | $120 |
| CloudFront | 10TB転送 | $850 |
| ALB | 2台 | $50 |
| **合計** | | **約$5,145** |

### 11.2 外部サービス

| サービス | 月額概算（USD） |
|---------|---------------|
| Pinecone | $70 |
| OpenAI API | $500 |
| Datadog | $150 |
| Stripe | 取引量による |
| SendGrid | $90 |
| **合計** | **約$810** |

**総計**: 約$6,000/月

---

## 12. スケーリング計画

### 12.1 ユーザー数によるスケーリング

| ユーザー数 | EKS Nodes | RDS | DocumentDB | 月額概算 |
|-----------|-----------|-----|-----------|---------|
| ~1,000 | 5 nodes | db.r6g.large | 2 nodes | $3,000 |
| ~5,000 | 10 nodes | db.r6g.xlarge | 3 nodes | $5,000 |
| ~10,000 | 20 nodes | db.r6g.2xlarge | 3 nodes | $8,000 |
| ~50,000 | 50 nodes | db.r6g.4xlarge | 5 nodes | $20,000 |

---

## 変更履歴

| バージョン | 日付 | 変更内容 | 作成者 |
|-----------|------|----------|--------|
| 1.0 | 2025-11-04 | 初版作成 | インフラチーム |
