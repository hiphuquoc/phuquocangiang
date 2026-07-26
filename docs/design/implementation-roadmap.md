# UI System Alignment Roadmap (AI-Friendly)

## Tong quan

Roadmap nay uu tien "dong bo he thong" cho cac module moi, thay vi redesign ly thuyet lon. Muc tieu la AI co the doc docs va tao UI moi dung style ngay.

## Phase 0 - Baseline lock (da uu tien)

- Chot baseline listing architecture:
  - `tourList` / `tourGrid`
  - `filterBox`
  - `productGridUnified`
- Chot baseline typography va spacing cho listing card.
- **Home canvas**: wrapper `.pageHome` + quy tắc “một viền / một shadow” trong `style.scss` và `design-system-overview.md` mục 4.

Deliverable:
- Docs da cap nhat theo baseline thuc te + home.

## Phase 1 - Token + pattern codification

- Trich style dung chung thanh pattern ro:
  - price + CTA
  - sale badge
  - description box
  - meta chips
- Giam override phan tan giua tour/ve/combo.

Deliverable:
- Shared SCSS rules ro pham vi.

## Phase 2 - Apply to all listing modules

- Tour listing
- Ve vui choi listing
- Combo / Air / Hotel listing (neu cung pattern)

Deliverable:
- Cac listing modules co hierarchy va action area dong nhat.

## Phase 3 - Icon transition (controlled)

- Khong them no ky thuat icon moi.
- Feature moi uu tien icon strategy moi.
- Co checklist mapping icon cu -> icon moi.

Deliverable:
- P0 icon surfaces theo strategy thong nhat.

## Phase 4 - Quality hardening

- Test text dai, title 2 dong, meta chips wrap.
- Test view switch list/grid.
- Test mobile 320/360/390.
- Test CLS va action hit-area.

Deliverable:
- QA checklist pass cho listing he thong.

## PR checklist (bat buoc cho UI moi)

- Da reuse pattern co san chua?
- Co them style hardcode cho price/CTA khong?
- Co screenshot desktop + mobile khong?
- Co test text dai/ngan va sale co/khong khong?
- Co ghi ro tai lieu docs nao duoc tham chieu khong?
