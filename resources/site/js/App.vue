<script>
import { useBootstrapStore } from "./stores/bootstrapStore.js";
import MainLayout from "./components/Layout/MainLayout.vue";
import HomePage from "./pages/HomePage.vue";
import SharpeningPage from "./pages/SharpeningPage.vue";
import RepairPage from "./pages/RepairPage.vue";
import DeliveryPage from "./pages/DeliveryPage.vue";
import ContactsPage from "./pages/ContactsPage.vue";
import WorkSchedulePage from "./pages/WorkSchedulePage.vue";
import PricePage from "./pages/PricePage.vue";
import PrivacyPolicyPage from "./pages/PrivacyPolicyPage.vue";
import UserAgreementPage from "./pages/UserAgreementPage.vue";
import UsageRulesPage from "./pages/UsageRulesPage.vue";
import PageNotFound from "./pages/PageNotFound.vue";

const pages = {
    home: HomePage,
    sharpening: SharpeningPage,
    repair: RepairPage,
    delivery: DeliveryPage,
    contacts: ContactsPage,
    "work-schedule": WorkSchedulePage,
    prices: PricePage,
    "privacy-policy": PrivacyPolicyPage,
    "user-agreement": UserAgreementPage,
    "usage-rules": UsageRulesPage,
    "not-found": PageNotFound,
};

export default {
    name: "PublicApp",
    components: {
        MainLayout,
    },
    setup() {
        const bootstrapStore = useBootstrapStore();
        return { bootstrapStore };
    },
    computed: {
        pageName() {
            return document.body?.dataset?.page || "home";
        },
        pageComponent() {
            return pages[this.pageName] || pages["not-found"];
        },
    },
    async mounted() {
        await this.bootstrapStore.fetchBootstrap();
    },
};
</script>

<template>
    <MainLayout>
        <component :is="pageComponent" />
    </MainLayout>
</template>
