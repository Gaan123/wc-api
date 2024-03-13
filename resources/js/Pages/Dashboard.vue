<script lang="ts" setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import {computed, onMounted, Ref, UnwrapRef, watch} from "vue";
import { ref } from "vue";
import type { Header, Item,ServerOptions } from "vue3-easy-data-table";
import axios from "axios";


const headers: Header[] = [
    { text: "Id", value: "number" },
    { text: "Order No.", value: "order_key"},
    { text: "Status", value: "status", sortable: true},
    { text: "total", value: "total"},
    { text: "Customer", value: "customer"},
    { text: "customer_note", value: "customer_note", sortable: true},
    { text: "billing", value: "lastAttended"},
    { text: "shipping", value: "country"},
    { text: "created date", value: "created_at"},
    { text: "last modified", value: "updated_at"},
];
const items = ref<Item[]>([]);
const loading = ref(false);
const serverItemsLength = ref(10);
const serverOptions = ref<ServerOptions>({
    page: 1,
    rowsPerPage: 10,
});
const filterOptions = ref({
    status:"",
    order_key:"",
    customer:"",
});
const restApiUrl = computed(() => {
    const { page, rowsPerPage, sortBy, sortType } = serverOptions.value;
    const filters = filterOptions.value;
    let url = `/api/orders?page=${page}&per_page=${rowsPerPage}`;

    if (sortBy && sortType) {
        url += `&sortBy=${sortBy}&sortType=${sortType}`;
    }

    // Add filter parameters to the URL
    for (const [key, value] of Object.entries(filters)) {
        if (value) {
            url += `&${key}=${value}`;
        }
    }

    return url;
});
const syncData=async ()=>{

    loading.value = true;
    const res = await axios.get('/api/orders/sync');
    items.value = res.data.data;
    serverItemsLength.value = res.data.meta.total;
    loading.value = false;
}
watch(
    serverOptions,
    (value) => {
        loadFromServer();
    },
    { deep: true }
);
watch(
    filterOptions,
    (value) => {
        loadFromServer();
    },
    { deep: true }
);
const loadFromServer = async () => {
    loading.value = true;
    const res = await axios.get(restApiUrl.value);
    items.value = res.data.data;
    serverItemsLength.value = res.data.meta.total;
    loading.value = false;
};
onMounted(function(){
    loadFromServer()
})
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>
        <div class="flex flex-row">
            <div class="flex-2 mr-1">
                <span>status:</span>
                <select v-model="filterOptions.status">
                    <option value="">select</option>
                    <option value="pending">pending</option>
                    <option value="processing">processing</option>
                    <option value="completed">completed</option>
                </select>
            </div>

            <div class="flex-2 mr-1">
                <span>Order number: </span>
                <input type="text" v-model="filterOptions.order_key">
            </div>
            <div class="flex-2 mr-1">
                <span>Customer: </span>
                <input type="text" v-model="filterOptions.customer">
            </div>
            <div class="flex-2 my-auto">
                <button @click="syncData" :disabled="loading" class="bg-green-500 text-white px-4 py-2 focus:outline-none focus:bg-green-700 active:bg-green-800">
                    Sync
                </button>
            </div>
        </div>


        <EasyDataTable
            :headers="headers"
            :items="items"
            v-model:server-options="serverOptions"
            :loading="loading"
            :server-items-length="serverItemsLength"
            buttons-pagination
        />
    </AuthenticatedLayout>
</template>
