# Icon Strategy (Transition Mode: Current -> SVG)

## 1) Muc tieu thuc te

Du an hien tai van dung `fa-` nhieu noi. Muc tieu la migrate co kiem soat, khong gay regression listing/booking.

## 2) Quy tac icon cho feature moi

Ngay tu bay gio:
- Feature moi uu tien SVG component (`<x-icon>` neu da co).
- Neu chua co infra SVG, tam dung icon hien co nhung phai map san sang migrate.

Khong duoc:
- Them icon style moi khac he (mix nhieu bo icon la).
- Dung icon de thay the text quan trong.

## 3) Kich thuoc va style

- Size chuan: 16, 20, 24.
- Mau: follow `currentColor`.
- Visual style: **rounded-modern** — icon trong ô vuông / pill nên dùng **bo góc 10–14px** (hoặc tròn) khớp card trên trang chủ (`.pageHome`); nét đều, không mix nhiều họ icon.

## 4) Mapping quy uoc

Dat ten icon theo action/domain:
- `list`, `grid`, `location`, `calendar`, `ticket`, `support`, `sale`.

Dat file mapping trong backlog migration:
- old class (`fa-*`)
- new svg name
- khu vuc UI
- trang thai migrate

## 5) Priority migration

P0:
- Filter view icons list/grid
- Card badges/meta icons
- Header/footer action icons

P1:
- Form icons
- Secondary content icons

## 6) Accessibility

- Icon trang tri: `aria-hidden="true"`.
- Icon button: phai co text label hoac aria-label.
- Dam bao contrast icon voi nen.

## 7) Definition of done

- Khu vuc moi khong them no ky thuat icon moi.
- Co tracking ro icon nao can migrate tiep.
- Visual icon nhat quan tren listing pages.
