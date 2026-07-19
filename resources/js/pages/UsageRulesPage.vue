<script>
import LegalDocumentPage from "../components/Documents/LegalDocumentPage.vue";

export default {
    name: "UsageRulesPage",
    components: { LegalDocumentPage },
};
</script>

<template>
    <LegalDocumentPage
        slug="usage-rules"
        fallback-title="Правила пользования"
    />
</template>
