const updateOptions = (options?: RequestInit): RequestInit => {
    const update = { ...options };
    const csrfTokenElement = document.head.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');

    if (csrfTokenElement) {
        update.headers = {
            ...update.headers,
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfTokenElement.content,
        };
    }

    return update;
};

export default (url: string, options?: RequestInit): Promise<Response> => {
    const updatedOptions = updateOptions(options);
    return fetch(url, updatedOptions);
};
