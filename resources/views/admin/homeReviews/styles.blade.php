<style>
/* Home Reviews — admin scoped styles */
.hrv-admin .hrv-hint {
    font-size: 0.8125rem;
    color: #6e6b7b;
    margin-bottom: 0.75rem;
}
.hrv-admin .hrv-hint code {
    background: #f3f2f7;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
    font-size: 0.78rem;
}
.hrv-admin .hrv-card .card-header {
    background: #fff;
}
.hrv-admin .mt-25 { margin-top: 0.25rem; }
.hrv-admin .mt-50 { margin-top: 0.5rem; }

.hrv-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
}
.hrv-stat-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
}

.hrv-review-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
}
.hrv-review-grid--new {
    border-top: 1px dashed #ebe9f1;
    padding-top: 1rem;
}

.hrv-review-card {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    padding: 0.75rem;
    border: 1px solid #ebe9f1;
    border-radius: 0.428rem;
    background: #fafafa;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}
.hrv-review-card:hover {
    border-color: #7367f0;
    box-shadow: 0 4px 18px rgba(115, 103, 240, 0.12);
}
.hrv-review-card--new {
    background: #fff;
    border-style: dashed;
}

.hrv-review-card__head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.hrv-review-card__avatar {
    flex-shrink: 0;
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    overflow: hidden;
    background: #eee;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px #ebe9f1;
}
.hrv-review-card__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.hrv-review-card__avatar--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    color: #b9b9c3;
    text-align: center;
    padding: 0.25rem;
}

.hrv-review-card__fields .form-label {
    font-size: 0.75rem;
    margin-bottom: 0.2rem;
    font-weight: 600;
    color: #5e5873;
}
.hrv-review-card__delete,
.hrv-review-card__remove-new {
    align-self: flex-start;
}

.hrv-empty {
    text-align: center;
    padding: 2rem 1rem;
    border: 2px dashed #ebe9f1;
    border-radius: 0.428rem;
    color: #6e6b7b;
    background: #fcfcfd;
}
.hrv-empty p { margin: 0; }
</style>
