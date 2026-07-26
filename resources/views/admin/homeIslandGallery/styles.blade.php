<style>
/* Home Island Gallery — admin scoped styles */
.hig-admin .hig-hint {
    font-size: 0.8125rem;
    color: #6e6b7b;
    margin-bottom: 0.75rem;
}
.hig-admin .hig-hint code {
    background: #f3f2f7;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
    font-size: 0.78rem;
}
.hig-admin .hig-card .card-header {
    background: #fff;
}
.hig-admin .mt-25 { margin-top: 0.25rem; }
.hig-admin .mt-50 { margin-top: 0.5rem; }

.hig-photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}
.hig-photo-grid--new {
    border-top: 1px dashed #ebe9f1;
    padding-top: 1rem;
}

.hig-photo-card {
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
.hig-photo-card:hover {
    border-color: #7367f0;
    box-shadow: 0 4px 18px rgba(115, 103, 240, 0.12);
}
.hig-photo-card--new {
    background: #fff;
    border-style: dashed;
}

.hig-photo-card__preview {
    position: relative;
    border-radius: 0.357rem;
    overflow: hidden;
    aspect-ratio: 4 / 3;
    background: #eee;
}
.hig-photo-card__preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.hig-photo-card__preview--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #b9b9c3;
    font-size: 0.875rem;
    font-weight: 500;
}
.hig-photo-card__badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #fff;
    background: rgba(0, 0, 0, 0.55);
}

.hig-photo-card__fields .form-label {
    font-size: 0.75rem;
    margin-bottom: 0.2rem;
    font-weight: 600;
    color: #5e5873;
}
.hig-photo-card__delete,
.hig-photo-card__remove-new {
    align-self: flex-start;
}

.hig-empty {
    text-align: center;
    padding: 2rem 1rem;
    border: 2px dashed #ebe9f1;
    border-radius: 0.428rem;
    color: #6e6b7b;
    background: #fcfcfd;
}
.hig-empty p { margin: 0; }
</style>
