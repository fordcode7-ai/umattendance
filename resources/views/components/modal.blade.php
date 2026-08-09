@props(['id' => 'modal', 'title' => 'Modal Title'])
<div class="modal-backdrop" id="{{ $id }}" aria-hidden="true">
    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
        <div class="modal-header">
            <h2 id="{{ $id }}-title">{{ $title }}</h2>
            <button type="button" class="modal-close" data-modal-close aria-label="Close modal"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="modal-body">{{ $slot }}</div>
    </div>
</div>
