<template>
    <div class="filemanager">
        <div :class="['filemanager-header', { 'filemanager-header-bordered': !modal }]">
            <div class="filemanager-header-top">
                <div>
                    <button v-if="path.length > 1" type="button" class="btn-back" @click="openFolder(path[path.length - 2])">
                        <arrow-left-icon size="15" stroke-width="1.75" />
                        <span class="btn-back-label">
                            {{ path[path.length - 2].name }}
                        </span>
                    </button>
                    <h1 v-if="path.length > 0" class="filemanager-header-title">
                        {{ path[path.length - 1].name }}
                    </h1>
                </div>
                <button v-if="modal" class="filemanager-modal-btn-close btn-close" type="button" data-bs-dismiss="modal" :aria-label="t('Close window')"></button>
            </div>
            <div class="filemanager-header-toolbar">
                <button class="btn btn-sm btn-light" type="button" @click="newFolder(folder.id)">
                    <folder-plus-icon size="16" />
                    {{ t('New folder') }}
                </button>
                <div class="btn-group btn-group-sm">
                    <button id="dropdown-action-button" aria-expanded="true" aria-haspopup="true" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" type="button">
                        {{ t('Action') }}
                    </button>

                    <div aria-labelledby="dropdown-action-button" class="dropdown-menu">
                        <button :disabled="selectedItems.length === 0" class="dropdown-item" type="button" @click="deleteSelected">
                            {{ t('Delete') }}
                        </button>
                        <button :disabled="!folder.id || selectedItems.length === 0" class="dropdown-item" type="button" @click="moveToParentFolder()">
                            {{ t('Move to parent folder') }}
                        </button>
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item" disabled="disabled" type="button">
                            {{
                                t('# items selected', selectedItems.length, {
                                    count: selectedItems.length,
                                })
                            }}
                        </button>
                    </div>
                </div>
                <div class="btn-group btn-group-sm">
                    <button :class="{ active: view === 'grid' }" class="btn btn-light" type="button" @click="switchView('grid')">
                        <layout-grid-icon size="16" />
                        {{ t('Grid') }}
                    </button>
                    <button :class="{ active: view === 'list' }" class="btn btn-light" type="button" @click="switchView('list')">
                        <layout-list-icon size="16" />
                        {{ t('List') }}
                    </button>
                </div>
                <div class="d-flex align-items-center">
                    <div v-if="loading" class="spinner-border spinner-border-sm text-dark" role="status">
                        <span class="visually-hidden">{{ t('Loading…') }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2 ms-auto">
                    <div class="input-group input-group-sm mb-0">
                        <div class="input-group-text">
                            <search-icon size="14" />
                        </div>
                        <input id="search" v-model="searchString" class="form-control" type="search" @input="onSearchStringChanged" @search="onSearchCleared" />
                    </div>

                    <button
                        v-if="props.multiple"
                        id="add-selected-files-button"
                        :disabled="selectedFiles.length < 1"
                        class="btn btn-sm btn-primary filemanager-btn-add btn-add-multiple"
                        type="button"
                        @click="addSelectedFiles()"
                    >
                        {{ t('Add selected files') }}
                    </button>

                    <button
                        v-if="props.single"
                        id="add-selected-file-button"
                        :disabled="selectedFiles.length !== 1 || (selectedFiles[0].type !== Array.from(props.type)[0] && props.type !== '')"
                        class="btn btn-sm btn-primary filemanager-btn-add btn-add-single"
                        type="button"
                        @click="addSingleFile(selectedFiles[0])"
                    >
                        {{ t('Add selected file') }}
                    </button>

                    <button
                        v-if="props.selectSingleFile"
                        id="add-selected-file-button"
                        :disabled="selectedFiles.length !== 1 || (selectedFiles[0].type !== Array.from(props.type)[0] && props.type !== '')"
                        class="btn btn-sm btn-primary filemanager-btn-add btn-add-single"
                        type="button"
                        @click="selectSingleFile(selectedFiles[0])"
                    >
                        {{ t('Select file') }}
                    </button>
                </div>

                <button id="upload-files-button" class="btn btn-sm btn-light header-btn-add" v-bind="buttonProps" type="button">
                    <cloud-upload-icon size="16" />
                    {{ t('Upload files') }}
                </button>
            </div>
        </div>

        <div class="filemanager-body">
            <UppyUploader ref="uppyUploaderRef" :folder-id="folder.id" @complete="fetchData"></UppyUploader>
            <div :class="{ 'filemanager-view-list': view === 'list' }" class="filemanager-list" @click="checkNone()">
                <p v-if="filteredItems.length === 0" class="my-3 text-muted">{{ t('The folder is empty.') }}</p>
                <div
                    v-for="item in filteredItems"
                    :id="'item_' + item.id"
                    :key="item.id"
                    :class="{
                        'filemanager-item-selected': selectedItems.indexOf(item) !== -1,
                        'filemanager-item-folder': item.type === 'f',
                        'filemanager-item-file': item.type !== 'f',
                        'filemanager-item-dragging-source': dragging && selectedItems.indexOf(item) !== -1,
                    }"
                    class="filemanager-item filemanager-item-with-name filemanager-item-editable"
                    draggable="true"
                    @click="check(item, $event)"
                    @dblclick="onDoubleClick(item)"
                    @dragend="dragEnd($event)"
                    @dragenter="dragEnter($event)"
                    @dragleave="dragLeave($event)"
                    @dragover="dragOver($event)"
                    @dragstart="dragStart(item, $event)"
                    @drop="drop(item, $event)"
                >
                    <div class="filemanager-item-wrapper">
                        <div v-if="item.type === 'i'" class="filemanager-item-icon">
                            <img class="filemanager-item-image" :src="item.thumb_sm" alt="" />
                        </div>
                        <div v-else :class="'filemanager-item-icon-' + item.type" class="filemanager-item-icon">
                            <file-music-icon v-if="item.type === 'a'" size="72" stroke-width="1.25" />
                            <file-video2-icon v-if="item.type === 'v'" size="72" stroke-width="1.25" />
                            <file-icon v-if="item.type === 'd'" size="72" stroke-width="1.25" />
                            <folder-icon v-if="item.type === 'f'" size="72" stroke-width="1.25" />
                        </div>
                        <div class="filemanager-item-name">
                            {{ item.name }}
                        </div>
                        <a v-if="!modal" :href="'/admin/files/' + item.id + '/edit'" class="filemanager-item-editable-button">
                            {{ t('Edit') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ArrowLeftIcon, CloudUploadIcon, FileIcon, FileMusicIcon, FileVideo2Icon, FolderIcon, FolderPlusIcon, LayoutGridIcon, LayoutListIcon, SearchIcon } from '@lucide/vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

import fetcher from '../admin/fetcher';

import UppyUploader from './UppyUploader.vue';

const { t } = useI18n();

const props = defineProps({
    multiple: {
        type: Boolean,
        default: false,
    },
    single: {
        type: Boolean,
        default: false,
    },
    selectSingleFile: {
        type: Boolean,
        default: false,
    },
    modal: {
        type: Boolean,
        default: true,
    },
    type: {
        type: String,
        default: '',
    },
});
const loadingTimeout = ref(null);
const fetchTimeout = ref(null);
const dragging = ref(false);
const loading = ref(false);
const view = ref('grid');
const uppyUploaderRef = ref(null);
const searchString = ref(null);

const buttonProps = computed(() => {
    return uppyUploaderRef.value?.dropzoneRef?.getButtonProps?.() || {};
});
const selectedItems = ref([]);
const deleteLimit = ref(100);
const urlBase = ref('/api/files');
const folder = ref({ id: '' });
const data = ref({ models: [], path: [] });

if (sessionStorage.getItem('view')) {
    view.value = JSON.parse(sessionStorage.getItem('view'));
}

const url = computed(() => {
    let url = urlBase.value;
    const params = new URLSearchParams();

    if (searchString.value) {
        params.append('search', searchString.value);
    } else {
        if (sessionStorage.getItem('folder')) {
            folder.value = JSON.parse(sessionStorage.getItem('folder'));
        }
        if (folder.value.id !== '') {
            params.append('folder_id', folder.value.id);
        }
    }

    const queryString = params.toString();
    return queryString ? `${url}?${queryString}` : url;
});

const filteredItems = computed(() => {
    return data.value.models;
});

const path = computed(() => {
    return data.value.path;
});

const selectedFiles = computed(() => {
    return selectedItems.value.filter((item) => item.type !== 'f').sort((a, b) => b.name.localeCompare(a.name));
});

fetchData();

function onSearchStringChanged() {
    clearTimeout(fetchTimeout.value);
    fetchTimeout.value = setTimeout(() => {
        selectedItems.value = [];
        fetchData();
    }, 300);
}

function onSearchCleared() {
    searchString.value = null;
    selectedItems.value = [];
    fetchData();
}

async function fetchData() {
    startLoading();
    try {
        const response = await fetcher(url.value);
        if (!response.ok) {
            const responseData = await response.json();
            throw new Error(responseData.message);
        }
        data.value = await response.json();
    } catch (error) {
        alertify.error(error.message || t('Sorry, a network error occurred.'));
    }
    stopLoading();
}

function startLoading() {
    loadingTimeout.value = setTimeout(() => {
        loading.value = true;
    }, 300);
}

function stopLoading() {
    clearTimeout(loadingTimeout.value);
    loading.value = false;
}

function dragStart(item, event) {
    event.dataTransfer.setData('text', '');
    dragging.value = true;
    if (selectedItems.value.indexOf(item) === -1) {
        checkNone();
        selectedItems.value.push(item);
    }
}

function dragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
}

function dragEnd() {
    dragging.value = false;
}

function dragEnter(event) {
    if (event.target.classList.contains('filemanager-item-folder')) {
        event.target.classList.add('filemanager-item-over');
    }
}

function dragLeave(event) {
    event.target.classList.remove('filemanager-item-over');
}

async function drop(targetItem, event) {
    event.target.classList.remove('filemanager-item-over');
    dragging.value = false;

    const ids = [];
    selectedItems.value.forEach((item) => {
        ids.push(item.id);
    });

    if (targetItem.type !== 'f' || ids.indexOf(targetItem.id) !== -1) {
        return;
    }

    for (let i = selectedItems.value.length - 1; i >= 0; i--) {
        const draggedItem = selectedItems[i];
        const index = data.value.models.indexOf(draggedItem);
        data.value.models.splice(index, 1);
    }

    try {
        const response = await fetcher('/api/files/' + ids.join(), {
            method: 'PATCH',
            body: JSON.stringify({ folder_id: targetItem.id }),
        });
        if (!response.ok) {
            const responseData = await response.json();
            throw new Error(responseData.message);
        }
        await fetchData();
    } catch (error) {
        alertify.error(error.message || t('Sorry, an error occurred.'));
    }

    checkNone();
}

async function newFolder(folderId) {
    const name = window.prompt(t('Enter a name for the new folder.'));
    if (!name) {
        return;
    }
    const data = {
        folder_id: folderId,
        type: 'f',
        name: name,
    };
    try {
        const response = await fetcher('/api/files', {
            method: 'POST',
            body: JSON.stringify(data),
        });
        const responseData = await response.json();
        if (!response.ok) {
            throw new Error(responseData.message);
        }
        await fetchData();
    } catch (error) {
        alertify.error(error.message || t('Sorry, an error occurred.'));
    }
}

function check(item, $event) {
    $event.stopPropagation();
    const indexOfLastCheckedItem = data.value.models.indexOf(selectedItems.value[selectedItems.value.length - 1]);
    const index = selectedItems.value.indexOf(item);
    if (!($event.ctrlKey || $event.metaKey || $event.shiftKey)) {
        checkNone();
    }
    if (index !== -1 && ($event.metaKey || $event.ctrlKey)) {
        selectedItems.value.splice(index, 1);
    } else if (selectedItems.value.indexOf(item) === -1) {
        selectedItems.value.push(item);
    }
    if (index === -1) {
        if ($event.shiftKey) {
            const currentItemIndex = data.value.models.indexOf(item);
            data.value.models.forEach((item, index) => {
                if (currentItemIndex > indexOfLastCheckedItem) {
                    if (indexOfLastCheckedItem === -1) {
                        if (index <= currentItemIndex) {
                            selectedItems.value.push(item);
                        }
                    }
                    if (indexOfLastCheckedItem !== -1) {
                        if (index > indexOfLastCheckedItem && index < currentItemIndex) {
                            if (selectedItems.value.indexOf(item) === -1) {
                                selectedItems.value.push(item);
                            }
                        }
                    }
                }
                if (currentItemIndex < indexOfLastCheckedItem) {
                    if (indexOfLastCheckedItem !== -1) {
                        if (index < indexOfLastCheckedItem && index > currentItemIndex) {
                            if (selectedItems.value.indexOf(item) === -1) {
                                selectedItems.value.push(item);
                            }
                        }
                    }
                }
            });
        }
    }
}

async function moveToParentFolder() {
    if (!folder.value.id) {
        return;
    }

    const ids = [],
        models = selectedItems.value,
        number = models.length;

    if (selectedItems.value.length > deleteLimit.value) {
        alertify.error('Too much elements (max ' + deleteLimit.value + ' items.)');
        return false;
    }

    models.forEach((item) => {
        ids.push(item.id);
        const index = data.value.models.indexOf(item);
        data.value.models.splice(index, 1);
    });

    checkNone();

    startLoading();

    try {
        const response = await fetcher('/api/files/' + ids.join(), {
            method: 'PATCH',
            body: JSON.stringify({ folder_id: path.value[path.value.length - 2].id }),
        });
        const responseData = await response.json();
        if (!response.ok) {
            throw new Error(responseData.message);
        }
        if (responseData.number < number) {
            alertify.error(
                t('# files could not be moved.', number - responseData.number, {
                    count: number - responseData.number,
                }),
            );
        }
        if (responseData.number > 0) {
            alertify.success(
                t('# files moved.', responseData.number, {
                    count: responseData.number,
                }),
            );
        }
    } catch (error) {
        alertify.error(error.message || t('Sorry, an error occurred.'));
    }
    stopLoading();
}

function addSingleFile(item) {
    emitter.emit('fileAdded', item);
    closeModal();
}

function selectSingleFile(item) {
    emitter.emit('fileSelected', item);
    closeModal();
}

function addSelectedFiles() {
    const files = [];

    if (selectedFiles.value.length === 0) {
        closeModal();
        return;
    }

    selectedFiles.value.forEach((file) => {
        files.push(file);
    });

    emitter.emit('filesAdded', selectedFiles.value);
    closeModal();
    checkNone();
}

function closeModal() {
    emitter.emit('closeModal');
}

function switchView(viewType) {
    view.value = viewType;
    sessionStorage.setItem('view', JSON.stringify(viewType));
}

function openFolder(folderToOpen) {
    folder.value = folderToOpen;
    searchString.value = null;
    sessionStorage.setItem('folder', JSON.stringify(folder.value));
    fetchData();
    checkNone();
}

function onDoubleClick(item) {
    if (item.type === 'f') {
        openFolder(item);
        return;
    }
    if (props.multiple) {
        addSelectedFiles();
    }
    if ((props.single && item.type === Array.from(props.type)[0]) || props.type === '') {
        addSingleFile(item);
    }
    if ((props.selectSingleFile && item.type === Array.from(props.type)[0]) || props.type === '') {
        selectSingleFile(item);
    }
}

function checkNone() {
    selectedItems.value = [];
}

async function deleteSelected() {
    if (selectedItems.value.length > deleteLimit.value) {
        alertify.error(
            t('Impossible to delete more than # items in one go.', {
                deleteLimit: deleteLimit.value,
            }),
        );
        return false;
    }
    if (
        !window.confirm(
            t('Are you sure you want to delete # items?', selectedItems.value.length, {
                count: selectedItems.value.length,
            }),
        )
    ) {
        return false;
    }

    startLoading();
    const deletePromises = selectedItems.value.map(async (model) => {
        try {
            const response = await fetcher(urlBase.value + '/' + model.id, { method: 'DELETE' });
            if (!response.ok) {
                const responseData = await response.json();
                throw new Error(responseData.message);
            }
            return response;
        } catch (error) {
            alertify.error(t(error.message) || t('Sorry, an error occurred.'));
        }
    });

    const responses = await Promise.all(deletePromises);
    const successes = responses.filter((response) => response && response.ok);
    if (successes.length > 0) {
        alertify.success(
            t('# files deleted.', successes.length, {
                count: successes.length,
            }),
        );
    }
    checkNone();
    stopLoading();
    await fetchData();
}
</script>
