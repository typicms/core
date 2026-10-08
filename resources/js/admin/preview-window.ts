import Dialog from 'bootstrap/js/dist/dialog.js';

export default () => {
    document.body.insertAdjacentHTML(
        'beforeend',
        `<dialog id="preview-modal" class="preview-dialog dialog dialog-xl">
            <iframe class="preview-dialog-iframe" id="preview-content"></iframe>
            <button class="preview-dialog-btn-close btn-close" type="button" id="close-preview" data-bs-dismiss="dialog" aria-label="Close window"></button>
        </dialog>`,
    );

    const previewModal = new Dialog('#preview-modal');
    const previewIframe = document.getElementById('preview-content') as HTMLIFrameElement;
    const closeButton = document.getElementById('close-preview');

    const openPreview = (event: Event) => {
        const target = event.target as HTMLAnchorElement;
        previewIframe.src = target.href;
        previewModal.show();
        event.preventDefault();
    };

    const closePreview = () => {
        previewModal.hide();
        previewIframe.src = '';
    };

    document.querySelectorAll('.btn-preview').forEach((button) => {
        button.addEventListener('click', openPreview);
    });

    closeButton?.addEventListener('click', closePreview);

    document.addEventListener('keydown', (event: KeyboardEvent) => {
        if (event.code === 'Escape') {
            closePreview();
        }
    });
};
