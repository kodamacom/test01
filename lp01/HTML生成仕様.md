# LLM用プロンプト：Untitled UI Kit + shadcn/ui 対応（SaaS LP／Tailwind + HTML）

## 目的
Untitled UI Kit の体系に準拠し、shadcn/ui コンポーネント＋Tailwindで高コンバージョンSaaSランディングページをHTML生成。

## ベース仕様
- デザイン: Untitled UI Kit
- 実装: Tailwind CSS + shadcn/ui
- HTML: セマンティックHTML5（JSX互換構造可）
- A11y: WAI-ARIA対応、aria-label明示
- フォーマット: インデント2、コメント多め

## ページ構成
1. Navbar（固定）: ロゴ/リンク/CTA。`NavigationMenu`, `Button`
2. Hero: 左テキスト＋右メディア。`Card`, `Button`, `Badge`。背景 `bg-gradient-to-b from-white to-slate-50`
3. Partners: ロゴ群。`flex gap-8` + `grayscale opacity-70`
4. Benefits: 3列カード。`grid grid-cols-1 md:grid-cols-3 gap-8`。`Card`
5. How It Works: 3ステップ。`flex md:flex-row`。番号バッジ
6. Pricing: 3プラン。中央`Pro`を `ring-2 ring-primary` 強調。`Card`, `Badge`, `Button`
7. Testimonials: アバター＋コメント。`Avatar`, `Card`
8. FAQ: `Accordion`（shadcn/ui）。`divide-y divide-border`
9. CTA: 中央大ボックス＋`Button`。背景 `bg-gradient-to-r from-primary/10 to-accent/10`
10. Footer: ロゴ、リンク、法務、ニュースレター。`Input`, `Button`, `Separator`

## カラー（Untitled UI 準拠）
- Primary: #3B82F6 / Accent: #22C55E
- Text: slate-900 / Sub: slate-600 / Border: slate-200 / BG: slate-50

## Tailwind設定（例）
```js
// tailwind.config.js
export default {
  theme: {
    extend: {
      colors: { primary: '#3B82F6', accent: '#22C55E' },
      borderRadius: { xl: '12px' },
      boxShadow: { sm: '0 1px 2px rgba(0,0,0,.05)', md: '0 4px 10px rgba(0,0,0,.1)' }
    }
  }
}
```

## コンポーネント例（shadcn/ui）
```jsx
import { Button, Card, Badge, Accordion, AccordionItem, AccordionTrigger, AccordionContent } from "@/components/ui"

<Card className="p-6 shadow-sm border rounded-xl">
  <h3 className="text-lg font-semibold text-primary mb-2">時間短縮</h3>
  <p className="text-slate-600">従来比70%の作業時間削減。</p>
</Card>

<Accordion type="single" collapsible>
  <AccordionItem value="item-1">
    <AccordionTrigger>返金ポリシーは？</AccordionTrigger>
    <AccordionContent>年次プランは残期間に応じて返金可能。</AccordionContent>
  </AccordionItem>
</Accordion>
```

## 出力指定
- 各セクションにHTMLコメント。
- Tailwindユーティリティを明示。
- 可読性重視・整形済み。
- A11y属性を適切に付与。

### HTMLスケルトン
```html
<!doctype html>
<html lang="ja">
<head>...</head>
<body class="bg-slate-50">
  <!-- Navbar -->
  <!-- Hero -->
  <!-- Partners -->
  <!-- Benefits -->
  <!-- How It Works -->
  <!-- Pricing -->
  <!-- Testimonials -->
  <!-- FAQ -->
  <!-- CTA -->
  <!-- Footer -->
</body>
</html>
```
