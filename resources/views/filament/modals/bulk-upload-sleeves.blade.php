<style>
    .bfu-dropzone {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .bfu-dropzone:hover {
        border-color: #818cf8 !important;
        background: linear-gradient(135deg, #f5f3ff 0%, #eef2ff 100%) !important;
    }
    .bfu-icon-circle {
        transition: transform 0.25s ease;
    }
    .bfu-dropzone:hover .bfu-icon-circle {
        transform: scale(1.08);
    }
    .bfu-btn-primary {
        transition: all 0.2s ease;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
    }
    .bfu-btn-primary:hover:not(:disabled) {
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
        transform: translateY(-1px);
    }
    .bfu-btn-primary:active:not(:disabled) {
        transform: translateY(0);
    }
    .bfu-btn-ghost {
        transition: all 0.2s ease;
    }
    .bfu-btn-ghost:hover:not(:disabled) {
        background: #f9fafb !important;
        border-color: #9ca3af !important;
    }
    .bfu-row {
        transition: all 0.2s ease;
        animation: bfu-fade-in 0.3s ease;
    }
    @keyframes bfu-fade-in {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .bfu-pulse {
        animation: bfu-pulse 1.4s ease-in-out infinite;
    }
    @keyframes bfu-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .bfu-progress-fill {
        transition: width 0.3s ease;
    }
</style>

<div
    x-data="{
        cloudName: @js($cloudName),
        uploadPreset: @js($uploadPreset),
        files: [],
        dragging: false,
        uploading: false,
        doneCount: 0,
        allDone: false,

        handleDrop(e) {
            this.dragging = false;
            this.handleFiles(e.dataTransfer.files);
        },

        handleFiles(fileList) {
            for (const file of fileList) {
                this.files.push({ file: file, name: file.name, status: 'pending', url: null });
            }
        },

        async uploadAll() {
            if (!this.cloudName || !this.uploadPreset) {
                alert('Cloudinary cloud name or upload preset is not configured.');
                return;
            }

            this.uploading = true;
            this.doneCount = 0;
            this.allDone = false;

            for (const f of this.files) {
                if (f.status === 'done') {
                    this.doneCount++;
                    continue;
                }

                f.status = 'uploading';

                try {
                    const formData = new FormData();
                    formData.append('file', f.file);
                    formData.append('upload_preset', this.uploadPreset);

                    const res = await fetch('https://api.cloudinary.com/v1_1/' + this.cloudName + '/image/upload', {
                        method: 'POST',
                        body: formData,
                    });

                    if (!res.ok) {
                        const errorBody = await res.text();
                        console.error('Cloudinary upload failed for', f.name, ':', errorBody);
                        throw new Error('Upload failed: ' + errorBody);
                    }

                    const data = await res.json();
                    f.url = data.secure_url;
                    f.status = 'done';
                } catch (e) {
                    f.status = 'error';
                }

                this.doneCount++;
            }

            const payload = this.files
                .filter(f => f.status === 'done')
                .map(f => ({ name: f.name, url: f.url }));

            this.uploading = false;

            if (payload.length > 0) {
                await this.$wire.processUploads(payload);
                this.allDone = true;
            }
        },
    }"
    style="display: flex; flex-direction: column; gap: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;"
>
    <div
        class="bfu-dropzone"
        x-on:dragover.prevent="dragging = true"
        x-on:dragleave.prevent="dragging = false"
        x-on:drop.prevent="handleDrop($event)"
        x-on:click="$refs.fileInput.click()"
        x-bind:style="dragging
            ? 'border: 2px dashed #6366f1; background: linear-gradient(135deg, #f5f3ff 0%, #eef2ff 100%); border-radius: 20px; padding: 40px 32px; text-align: center; cursor: pointer;'
            : 'border: 2px dashed #e0e0ea; background: linear-gradient(135deg, #fafafa 0%, #f7f7fb 100%); border-radius: 20px; padding: 40px 32px; text-align: center; cursor: pointer;'"
    >
        <div style="display: flex; flex-direction: column; align-items: center; gap: 14px;">
            <div
                class="bfu-icon-circle"
                style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);"
            >
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
            </div>

            <div>
                <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">Upload Sleeve Images</h3>
                <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0 0;">Drag & drop images here, or click to browse</p>
            </div>

            <div style="font-size: 11px; font-weight: 500; color: #6366f1; background: white; border: 1px solid #e0e0ea; border-radius: 999px; padding: 6px 14px; letter-spacing: 0.02em;">
                Name_Fabric_Default.png
            </div>
        </div>

        <input
            type="file"
            x-ref="fileInput"
            multiple
            accept="image/*"
            style="display: none;"
            x-on:change="handleFiles($event.target.files); $event.target.value = ''"
        >
    </div>

    <div x-show="files.length > 0" style="display: flex; flex-direction: column; gap: 8px;">
        <div x-show="uploading || allDone" style="width: 100%; height: 6px; background: #f0f0f5; border-radius: 999px; overflow: hidden;">
            <div
                class="bfu-progress-fill"
                x-bind:style="'height: 100%; border-radius: 999px; background: linear-gradient(90deg, #818cf8, #6366f1); width: ' + (files.length ? Math.round((doneCount / files.length) * 100) : 0) + '%;'"
            ></div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 6px; max-height: 240px; overflow-y: auto; padding-right: 2px;">
            <template x-for="(f, index) in files" :key="index">
                <div
                    class="bfu-row"
                    style="display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 13px; background: white; border: 1px solid #ececf2; border-radius: 10px; padding: 10px 14px;"
                >
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <span x-text="f.name" style="color: #374151; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                    </div>

                    <span
                        x-bind:class="f.status === 'uploading' ? 'bfu-pulse' : ''"
                        x-text="f.status === 'pending' ? 'Pending' : f.status === 'uploading' ? 'Uploading' : f.status === 'done' ? 'Done' : 'Error'"
                        x-bind:style="{
                            pending: 'flex-shrink: 0; font-size: 11px; font-weight: 600; color: #9ca3af; background: #f3f4f6; padding: 3px 10px; border-radius: 999px;',
                            uploading: 'flex-shrink: 0; font-size: 11px; font-weight: 600; color: #6366f1; background: #eef2ff; padding: 3px 10px; border-radius: 999px;',
                            done: 'flex-shrink: 0; font-size: 11px; font-weight: 600; color: #15803d; background: #dcfce7; padding: 3px 10px; border-radius: 999px;',
                            error: 'flex-shrink: 0; font-size: 11px; font-weight: 600; color: #dc2626; background: #fee2e2; padding: 3px 10px; border-radius: 999px;',
                        }[f.status]"
                    ></span>
                </div>
            </template>
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 10px;">
        <button
            type="button"
            class="bfu-btn-primary"
            x-on:click="uploadAll()"
            x-bind:disabled="uploading || files.length === 0"
            x-bind:style="(uploading || files.length === 0)
                ? 'background: #c7c8f5; color: white; border: none; border-radius: 10px; padding: 10px 20px; font-size: 14px; font-weight: 600; cursor: not-allowed; box-shadow: none;'
                : 'background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%); color: white; border: none; border-radius: 10px; padding: 10px 20px; font-size: 14px; font-weight: 600; cursor: pointer;'"
        >
            <span x-show="!uploading">Upload All</span>
            <span x-show="uploading">Uploading… <span x-text="doneCount"></span>/<span x-text="files.length"></span></span>
        </button>

        <button
            type="button"
            class="bfu-btn-ghost"
            x-on:click="files = []"
            x-bind:disabled="uploading"
            style="background: white; color: #374151; border: 1px solid #e0e0ea; border-radius: 10px; padding: 10px 20px; font-size: 14px; font-weight: 500; cursor: pointer;"
        >
            Clear
        </button>

        <div x-show="allDone" style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: #15803d;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span>Done — you can close this now</span>
        </div>
    </div>
</div>
