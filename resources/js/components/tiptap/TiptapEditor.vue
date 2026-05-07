<template>
    <div class="mb-3 form-group-translation">
        <p v-if="label" class="form-label">{{ label }} ({{ locale }})</p>
        <div v-if="editor" class="tiptap-toolbar">
            <div class="dropdown">
                <button
                    class="tiptap-button dropdown-toggle text-start d-flex justify-content-between align-items-center"
                    style="width: 100px"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    :disabled="editor.isActive('image')"
                >
                    <span v-if="editor.isActive('heading')" class="">
                        <template v-for="level in headingLevels" :key="level">
                            <span v-if="editor.isActive('heading', { level })">{{ t('Heading') }} {{ level }}</span>
                        </template>
                    </span>
                    <span v-else>{{ t('Normal') }}</span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <button
                            class="dropdown-item small d-flex gap-1 align-items-center"
                            :class="{ active: editor.isActive('paragraph') }"
                            type="button"
                            @click="editor.chain().focus().setParagraph().run()"
                        >
                            {{ t('Normal') }}
                        </button>
                    </li>
                    <li v-for="level in headingLevels" :key="level">
                        <button
                            class="dropdown-item small d-flex gap-1 align-items-center"
                            :class="{ active: editor.isActive('heading', { level }) }"
                            type="button"
                            @click="editor.chain().focus().toggleHeading({ level }).run()"
                        >
                            {{ t('Heading') }} {{ level }}
                        </button>
                    </li>
                </ul>
            </div>
            <div class="dropdown">
                <button
                    class="tiptap-button dropdown-toggle text-start d-flex justify-content-between align-items-center"
                    style="width: 140px"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    :disabled="editor.isActive('image')"
                >
                    <span v-if="activeBlockStyle" class="tiptap-style-label">{{ t(activeBlockStyle.label) }}</span>
                    <span v-else-if="activeListStyle" class="tiptap-style-label">{{ t(activeListStyle.label) }}</span>
                    <span v-else-if="activeLinkStyle" class="tiptap-style-label">{{ t(activeLinkStyle.label) }}</span>
                    <span v-else class="tiptap-style-label">{{ t('Style') }}</span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <p class="dropdown-header text-uppercase fw-light">{{ t('Paragraph Style') }}</p>
                    </li>
                    <li v-for="blockStyle in blockStyles" :key="`${blockStyle.tag}-${blockStyle.class}`">
                        <button
                            type="button"
                            class="dropdown-item small d-flex gap-1 align-items-center"
                            :class="{ active: editor.isActive(blockStyle.tag, { class: blockStyle.class }) }"
                            @click="
                                editor.isActive(blockStyle.tag, { class: blockStyle.class })
                                    ? editor.commands.updateAttributes(blockStyle.tag, { class: null })
                                    : editor.commands.updateAttributes(blockStyle.tag, { class: blockStyle.class })
                            "
                        >
                            {{ t(blockStyle.label) }}
                        </button>
                    </li>
                    <template v-if="editor.isActive('bulletList')">
                        <li>
                            <p class="dropdown-header text-uppercase fw-light">{{ t('List Style') }}</p>
                        </li>
                        <li v-for="listStyle in listStyles" :key="`${listStyle.tag}-${listStyle.class}`">
                            <button
                                type="button"
                                class="dropdown-item small d-flex gap-1 align-items-center"
                                :class="{ active: editor.isActive(listStyle.tag, { class: listStyle.class }) }"
                                @click="
                                    editor.isActive(listStyle.tag, { class: listStyle.class })
                                        ? editor.chain().focus().updateAttributes(listStyle.tag, { class: null }).run()
                                        : editor.chain().focus().updateAttributes(listStyle.tag, { class: listStyle.class }).run()
                                "
                            >
                                {{ t(listStyle.label) }}
                            </button>
                        </li>
                    </template>
                    <template v-if="editor.isActive('link')">
                        <li>
                            <p class="dropdown-header text-uppercase fw-light">{{ t('Link Style') }}</p>
                        </li>
                        <li v-for="linkStyle in linkStyles" :key="`${linkStyle.tag}-${linkStyle.class}`">
                            <button
                                type="button"
                                class="dropdown-item small d-flex gap-1 align-items-center"
                                :class="{ active: editor.isActive('link', { class: linkStyle.class }) }"
                                @click="
                                    editor.isActive('link', { class: linkStyle.class })
                                        ? editor.commands.updateAttributes('link', { class: null })
                                        : editor.commands.updateAttributes('link', { class: linkStyle.class })
                                "
                            >
                                {{ t(linkStyle.label) }}
                            </button>
                        </li>
                    </template>
                </ul>
            </div>
            <div class="tiptap-separator"></div>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Bold')"
                :disabled="!editor.can().chain().focus().toggleBold().run()"
                :class="{ 'is-active': editor.isActive('bold') }"
                @click="editor.chain().focus().toggleBold().run()"
            >
                <bold-icon size="18" stroke-width="1.5" />
            </button>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Italic')"
                :disabled="!editor.can().chain().focus().toggleItalic().run()"
                :class="{ 'is-active': editor.isActive('italic') }"
                @click="editor.chain().focus().toggleItalic().run()"
            >
                <italic-icon size="18" stroke-width="1.5" />
            </button>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Strikethrough')"
                :disabled="!editor.can().chain().focus().toggleStrike().run()"
                :class="{ 'is-active': editor.isActive('strike') }"
                @click="editor.chain().focus().toggleStrike().run()"
            >
                <strikethrough-icon size="18" stroke-width="1.5" />
            </button>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Superscript')"
                :disabled="!editor.can().chain().focus().toggleSuperscript().run()"
                :class="{ 'is-active': editor.isActive('superscript') }"
                @click="
                    editor.isActive('subscript') && editor.chain().focus().unsetSubscript().run();
                    editor.chain().focus().toggleSuperscript().run();
                "
            >
                <superscript-icon size="18" stroke-width="1.5" />
            </button>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Subscript')"
                :disabled="!editor.can().chain().focus().toggleSubscript().run()"
                :class="{ 'is-active': editor.isActive('subscript') }"
                @click="
                    editor.isActive('superscript') && editor.chain().focus().unsetSuperscript().run();
                    editor.chain().focus().toggleSubscript().run();
                "
            >
                <subscript-icon size="18" stroke-width="1.5" />
            </button>
            <div class="tiptap-separator"></div>
            <button v-tooltip type="button" class="tiptap-button" :title="t('Remove Format')" :disabled="editor.isActive('image')" @click="editor.chain().focus().unsetAllMarks().run()">
                <remove-formatting-icon size="18" stroke-width="1.5" />
            </button>
            <div class="tiptap-separator"></div>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Insert/Remove Bulleted List')"
                :class="{ 'is-active': editor.isActive('bulletList') }"
                :disabled="editor.isActive('image')"
                @click="editor.chain().focus().toggleBulletList().run()"
            >
                <list-icon size="18" stroke-width="1.5" />
            </button>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Insert/Remove Numbered List')"
                :class="{ 'is-active': editor.isActive('orderedList') }"
                :disabled="editor.isActive('image')"
                @click="editor.chain().focus().toggleOrderedList().run()"
            >
                <list-ordered-icon size="18" stroke-width="1.5" />
            </button>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Increase Indent')"
                :disabled="!editor.can().sinkListItem('listItem')"
                @click="editor.chain().focus().sinkListItem('listItem').run()"
            >
                <indent-increase-icon size="18" stroke-width="1.5" />
            </button>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Decrease Indent')"
                :disabled="!editor.can().liftListItem('listItem')"
                @click="editor.chain().focus().liftListItem('listItem').run()"
            >
                <indent-decrease-icon size="18" stroke-width="1.5" />
            </button>
            <template v-if="textAlign">
                <div class="tiptap-separator"></div>
                <button
                    v-tooltip
                    type="button"
                    class="tiptap-button"
                    :title="t('Align Left')"
                    :class="{ 'is-active': editor.isActive({ textAlign: 'left' }) }"
                    @click="editor.chain().focus().setTextAlign('left').run()"
                >
                    <align-left-icon size="18" stroke-width="1.5" />
                </button>
                <button
                    v-tooltip
                    type="button"
                    class="tiptap-button"
                    :title="t('Align Center')"
                    :class="{ 'is-active': editor.isActive({ textAlign: 'center' }) }"
                    @click="editor.chain().focus().setTextAlign('center').run()"
                >
                    <align-center-icon size="18" stroke-width="1.5" />
                </button>
                <button
                    v-tooltip
                    type="button"
                    class="tiptap-button"
                    :title="t('Align Right')"
                    :class="{ 'is-active': editor.isActive({ textAlign: 'right' }) }"
                    @click="editor.chain().focus().setTextAlign('right').run()"
                >
                    <align-right-icon size="18" stroke-width="1.5" />
                </button>
            </template>
            <div class="tiptap-separator"></div>
            <button v-tooltip type="button" class="tiptap-button" :title="t('Link')" :class="{ 'is-active': editor.isActive('link') }" :disabled="editor.isActive('image')" @click="openLinkDialog">
                <link2-icon size="18" stroke-width="1.5" />
            </button>
            <button v-tooltip type="button" class="tiptap-button" :title="t('Unset Link')" :disabled="editor.isActive('image')" @click="editor.chain().focus().unsetLink().run()">
                <link2-off-icon size="18" stroke-width="1.5" />
            </button>
            <div class="tiptap-separator"></div>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Block Quote')"
                :class="{ 'is-active': editor.isActive('blockquote') }"
                @click="editor.chain().focus().toggleBlockquote().run()"
            >
                <quote-icon size="18" stroke-width="1.5" />
            </button>
            <div class="tiptap-separator"></div>
            <button v-tooltip type="button" class="tiptap-button" :title="t('Image')" :class="{ 'is-active': editor.isActive('image') }" @click="openImageDialog">
                <image-icon size="18" stroke-width="1.5" />
            </button>
            <button v-tooltip type="button" class="tiptap-button" :title="t('YouTube Video')" :class="{ 'is-active': editor.isActive('youtube') }" @click="openYoutubeDialog">
                <square-play-icon size="18" stroke-width="1.5" />
            </button>
            <button v-tooltip type="button" class="tiptap-button" :title="t('Embed Iframe')" @click="openIframeDialog">
                <video-icon size="18" stroke-width="1.5" />
            </button>

            <!-- Table-->
            <div class="dropdown">
                <button class="tiptap-button dropdown-toggle text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <sheet-icon size="18" stroke-width="1.5" />
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <button type="button" class="dropdown-item small d-flex gap-1 align-items-center" @click="editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()">
                            <grid2x2-plus-icon size="18" stroke-width="1" />
                            {{ t('Insert table') }}
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item small d-flex gap-1 align-items-center" :disabled="!editor.can().deleteTable()" @click="editor.chain().focus().deleteTable().run()">
                            <grid2x2-x-icon size="18" stroke-width="1" />
                            {{ t('Delete table') }}
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item small d-flex gap-1 align-items-center" :disabled="!editor.can().fixTables()" @click="editor.chain().focus().fixTables().run()">
                            <grid2x2-check-icon size="18" stroke-width="1" />
                            {{ t('Fix tables') }}
                        </button>
                    </li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li>
                        <button
                            type="button"
                            class="dropdown-item small d-flex gap-1 align-items-center"
                            :disabled="!editor.can().addColumnBefore()"
                            @click="editor.chain().focus().addColumnBefore().run()"
                        >
                            <between-horizontal-end-icon size="18" stroke-width="1" />
                            {{ t('Add column before') }}
                        </button>
                    </li>
                    <li>
                        <button
                            type="button"
                            class="dropdown-item small d-flex gap-1 align-items-center"
                            :disabled="!editor.can().addColumnAfter()"
                            @click="editor.chain().focus().addColumnAfter().run()"
                        >
                            <between-horizontal-start-icon size="18" stroke-width="1" />
                            {{ t('Add column after') }}
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item small d-flex gap-1 align-items-center" :disabled="!editor.can().deleteColumn()" @click="editor.chain().focus().deleteColumn().run()">
                            <x-icon size="18" stroke-width="1" />
                            {{ t('Delete column') }}
                        </button>
                    </li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li>
                        <button type="button" class="dropdown-item small d-flex gap-1 align-items-center" :disabled="!editor.can().addRowBefore()" @click="editor.chain().focus().addRowBefore().run()">
                            <between-vertical-end-icon size="18" stroke-width="1" />
                            {{ t('Add row before') }}
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item small d-flex gap-1 align-items-center" :disabled="!editor.can().addRowAfter()" @click="editor.chain().focus().addRowAfter().run()">
                            <between-vertical-start-icon size="18" stroke-width="1" />
                            {{ t('Add row after') }}
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item small d-flex gap-1 align-items-center" :disabled="!editor.can().deleteRow()" @click="editor.chain().focus().deleteRow().run()">
                            <x-icon size="18" stroke-width="1" />
                            {{ t('Delete row') }}
                        </button>
                    </li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li>
                        <button type="button" class="dropdown-item small d-flex gap-1 align-items-center" :disabled="!editor.can().mergeCells()" @click="editor.chain().focus().mergeCells().run()">
                            <table-cells-merge-icon size="18" stroke-width="1" />
                            {{ t('Merge cells') }}
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item small d-flex gap-1 align-items-center" :disabled="!editor.can().splitCell()" @click="editor.chain().focus().splitCell().run()">
                            <table-cells-split-icon size="18" stroke-width="1" />
                            {{ t('Split cell') }}
                        </button>
                    </li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li>
                        <button
                            type="button"
                            class="dropdown-item small d-flex gap-1 align-items-center"
                            :disabled="!editor.can().toggleHeaderColumn()"
                            @click="editor.chain().focus().toggleHeaderColumn().run()"
                        >
                            {{ t('Toggle header column') }}
                        </button>
                    </li>
                    <li>
                        <button
                            type="button"
                            class="dropdown-item small d-flex gap-1 align-items-center"
                            :disabled="!editor.can().toggleHeaderRow()"
                            @click="editor.chain().focus().toggleHeaderRow().run()"
                        >
                            {{ t('Toggle header row') }}
                        </button>
                    </li>
                    <li>
                        <button
                            type="button"
                            class="dropdown-item small d-flex gap-1 align-items-center"
                            :disabled="!editor.can().toggleHeaderCell()"
                            @click="editor.chain().focus().toggleHeaderCell().run()"
                        >
                            {{ t('Toggle header cell') }}
                        </button>
                    </li>
                </ul>
            </div>
            <div class="tiptap-separator"></div>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Code Block')"
                :class="{ 'is-active': editor.isActive('codeBlock') }"
                :disabled="editor.isActive('image')"
                @click="editor.chain().focus().toggleCodeBlock().run()"
            >
                <square-code-icon size="18" stroke-width="1.5" />
            </button>
            <button
                v-tooltip
                type="button"
                class="tiptap-button"
                :title="t('Code')"
                :disabled="!editor.can().chain().focus().toggleCode().run()"
                :class="{ 'is-active': editor.isActive('code') }"
                @click="editor.chain().focus().toggleCode().run()"
            >
                <code-icon size="18" stroke-width="1.5" />
            </button>
            <div class="tiptap-separator"></div>
            <button v-tooltip type="button" class="tiptap-button" :title="t('Insert Horizontal Line')" @click="editor.chain().focus().setHorizontalRule().run()">
                <minus-icon size="18" stroke-width="1.5" />
            </button>
            <button v-tooltip type="button" class="tiptap-button" :title="t('Insert Line Break')" @click="editor.chain().focus().setHardBreak().run()">
                <wrap-text-icon size="18" stroke-width="1.5" />
            </button>
            <div class="tiptap-separator"></div>
            <button v-tooltip type="button" class="tiptap-button" :title="t('Undo')" :disabled="!editor.can().chain().focus().undo().run()" @click="editor.chain().focus().undo().run()">
                <undo-icon size="18" stroke-width="1.5" />
            </button>
            <button v-tooltip type="button" class="tiptap-button" :title="t('Redo')" :disabled="!editor.can().chain().focus().redo().run()" @click="editor.chain().focus().redo().run()">
                <redo-icon size="18" stroke-width="1.5" />
            </button>
            <div class="tiptap-separator"></div>
            <button v-tooltip type="button" class="tiptap-button" :title="t('Edit Source Code')" @click="openSourceCodeDialog">
                <file-code-icon size="18" stroke-width="1.5" />
            </button>
        </div>
        <bubble-menu v-if="editor" :editor="editor">
            <div class="bubble-menu btn-group shadow">
                <button
                    v-if="!editor.isActive('image')"
                    v-tooltip
                    type="button"
                    class="tiptap-button"
                    :title="t('Bold')"
                    :class="{ 'is-active': editor.isActive('bold') }"
                    @click="editor.chain().focus().toggleBold().run()"
                >
                    <bold-icon size="18" stroke-width="1.5" />
                </button>
                <button
                    v-if="!editor.isActive('image')"
                    v-tooltip
                    type="button"
                    class="tiptap-button"
                    :title="t('Italic')"
                    :class="{ 'is-active': editor.isActive('italic') }"
                    @click="editor.chain().focus().toggleItalic().run()"
                >
                    <italic-icon size="18" stroke-width="1.5" />
                </button>
                <button v-if="!editor.isActive('image')" v-tooltip type="button" class="tiptap-button" :title="t('Link')" :class="{ 'is-active': editor.isActive('link') }" @click="openLinkDialog">
                    <link2-icon size="18" stroke-width="1.5" />
                </button>
                <button v-if="editor.isActive('link')" v-tooltip type="button" class="tiptap-button" :title="t('Unset Link')" @click="editor.chain().focus().unsetLink().run()">
                    <link2-off-icon size="18" stroke-width="1.5" />
                </button>
                <button v-if="editor.isActive('image')" v-tooltip type="button" class="tiptap-button" :title="t('Edit')" @click="openImageDialog">Edit</button>
            </div>
        </bubble-menu>
        <div class="rich-content-container-border">
            <editor-content class="rich-content-container" :editor="editor" :data-language="locale" />
        </div>
        <textarea v-if="editor" :name="name" class="d-none" :value="editor.getHTML()"></textarea>
        <tiptap-link-dialog :id="'link-dialog-' + id" v-model:link="link" v-model:show="linkDialogOpened" @save="setLink"></tiptap-link-dialog>
        <tiptap-image-dialog :id="'image-dialog-' + id" v-model:image="image" v-model:captioned="imageCaptioned" v-model:show="imageDialogOpened" @save="setImage"></tiptap-image-dialog>
        <tiptap-iframe-dialog :id="'youtube-dialog-' + id" v-model:video="youtube" v-model:show="youtubeDialogOpened" :title="t('YouTube Video')" @save="addYoutube"></tiptap-iframe-dialog>
        <tiptap-iframe-dialog :id="'iframe-dialog-' + id" v-model:video="iframe" v-model:show="iframeDialogOpened" :title="t('Media embed')" @save="addIframe"></tiptap-iframe-dialog>
        <tiptap-source-code-dialog :id="'source-code-dialog-' + id" v-model:html="sourceCodeHtml" v-model:show="sourceCodeDialogOpened" @save="setSourceCode"></tiptap-source-code-dialog>
    </div>
</template>

<script>
let publicCssLoaded = false;

function loadPublicCss() {
    if (publicCssLoaded) {
        return;
    }
    publicCssLoaded = true;

    const cssUrl = document.querySelector('meta[name="public-css-url"]')?.getAttribute('content');

    if (!cssUrl) {
        return;
    }

    fetch(cssUrl)
        .then((response) => response.text())
        .then((cssText) => {
            const style = document.createElement('style');
            style.setAttribute('data-tiptap-public-css', '');
            const scopedCss = cssText.replace(/:root/g, ':scope');
            style.textContent = `@scope (.rich-content-container) { ${scopedCss} }`;
            document.body.appendChild(style);
        })
        .catch((error) => {
            console.error('Failed to load public CSS for TipTap editor:', error);
            publicCssLoaded = false;
        });
}

export default {};
</script>

<script setup>
import {
    AlignCenterIcon,
    AlignLeftIcon,
    AlignRightIcon,
    BetweenHorizontalEndIcon,
    BetweenHorizontalStartIcon,
    BetweenVerticalEndIcon,
    BetweenVerticalStartIcon,
    BoldIcon,
    CodeIcon,
    FileCodeIcon,
    Grid2x2CheckIcon,
    Grid2x2PlusIcon,
    Grid2x2XIcon,
    ImageIcon,
    IndentDecreaseIcon,
    IndentIncreaseIcon,
    ItalicIcon,
    Link2Icon,
    Link2OffIcon,
    ListIcon,
    ListOrderedIcon,
    MinusIcon,
    QuoteIcon,
    RedoIcon,
    RemoveFormattingIcon,
    SheetIcon,
    SquareCodeIcon,
    StrikethroughIcon,
    SubscriptIcon,
    SuperscriptIcon,
    TableCellsMergeIcon,
    TableCellsSplitIcon,
    UndoIcon,
    VideoIcon,
    WrapTextIcon,
    XIcon,
    SquarePlayIcon,
} from '@lucide/vue';
import Image from '@tiptap/extension-image';
import { BulletList } from '@tiptap/extension-list';
import Paragraph from '@tiptap/extension-paragraph';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';
import { TableKit } from '@tiptap/extension-table';
import TextAlign from '@tiptap/extension-text-align';
import Typography from '@tiptap/extension-typography';
import Youtube from '@tiptap/extension-youtube';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { BubbleMenu } from '@tiptap/vue-3/menus';
import Tooltip from 'bootstrap/js/dist/tooltip';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import { Figcaption } from './figcaption.ts';
import { Figure } from './figure.ts';
import { Iframe } from './iframe.ts';
import TiptapIframeDialog from './TiptapIframeDialog.vue';
import TiptapImageDialog from './TiptapImageDialog.vue';
import TiptapLinkDialog from './TiptapLinkDialog.vue';
import TiptapSourceCodeDialog from './TiptapSourceCodeDialog.vue';

const { t } = useI18n();

const link = ref({});
const linkDialogOpened = ref(false);

const image = ref({});
const imageCaptioned = ref(false);
const imageDialogOpened = ref(false);

const youtube = ref({});
const youtubeDialogOpened = ref(false);

const iframe = ref({});
const iframeDialogOpened = ref(false);

const sourceCodeHtml = ref('');
const sourceCodeDialogOpened = ref(false);

const id = computed(() => {
    return String(props.name)
        .replace(/[^a-z0-9]/g, '-')
        .replace(/-+$/, '');
});

const props = defineProps({
    name: {
        type: String,
    },
    label: {
        type: String,
        default: null,
    },
    locale: {
        type: String,
        default: 'en',
    },
    initContent: {
        type: String,
        default: '',
    },
    textAlign: {
        type: Boolean,
        default: false,
    },
    headingLevels: {
        type: Array,
        default: () => [1, 2, 3, 4, 5, 6],
    },
    blockStyles: {
        type: Array,
        default: () => [
            { tag: 'paragraph', class: 'lead', label: 'Lead Paragraph' },
            { tag: 'paragraph', class: 'alert alert-info', label: 'Alert Info' },
            { tag: 'paragraph', class: 'alert alert-warning', label: 'Alert Warning' },
            { tag: 'paragraph', class: 'alert alert-success', label: 'Alert Success' },
            { tag: 'paragraph', class: 'alert alert-danger', label: 'Alert Danger' },
        ],
    },
    listStyles: {
        type: Array,
        default: () => [{ tag: 'bulletList', class: 'arrow-list', label: 'Arrow List' }],
    },
    linkStyles: {
        type: Array,
        default: () => [
            { tag: 'a', class: 'btn btn-primary', label: 'Button Primary' },
            { tag: 'a', class: 'btn btn-secondary', label: 'Button Secondary' },
            { tag: 'a', class: 'btn btn-outline-primary', label: 'Button Outline Primary' },
            { tag: 'a', class: 'btn btn-outline-secondary', label: 'Button Outline Secondary' },
        ],
    },
});

const transformHtml = (html) => {
    if (!html) {
        return html;
    }

    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');

    // Find all iframes
    const iframes = doc.querySelectorAll('iframe');

    iframes.forEach((iframe) => {
        const parent = iframe.parentElement;

        if (!parent) {
            return;
        }

        // Check if the iframe is from YouTube
        const src = iframe.getAttribute('src') || '';
        const isYoutube = src.includes('youtube.com') || src.includes('youtu.be');

        // Determine the data attribute to add
        const dataAttr = isYoutube ? 'data-youtube-video' : 'data-media-embed';

        // If the parent is a <p> tag, replace it with a <div>
        if (parent.tagName.toLowerCase() === 'p') {
            const div = doc.createElement('div');

            // Copy all attributes from p to div
            Array.from(parent.attributes).forEach((attr) => {
                div.setAttribute(attr.name, attr.value);
            });

            // Add the data attribute
            div.setAttribute(dataAttr, '');

            // Move all children from p to div
            while (parent.firstChild) {
                div.appendChild(parent.firstChild);
            }

            // Replace p with div
            parent.replaceWith(div);
        } else if (parent.tagName.toLowerCase() === 'div') {
            // If the parent is already a div, just add the data attribute
            parent.setAttribute(dataAttr, '');
        }
    });

    return doc.body.innerHTML;
};

const content = ref(transformHtml(props.initContent));

const activeBlockStyle = computed(() => {
    if (!editor.value) {
        return null;
    }

    return props.blockStyles.find((style) => editor.value.isActive(style.tag, { class: style.class })) || null;
});

const activeListStyle = computed(() => {
    if (!editor.value) {
        return null;
    }

    return props.listStyles.find((style) => editor.value.isActive(style.tag, { class: style.class })) || null;
});

const activeLinkStyle = computed(() => {
    if (!editor.value) {
        return null;
    }

    return props.linkStyles.find((style) => editor.value.isActive('link', { class: style.class })) || null;
});

const CustomParagraph = Paragraph.extend({
    addAttributes() {
        return {
            class: {
                default: null,
                renderHTML: (attributes) => {
                    return {
                        class: attributes.class || null,
                    };
                },
            },
        };
    },
});

const CustomBulletList = BulletList.extend({
    addAttributes() {
        return {
            class: {
                default: null,
                renderHTML: (attributes) => {
                    return {
                        class: attributes.class || null,
                    };
                },
            },
        };
    },
});

const CustomImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            style: {
                default: null,
                parseHTML: (element) => element.getAttribute('style'),
                renderHTML: (attributes) => {
                    if (!attributes.style) {
                        return {};
                    }

                    return {
                        style: attributes.style,
                    };
                },
            },
            class: {
                default: null,
                parseHTML: (element) => element.getAttribute('class'),
                renderHTML: (attributes) => {
                    if (!attributes.class) {
                        return {};
                    }

                    return {
                        class: attributes.class,
                    };
                },
            },
            dataOriginalWidth: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-original-width'),
                renderHTML: (attributes) => {
                    if (!attributes.dataOriginalWidth) {
                        return {};
                    }

                    return {
                        'data-original-width': attributes.dataOriginalWidth,
                    };
                },
            },
            dataOriginalHeight: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-original-height'),
                renderHTML: (attributes) => {
                    if (!attributes.dataOriginalHeight) {
                        return {};
                    }

                    return {
                        'data-original-height': attributes.dataOriginalHeight,
                    };
                },
            },
        };
    },
});

const editor = useEditor({
    editorProps: {
        attributes: {
            class: 'rich-content',
        },
    },
    extensions: [
        StarterKit.configure({
            listKeymap: false,
            underline: false,
            trailingNode: false,
            paragraph: false,
            bulletList: false,
            heading: {
                levels: props.headingLevels,
            },
            link: {
                openOnClick: false,
                defaultProtocol: 'https',
                HTMLAttributes: {
                    rel: null,
                    target: null,
                },
            },
        }),
        CustomBulletList,
        Typography,
        Superscript,
        Subscript,
        Youtube.configure({
            nocookie: true,
            modestBranding: true,
            width: 560,
            height: 315,
        }),
        Iframe.configure({
            width: 560,
            height: 315,
        }),
        Figure,
        Figcaption,
        CustomImage,
        CustomParagraph,
        TableKit.configure({
            table: { resizable: false },
        }),
        ...(props.textAlign
            ? [
                  TextAlign.configure({
                      types: ['heading', 'paragraph'],
                  }),
              ]
            : []),
    ],
    content: content.value,
});

const openLinkDialog = function () {
    linkDialogOpened.value = true;
    link.value = {
        href: editor.value.getAttributes('link').href,
        target: editor.value.getAttributes('link').target,
    };
};

const setLink = function () {
    if (link.value.href === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
    } else {
        editor.value.chain().focus().extendMarkRange('link').setLink(link.value).run();
    }
};

const openImageDialog = function () {
    imageDialogOpened.value = true;
    const isFigure = editor.value.view.state.selection.$head.parent.type.name === 'figure';
    imageCaptioned.value = isFigure;

    const imageAttrs = editor.value.getAttributes('image');
    const figureAttrs = isFigure ? editor.value.getAttributes('figure') : {};

    // Determine alignment from style attribute
    const style = isFigure ? figureAttrs.style : imageAttrs.style;
    let align = 'none';
    if (style) {
        if (style.includes('float:left') || style.includes('float: left')) {
            align = 'left';
        } else if (style.includes('float:right') || style.includes('float: right')) {
            align = 'right';
        }
    }

    image.value = {
        src: imageAttrs.src,
        alt: imageAttrs.alt,
        width: imageAttrs.width,
        height: imageAttrs.height,
        dataOriginalWidth: imageAttrs.dataOriginalWidth,
        dataOriginalHeight: imageAttrs.dataOriginalHeight,
        align: align,
        customSize: (isFigure ? figureAttrs.class : imageAttrs.class)?.includes('custom-size') || false,
    };
};

const setImage = function () {
    if (image.value.src) {
        // Determine style based on alignment
        const style = image.value.align === 'left' ? 'float:left' : image.value.align === 'right' ? 'float:right' : null;
        const imageClass = image.value.customSize ? 'custom-size' : null;

        if (editor.value.view.state.selection.$head.parent.type.name !== 'figure') {
            // If the current image is not in a figure…
            if (imageCaptioned.value) {
                // Add figure and figcaption with style.
                editor.value
                    .chain()
                    .focus()
                    .insertContent({
                        type: 'figure',
                        attrs: {
                            style: style,
                            class: imageClass,
                        },
                        content: [
                            {
                                type: 'image',
                                attrs: {
                                    src: image.value.src,
                                    alt: image.value.alt,
                                    width: image.value.width,
                                    height: image.value.height,
                                    dataOriginalWidth: image.value.dataOriginalWidth,
                                    dataOriginalHeight: image.value.dataOriginalHeight,
                                    class: imageClass,
                                },
                            },
                            {
                                type: 'figcaption',
                                content: [
                                    {
                                        type: 'text',
                                        text: '…',
                                    },
                                ],
                            },
                        ],
                    })
                    .run();
            } else {
                // Just insert the image with style.
                editor.value
                    .chain()
                    .focus()
                    .setImage({
                        src: image.value.src,
                        alt: image.value.alt,
                        width: image.value.width,
                        height: image.value.height,
                        dataOriginalWidth: image.value.dataOriginalWidth,
                        dataOriginalHeight: image.value.dataOriginalHeight,
                        style: style,
                        class: imageClass,
                    })
                    .run();
            }
        } else {
            // If the current image is in a figure…
            if (imageCaptioned.value) {
                // Update image attributes
                editor.value
                    .chain()
                    .focus()
                    .setImage({
                        src: image.value.src,
                        alt: image.value.alt,
                        width: image.value.width,
                        height: image.value.height,
                        dataOriginalWidth: image.value.dataOriginalWidth,
                        dataOriginalHeight: image.value.dataOriginalHeight,
                        class: imageClass,
                    })
                    .run();
                // Update figure style and class
                editor.value.commands.updateAttributes('figure', { style: style, class: imageClass });
            } else {
                // Replace the figure with an image.
                editor.value
                    .chain()
                    .focus()
                    .selectParentNode()
                    .insertContent({
                        type: 'image',
                        attrs: {
                            src: image.value.src,
                            alt: image.value.alt,
                            width: image.value.width,
                            height: image.value.height,
                            dataOriginalWidth: image.value.dataOriginalWidth,
                            dataOriginalHeight: image.value.dataOriginalHeight,
                            style: style,
                            class: imageClass,
                        },
                    })
                    .run();
            }
        }
    }
};

const openYoutubeDialog = function () {
    youtubeDialogOpened.value = true;
    youtube.value = {};
};

const addYoutube = function () {
    const url = youtube.value.src;

    // Check for playlist-only URL
    const playlistMatch = url.match(/youtube\.com\/playlist\?list=([a-zA-Z0-9_-]+)/);
    if (playlistMatch) {
        const embedUrl = `https://www.youtube-nocookie.com/embed/videoseries?list=${playlistMatch[1]}`;
        editor.value.commands.insertContent({
            type: 'iframe',
            attrs: {
                src: embedUrl,
                width: 560,
                height: 315,
            },
        });
        return;
    }

    // Check if URL has a playlist parameter - TipTap YouTube extension doesn't preserve it
    const videoWithPlaylistMatch = url.match(/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+).*[&?]list=([a-zA-Z0-9_-]+)/);
    if (videoWithPlaylistMatch) {
        const embedUrl = `https://www.youtube-nocookie.com/embed/${videoWithPlaylistMatch[1]}?list=${videoWithPlaylistMatch[2]}`;
        editor.value.commands.insertContent({
            type: 'iframe',
            attrs: {
                src: embedUrl,
                width: 560,
                height: 315,
            },
        });
        return;
    }

    editor.value.commands.setYoutubeVideo({
        src: url,
    });
};

const openIframeDialog = function () {
    iframeDialogOpened.value = true;
    iframe.value = {};
};

const addIframe = function () {
    editor.value.commands.insertContent({
        type: 'iframe',
        attrs: {
            src: iframe.value.src,
            width: 560,
            height: 315,
        },
    });
};

const openSourceCodeDialog = function () {
    sourceCodeDialogOpened.value = true;
    sourceCodeHtml.value = editor.value.getHTML();
};

const setSourceCode = function () {
    if (sourceCodeHtml.value !== null && sourceCodeHtml.value !== undefined) {
        editor.value.commands.setContent(sourceCodeHtml.value);
    }
};

const vTooltip = {
    mounted: (element) => {
        new Tooltip(element, { delay: { show: 500, hide: 0 }, trigger: 'hover' });
    },
};

onMounted(() => {
    loadPublicCss();
});
</script>
