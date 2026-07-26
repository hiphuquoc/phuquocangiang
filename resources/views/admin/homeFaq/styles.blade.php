<style>
.hfaq-admin .hfq-hint {
    font-size: 0.8125rem;
    color: #6e6b7b;
    margin-bottom: 0.75rem;
}
.hfaq-admin .hfq-hint code {
    background: #f3f2f7;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
    font-size: 0.78rem;
}
.hfaq-admin .hfq-card .card-header { background: #fff; }
.hfaq-admin .mt-25 { margin-top: 0.25rem; }
.hfaq-admin .mt-50 { margin-top: 0.5rem; }

/* Mỗi FAQ một hàng — không xếp cột để tránh chật */
.hfaq-item-grid {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.hfaq-item-grid--new:not(:empty) {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px dashed #d8d6de;
}
.hfaq-item-grid--new:not(:empty)::before {
    content: "Câu hỏi mới (chưa lưu)";
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #7367f0;
}

.hfaq-item-card {
    display: flex;
    flex-direction: column;
    border: 1px solid #d8d6de;
    border-radius: 0.5rem;
    background: #fff;
    box-shadow: 0 2px 8px rgba(34, 41, 47, 0.06);
    overflow: hidden;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}
.hfaq-item-card:hover {
    border-color: #b4b2bd;
    box-shadow: 0 6px 22px rgba(115, 103, 240, 0.1);
}
.hfaq-item-card--new {
    border-style: dashed;
    border-color: #7367f0;
    background: #fcfbff;
}

.hfaq-item-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem 1rem;
    padding: 0.85rem 1.25rem;
    background: linear-gradient(180deg, #f8f8f8 0%, #f3f2f7 100%);
    border-bottom: 1px solid #ebe9f1;
}
.hfaq-item-card__head-left {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    min-width: 0;
}
.hfaq-item-card__badge {
    flex-shrink: 0;
    font-size: 0.72rem;
    font-weight: 700;
    color: #fff;
    background: #7367f0;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
}
.hfaq-item-card--new .hfq-item-card__badge {
    background: #28c76f;
}
.hfaq-item-card__head .form-check {
    margin: 0;
    padding-left: 1.6rem;
}
.hfaq-item-card__head .form-check-label {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #5e5873;
    white-space: nowrap;
}
.hfaq-item-card__head-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.hfaq-item-card__body {
    display: flex;
    flex-direction: column;
    gap: 0;
    padding: 0;
}

.hfaq-field-group {
    padding: 1.1rem 1.25rem;
    border-bottom: 1px solid #f0eff4;
}
.hfaq-field-group:last-child {
    border-bottom: none;
}
.hfaq-field-group--answer {
    background: #fafafa;
}
.hfaq-field-group--meta {
    background: #fcfcfd;
    padding-top: 0.85rem;
    padding-bottom: 0.85rem;
}

.hfaq-field-group .form-label {
    display: block;
    font-size: 0.78rem;
    margin-bottom: 0.45rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: #5e5873;
}
.hfaq-field-group .form-control {
    font-size: 0.9375rem;
    line-height: 1.5;
}
.hfaq-field-group textarea.form-control {
    min-height: 9rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.8125rem;
    line-height: 1.55;
    resize: vertical;
}
.hfaq-field-group--answer .form-label::after {
    content: " — hỗ trợ thẻ HTML";
    font-weight: 500;
    text-transform: none;
    letter-spacing: 0;
    color: #b9b9c3;
    font-size: 0.72rem;
}

.hfaq-item-card__footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: #f8f8f8;
    border-top: 1px solid #ebe9f1;
}

.hfaq-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    border: 2px dashed #ebe9f1;
    border-radius: 0.428rem;
    color: #6e6b7b;
    background: #fcfcfd;
}
.hfaq-empty p { margin: 0; }

@media (max-width: 575.98px) {
    .hfq-item-card__head {
        flex-direction: column;
        align-items: flex-start;
    }
    .hfq-item-card__head-actions {
        width: 100%;
        justify-content: space-between;
    }
}
</style>
