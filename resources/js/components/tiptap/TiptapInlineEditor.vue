<template>
    <div class="mb-5 form-group-translation">
        <label v-if="label" class="form-label">
            {{ label }}
            <span v-if="locale">({{ locale }})</span>
        </label>
        <div>
            <div v-if="editor" class="tiptap-inline-toolbar">
                <button
                    v-tooltip
                    type="button"
                    class="tiptap-button"
                    :title="t('Italic')"
                    :disabled="!editor.can().chain().focus().toggleItalic().run()"
                    :class="{ 'is-active': editor.isActive('italic') }"
                    @click="editor.chain().focus().toggleItalic().run()"
                >
                    Light
                </button>
            </div>
            <div class="tiptap-inline-editor">
                <editor-content :id="editorId" :editor="editor" :data-language="locale" />
            </div>
            <input :id="inputId" type="hidden" :name="name" :value="markdownContent" />
        </div>
    </div>
</template>

<script setup>
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import Tooltip from 'bootstrap/js/dist/tooltip.js';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        default: null,
    },
    locale: {
        type: String,
        default: null,
    },
    initContent: {
        type: String,
        default: '',
    },
});

const content = ref(props.initContent);
const markdownContent = ref('');
const isExternalUpdate = ref(false);

const inputId = computed(() => {
    return props.name;
});

const editorId = computed(() => {
    return 'editor-' + inputId.value;
});

// Convert markdown to HTML for display
function markdownToHtml(markdown) {
    if (!markdown) {
        return '';
    }

    let html = markdown;

    // Convert *text* to <em>text</em>
    html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');

    return html;
}

// Convert HTML to markdown for storage
function htmlToMarkdown(html) {
    if (!html) {
        return '';
    }

    let markdown = html;

    // Remove paragraph tags
    markdown = markdown.replace(/<\/?p>/g, '');

    // Convert <em> to *text*
    markdown = markdown.replace(/<em>(.+?)<\/em>/g, '*$1*');

    // Clean up any remaining HTML tags
    markdown = markdown.replace(/<[^>]+>/g, '');

    return markdown.trim();
}

const editor = useEditor({
    extensions: [
        StarterKit.configure({
            heading: false,
            bulletList: false,
            orderedList: false,
            blockquote: false,
            codeBlock: false,
            horizontalRule: false,
            hardBreak: false,
        }),
    ],
    content: markdownToHtml(content.value),
    onUpdate: ({ editor }) => {
        // Don't dispatch events if this update came from an external source
        if (isExternalUpdate.value) {
            return;
        }

        const html = editor.getHTML();
        const markdown = htmlToMarkdown(html);
        markdownContent.value = markdown;

        // Dispatch events for title-cloner and slug functionality
        const hiddenInput = document.getElementById(inputId.value);
        if (hiddenInput) {
            hiddenInput.value = markdown;
            hiddenInput.dispatchEvent(new Event('keyup', { bubbles: true }));
        }
    },
});

// Initialize markdown content
watch(
    editor,
    (newEditor) => {
        if (newEditor) {
            const html = newEditor.getHTML();
            markdownContent.value = htmlToMarkdown(html);

            // Listen for external updates to the hidden input field (e.g., from title-cloner button)
            const hiddenInput = document.getElementById(inputId.value);
            if (hiddenInput) {
                const updateEditorFromInput = () => {
                    const newValue = hiddenInput.value;
                    if (newValue !== markdownContent.value) {
                        markdownContent.value = newValue;
                        const html = markdownToHtml(newValue);

                        // Set flag to prevent onUpdate from dispatching events
                        isExternalUpdate.value = true;
                        newEditor.commands.setContent(html);

                        // Reset flag after a tick
                        setTimeout(() => {
                            isExternalUpdate.value = false;
                        }, 0);
                    }
                };

                hiddenInput.addEventListener('input', updateEditorFromInput);
                hiddenInput.addEventListener('keyup', updateEditorFromInput);
            }
        }
    },
    { immediate: true },
);

const vTooltip = {
    mounted: (element) => {
        new Tooltip(element, { delay: { show: 500, hide: 0 }, trigger: 'hover' });
    },
};
</script>
