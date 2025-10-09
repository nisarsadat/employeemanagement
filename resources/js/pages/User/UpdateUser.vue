<template>
    <v-dialog
        transition="dialog-top-transition"
        v-model="PeopleRepository.updateDailog"
        width="800px"
        class="form-wrapper"
    >
        <template v-slot:default="{ isActive }">
            <v-card class="px-3" rounded="xl">
                <v-card-title class="px-6 py-4 d-flex justify-space-between">
                    <h2 class="text-h5 font-weight-bold">{{ t("create") }}</h2>
                    <v-btn
                        variant="text"
                        @click="isActive.value = false"
                        icon="mdi-close"
                        density="comfortable"
                    ></v-btn>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text class="pa-6">
                    <v-form ref="formRef" class="w-full gap-4">
                        <!-- Image Upload -->
                        <div class="d-flex justify-center mb-6">
                            <div
                                class="image-upload-container"
                                @click="openImagePicker"
                                @mouseover="hoverImage = true"
                                @mouseleave="hoverImage = false"
                            >
                                <v-avatar size="120" class="relative">
                                    <v-img
                                        v-if="formData.image"
                                        :src="formData.image"
                                        cover
                                    ></v-img>
                                    <v-icon
                                        v-else
                                        size="48"
                                        color="grey-lighten-1"
                                        >mdi-camera</v-icon
                                    >

                                    <!-- Overlay with actions -->
                                    <div
                                        class="image-overlay d-flex align-center justify-center"
                                        :class="{
                                            'show-overlay':
                                                hoverImage || formData.image,
                                        }"
                                    >
                                        <div
                                            v-if="formData.image"
                                            class="d-flex gap-2"
                                        >
                                            <v-btn
                                                variant="flat"
                                                color="white"
                                                icon="mdi-pencil"
                                                size="x-small"
                                                @click.stop="openImagePicker"
                                                class="action-btn"
                                            ></v-btn>
                                            <v-btn
                                                variant="flat"
                                                color="white"
                                                icon="mdi-delete"
                                                size="x-small"
                                                @click.stop="removeImage"
                                                class="action-btn"
                                            ></v-btn>
                                        </div>
                                        <v-icon v-else size="32" color="white"
                                            >mdi-plus</v-icon
                                        >
                                    </div>
                                </v-avatar>
                                <input
                                    type="file"
                                    ref="imageInput"
                                    accept="image/*"
                                    class="d-none"
                                    @change="handleImageUpload"
                                />
                            </div>
                        </div>

                        <div class="d-flex gap-4" style="width: 100%">
                            <v-text-field
                                v-model="formData.name"
                                variant="outlined"
                                density="compact"
                                :label="t('name')"
                                :rules="[rules.required, rules.counter]"
                                single-line
                                width="50%"
                                bg-color="grey-lighten-5"
                                rounded="lg"
                            ></v-text-field>
                            <v-text-field
                                v-model="formData.username"
                                variant="outlined"
                                density="compact"
                                :label="t('username')"
                                single-line
                                width="50%"
                                bg-color="grey-lighten-5"
                                rounded="lg"
                            ></v-text-field>
                        </div>
                        <div class="d-flex gap-4" style="width: 100%">
                            <v-text-field
                                v-model="formData.phone"
                                variant="outlined"
                                density="compact"
                                :label="t('phone')"
                                :rules="[rules.required, rules.counter]"
                                single-line
                                width="50%"
                                bg-color="grey-lighten-5"
                                rounded="lg"
                            ></v-text-field>
                            <v-text-field
                                v-model="formData.password"
                                variant="outlined"
                                density="compact"
                                :label="t('password')"
                                :rules="[rules.required, rules.password]"
                                single-line
                                width="50%"
                                bg-color="grey-lighten-5"
                                rounded="lg"
                                :type="showPassword ? 'text' : 'password'"
                                :append-inner-icon="
                                    showPassword ? 'mdi-eye-off' : 'mdi-eye'
                                "
                                @click:append-inner="
                                    showPassword = !showPassword
                                "
                            ></v-text-field>
                        </div>
                        <div class="d-flex justify-space-between align-center">
                            <v-switch
                                v-model="formData.status"
                                :label="
                                    formData.status
                                        ? t('active')
                                        : t('inactive')
                                "
                                color="primary"
                                hide-details
                                inset
                                :true-value="1"
                                :false-value="0"
                            ></v-switch>
                        </div>
                    </v-form>
                </v-card-text>
                <div class="justify-start pl-6 pb-6">
                    <v-btn
                        color="primary"
                        variant="flat"
                        @click="createuser"
                        rounded="lg"
                        size="large"
                        :loading="PeopleRepository.loading"
                        >{{ t("submit") }}</v-btn
                    >
                </div>
            </v-card>
        </template>
    </v-dialog>
</template>

<script setup>
import { usePeopleRepository } from "../../stores/PeopleRepository";
import { reactive, ref } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const PeopleRepository = usePeopleRepository();
const formRef = ref(null);
const imageInput = ref(null);
const showPassword = ref(false);
const hoverImage = ref(false);
const selectedImageFile = ref(null);

const formData = reactive({
    name: PeopleRepository.User.name,
    password: PeopleRepository.User.password,
    phone: PeopleRepository.User.phone,
    username: PeopleRepository.User.username,
    image: PeopleRepository.User.image,
});

const openImagePicker = () => {
    imageInput.value.click();
};

const removeImage = () => {
    formData.image = null;
    selectedImageFile.value = null;
};

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate image type
        if (!file.type.match("image.*")) {
            alert("Please select a valid image file");
            return;
        }

        selectedImageFile.value = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            formData.image = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const createuser = async () => {
    const { valid } = await formRef.value.validate();

    if (valid) {
        // Prepare FormData for file upload
        const formDataToSend = new FormData();
        formDataToSend.append("name", formData.name);
        formDataToSend.append("username", formData.username);
        formDataToSend.append("phone", formData.phone);
        formDataToSend.append("password", formData.password);

        if (selectedImageFile.value) {
            formDataToSend.append("image", selectedImageFile.value);
        }

        await PeopleRepository.CreateUser(formDataToSend);
    }
};

const rules = {
    required: (value) => !!value || "Required.",
    counter: (value) => value.length >= 1 || "Min 1 characters",
    email: (value) => {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(value) || "Invalid email";
    },
    password: (value) => value.length >= 6 || "Min 6 characters",
};
</script>

<style scoped>
.form-wrapper :deep(.v-field__outline) {
    --v-field-border-opacity: 0.08;
}

.form-wrapper :deep(.v-input__control) {
    min-height: 44px !important;
}

.d-flex.gap-4 {
    gap: 16px;
}

/* Image Upload Styles */
.image-upload-container {
    position: relative;
    cursor: pointer;
    border: 2px dashed rgba(0, 0, 0, 0.2);
    border-radius: 50%;
    padding: 8px;
    transition: all 0.3s ease;
}

.image-upload-container:hover {
    border-color: rgba(var(--v-theme-primary), 0.8);
    background-color: rgba(var(--v-theme-primary), 0.05);
    transform: scale(1.02);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.4);
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.show-overlay {
    opacity: 1;
}

.action-btn {
    background-color: rgba(255, 255, 255, 0.2) !important;
    backdrop-filter: blur(2px);
}

.action-btn:hover {
    background-color: rgba(255, 255, 255, 0.3) !important;
}

/* Card styling */
.v-card {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

/* Input field styling */
:deep(.v-field) {
    transition: all 0.3s ease;
}

:deep(.v-field:hover) {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Submit button styling */
.v-btn {
    letter-spacing: 0.5px;
    font-weight: 600;
    text-transform: capitalize;
    transition: all 0.3s ease;
}

.v-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.3);
}
</style>
