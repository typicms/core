import { mergeAttributes, Node } from '@tiptap/core';

export interface IframeOptions {
    allowFullscreen: boolean;
    autoplay: boolean;
    height: number;
    loading: string;
    width: number;
    HTMLAttributes: Record<string, unknown>;
}

declare module '@tiptap/core' {
    // eslint-disable-next-line no-unused-vars
    interface Commands<ReturnType> {
        iframe: {
            // eslint-disable-next-line no-unused-vars
            setIframe: (options: { src: string }) => ReturnType;
        };
    }
}

export const Iframe = Node.create<IframeOptions>({
    name: 'iframe',

    addOptions() {
        return {
            allowFullscreen: true,
            autoplay: false,
            height: 315,
            loading: 'lazy',
            width: 560,
            HTMLAttributes: {},
        };
    },

    inline: false,

    group: 'block',

    draggable: true,

    atom: true,

    addAttributes() {
        return {
            src: {
                default: null,
            },
            width: {
                default: this.options.width,
            },
            height: {
                default: this.options.height,
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'div[data-media-embed] iframe',
            },
        ];
    },

    addCommands() {
        return {
            setIframe:
                (options: { src: string }) =>
                ({ commands }) => {
                    return commands.insertContent({
                        type: this.name,
                        attrs: options,
                    });
                },
        };
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'div',
            { 'data-media-embed': '' },
            [
                'iframe',
                mergeAttributes(
                    this.options.HTMLAttributes,
                    {
                        allowfullscreen: this.options.allowFullscreen,
                        autoplay: this.options.autoplay,
                        height: this.options.height,
                        loading: this.options.loading,
                        width: this.options.width,
                    },
                    HTMLAttributes,
                ),
            ],
        ];
    },
});
