import Toast from 'bootstrap/js/dist/toast.js';

type ToastTheme = 'success' | 'danger';

const containerClassName = 'toast-container position-fixed bottom-0 end-0 p-3';

function createContainer(host: HTMLElement): HTMLElement {
    const container = document.createElement('div');
    container.className = containerClassName;
    container.setAttribute('aria-live', 'polite');
    host.append(container);

    return container;
}

function pageContainer(): HTMLElement {
    return document.querySelector<HTMLElement>('body > .toast-container') ?? createContainer(document.body);
}

/*
 * A modal dialog sits in the top layer, above any z-index, so a toast shown
 * while one is open goes inside it. When it closes, its toasts move back to
 * the page so that they stay visible until they hide.
 */
function container(): HTMLElement {
    const dialog = [...document.querySelectorAll<HTMLDialogElement>('dialog')].findLast((element) => element.matches(':modal'));

    if (!dialog) {
        return pageContainer();
    }

    const existing = dialog.querySelector<HTMLElement>(':scope > .toast-container');

    if (existing) {
        return existing;
    }

    const dialogContainer = createContainer(dialog);

    dialog.addEventListener(
        'close',
        () => {
            pageContainer().append(...dialogContainer.children);
            dialogContainer.remove();
        },
        { once: true },
    );

    return dialogContainer;
}

function show(message: string, theme: ToastTheme): void {
    const parent = container();
    const closeLabel = pageContainer().dataset.closeLabel ?? 'Close';

    const element = document.createElement('div');
    element.className = `toast theme-${theme}`;
    element.setAttribute('aria-atomic', 'true');

    if (theme === 'danger') {
        element.setAttribute('role', 'alert');
    }

    const body = document.createElement('div');
    body.className = 'toast-body';
    body.textContent = message;

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close';
    closeButton.dataset.bsDismiss = 'toast';
    closeButton.setAttribute('aria-label', closeLabel);

    const content = document.createElement('div');
    content.className = 'toast-content';
    content.append(body, closeButton);
    element.append(content);

    element.addEventListener('hidden.bs.toast', () => {
        Toast.getInstance(element)?.dispose();
        element.remove();
    });

    parent.append(element);
    void new Toast(element).show();
}

export default {
    success(message: string): void {
        show(message, 'success');
    },
    error(message: string): void {
        show(message, 'danger');
    },
};
