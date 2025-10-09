<template>
    <v-card flat fluid class="rounded-xl bg-white">
        <v-layout flat fluid>
            <NavigationDrawer
                v-if="employeerepository.drawerVisible"
                :location="location"
                :dir="isRtl ? 'rtl' : 'ltr'"
                class="bg-bgproject"
                flat
                :rail="employeerepository.rail"
            />
            <v-main
                class="thepage h-ful scrollabl-content-home rounded-xl bg-bgproject"
            >
                <router-view />
            </v-main>
        </v-layout>
    </v-card>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { useI18n } from "vue-i18n";
import NavigationDrawer from "@/components/NavigationDrawer.vue";
import { useEmployeOfficeRepository } from "@/stores/EmployeeRepository";

const employeerepository = useEmployeOfficeRepository();
const { locale } = useI18n();
const isRtl = ref(locale.value === "fa");
watch(locale, (l) => (isRtl.value = l === "fa"));

const location = computed(() => (locale.value === "fa" ? "right" : "left"));
</script>
