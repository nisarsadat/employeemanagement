<template>
    <CreateUser v-if="PeopleRepository.createDailog" />
    <v-container fluid class="mt-12 pl-6">
        <AppBar
            :title="t('User')"
            :subtitle="t('Add a User')"
            @toggle-drawer="drawer = !drawer"
        >
            <template #actions>
                <v-btn icon></v-btn>
            </template>
        </AppBar>
        <v-row class="d-flex align-center py-4">
            <v-col cols="12" md="11">
                <v-text-field
                    v-model="search"
                    :label="t('Search')"
                    append-inner-icon="mdi-search-web"
                    variant="outlined"
                    density="compact"
                    hide-details
                    clearable
                    class="rounded"
                ></v-text-field>
            </v-col>
            <v-col cols="12" md="1">
                <v-btn color="primary" variant="flat" @click="createPopUp">
                    <v-icon left>mdi-plus</v-icon>
                    {{ t("Create User") }}
                </v-btn>
            </v-col>
        </v-row>
        <v-data-table-server
            :dir="isRtl ? 'rtl' : 'ltr'"
            v-model:page="PeopleRepository.page"
            v-model:items-per-page="PeopleRepository.itemsPerPage"
            :headers="headers"
            :items="PeopleRepository.Users"
            :items-length="PeopleRepository.totalItems"
            :loading="PeopleRepository.loading"
            :search="PeopleRepository.Search"
            item-value="id"
            item-key="id"
            class="elevation-0 custom-table"
            :items-per-page-text="t('ItemsPerPage')"
            show-select
            hover
            @update:options="PeopleRepository.FetchUsersData"
        >
            <template v-slot:loading>
                <v-skeleton-loader
                    type="list-item, heading, divider, date-picker-options, date-picker-days, actions"
                    class="elevation-1"
                ></v-skeleton-loader>
            </template>
            <!-- <template v-slot:item.status="{ item }">
                <v-chip
                    :color="getStatusColor(item.status)"
                    variant="outlined"
                    size="small"
                    class="text-capitalize"
                >
                    {{ item.status }}
                </v-chip>
            </template>
            <template v-slot:item.balance="{ item }">
                <span
                    class="font-weight-bold"
                    :class="getBalanceClass(item.balance)"
                >
                    {{ formatCurrency(item.balance, item.currency) }}
                </span>
            </template> -->
            <template v-slot:item.photo="{ item }">
                <v-avatar size="36" class="my-1">
                    <v-img
                        v-if="item.photo"
                        :src="item.photo"
                        cover
                        :alt="item.name"
                    ></v-img>
                    <v-icon v-else size="20" color="grey">
                        mdi-account-circle
                    </v-icon>
                </v-avatar>
            </template>
            <template v-slot:item.actions="{ item }">
                <v-menu>
                    <template v-slot:activator="{ props }">
                        <v-btn
                            icon
                            variant="text"
                            color="grey-darken-1"
                            v-bind="props"
                        >
                            <v-icon>mdi-dots-vertical</v-icon>
                        </v-btn>
                    </template>
                    <v-list density="compact">
                        <v-list-item @click="editItem(item.id)">
                            <template v-slot:prepend>
                                <v-icon color="grey-darken-1"
                                    >mdi-pencil-outline</v-icon
                                >
                            </template>
                            <v-list-item-title>{{
                                t("Edit")
                            }}</v-list-item-title>
                        </v-list-item>
                        <v-list-item @click="deleteItem(item.id)">
                            <template v-slot:prepend>
                                <v-icon color="grey-darken-1"
                                    >mdi-delete-outline</v-icon
                                >
                            </template>
                            <v-list-item-title>{{
                                t("Delete")
                            }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-menu>
            </template>

            <template v-slot:no-data>
                <v-alert
                    type="info"
                    color="primary"
                    variant="tonal"
                    class="ma-4"
                >
                    {{ t("No User found. Create your first User!") }}
                </v-alert>
            </template>
        </v-data-table-server>
    </v-container>
</template>

<script setup>
import { ref, computed } from "vue";
import AppBar from "../../components/AppBar.vue";
import { usePeopleRepository } from "../../stores/PeopleRepository";
const PeopleRepository = usePeopleRepository();
import CreateUser from "./CreateUser.vue";
import { watch } from "vue";
import { useI18n } from "vue-i18n";
const { t, locale } = useI18n();
const isRtl = ref(locale.value === "fa"); // Assuming 'fa' is the code for Dari
watch(locale, (newLocale) => {
    isRtl.value = newLocale === "fa";
});
const headers = [
    {
        title: t("Select"),
        key: "data-table-select",
        sortable: false,
        align: "end",
    },
    { title: t("Photo"), key: "photo", sortable: true },
    { title: t("UserName"), key: "name", sortable: true },
    { title: t("Email"), key: "email", sortable: true, align: "end" },
    { title: t("Status"), key: "status", sortable: true, align: "end" },
    { title: t("Action"), key: "actions", sortable: false, align: "end" },
];

// const formatCurrency = (amount, currency) => {
//     return `${currency.symbol} ${Math.abs(amount).toFixed(2)}`;
// };
// const getStatusColor = (status) => {
//     return status === "active" ? "success" : "error";
// };
// const getBalanceClass = (balance) => {
//     return balance < 0 ? "text-error" : "text-success";
// };
const createPopUp = () => {
    PeopleRepository.createDailog = true;
    console.log("Create account clicked");
};

const createPopUpTransfer = () => {
    // MoneyAccountRepository.createDailogTransfer = true
    console.log("Transfer clicked");
};
const editItem = (id) => {
    console.log("Edit item:", id);
};
const deleteItem = (id) => {
    PeopleRepository.DeleteUser(id);
    console.log("Delete item:", id);
};
</script>

<style scoped>
.v-data-table {
    border-radius: 4px;
    overflow: hidden;
}
.v-data-table-header {
    background-color: rgba(0, 0, 0, 0.02);
}
.v-data-table-header th {
    font-weight: 600;
    letter-spacing: 0.5px;
}
@media (max-width: 960px) {
    .v-col-md-8 {
        margin-top: 16px;
    }
}
.v-chip {
    border-width: 1px;
    font-weight: 500;
}
.text-success {
    color: #4caf50;
}
.text-error {
    color: #f44336;
}
.v-btn--icon {
    transition: all 0.2s ease;
}
.v-btn--icon:hover {
    transform: scale(1.1);
}
</style>
