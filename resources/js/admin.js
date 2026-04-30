import Dropdown from 'bootstrap/js/dist/dropdown';
import Tab from 'bootstrap/js/dist/tab';
import Collapse from 'bootstrap/js/dist/collapse';
import Alert from 'bootstrap/js/dist/alert';
import Offcanvas from 'bootstrap/js/dist/offcanvas';
import Modal from 'bootstrap/js/dist/modal';
import Tooltip from 'bootstrap/js/dist/tooltip';
import { browserSupportsWebAuthn, startAuthentication, startRegistration } from '@simplewebauthn/browser';
import alertify from 'alertify.js';
import mitt from 'mitt';
import TomSelect from 'tom-select';
import { createApp } from 'vue';
import { createI18n } from 'vue-i18n';

import en from '../../lang/en.json';
import es from '../../lang/es.json';
import fr from '../../lang/fr.json';

import enableCheckboxesPermissions from './admin/enable-checkboxes-permissions.ts';
import enableSidebarPanelCollapse from './admin/enable-sidebar-panel-collapse.ts';
import enableTagsField from './admin/enable-tags-field.ts';
import enablePreviewWindow from './admin/preview-window.ts';
import enableSetContentLocale from './admin/set-content-locale.ts';
import Slug from './admin/slug.ts';
import FileField from './components/FileField.vue';
import FileManager from './components/FileManager.vue';
import FileManagerContent from './components/FileManagerContent.vue';
import FilesField from './components/FilesField.vue';
import History from './components/History.vue';
import ImageCropper from './components/ImageCropper.vue';
import ItemList from './components/ItemList.vue';
import ItemListCheckbox from './components/ItemListCheckbox.vue';
import ItemListColumnHeader from './components/ItemListColumnHeader.vue';
import ItemListEditButton from './components/ItemListEditButton.vue';
import ItemListShowButton from './components/ItemListShowButton.vue';
import ItemListStatusButton from './components/ItemListStatusButton.vue';
import ItemListTree from './components/ItemListTree.vue';
import TiptapEditor from './components/tiptap/TiptapEditor.vue';
import UserPasskeys from './components/UserPasskeys.vue';
import useHelpers from './composables/useHelpers.ts';

// import Repeater from './components/Repeater.vue';

window.browserSupportsWebAuthn = browserSupportsWebAuthn;
window.startAuthentication = startAuthentication;
window.startRegistration = startRegistration;

const messages = { fr, en, es };
const i18n = new createI18n({
    legacy: false,
    locale: window.TypiCMS.locale,
    messages,
});

const emitter = mitt();
window.emitter = emitter;

const { formatDate, formatDateTime, formatDateRange, $can } = useHelpers();

const app = createApp()
    .component('ItemListColumnHeader', ItemListColumnHeader)
    .component('ItemList', ItemList)
    .component('ItemListTree', ItemListTree)
    .component('ItemListStatusButton', ItemListStatusButton)
    .component('ItemListEditButton', ItemListEditButton)
    .component('ItemListShowButton', ItemListShowButton)
    .component('ItemListCheckbox', ItemListCheckbox)
    .component('History', History)
    .component('FileManager', FileManager)
    .component('FileManagerContent', FileManagerContent)
    .component('FileField', FileField)
    .component('FilesField', FilesField)
    .component('ImageCropper', ImageCropper)
    // .component('Repeater', Repeater)
    .component('UserPasskeys', UserPasskeys)
    .component('TiptapEditor', TiptapEditor)
    .use(i18n);
app.config.globalProperties.emitter = emitter;
app.config.globalProperties.formatDate = formatDate;
app.config.globalProperties.formatDateRange = formatDateRange;
app.config.globalProperties.formatDateTime = formatDateTime;
app.config.globalProperties.$can = $can;
app.mount('#app');

window.alertify = alertify;
window.TomSelect = TomSelect;
enablePreviewWindow();
enableSetContentLocale();
enableSidebarPanelCollapse();
enableCheckboxesPermissions();
enableTagsField();

document.querySelectorAll('[data-slug]').forEach((item) => new Slug(item));
