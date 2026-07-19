<script>
import LegalDocumentPage from "../components/Documents/LegalDocumentPage.vue";

export default {
    name: "PrivacyPolicyPage",
    components: { LegalDocumentPage },
};
</script>

<template>
    <LegalDocumentPage
        slug="privacy-policy"
        fallback-title="Политика конфиденциальности"
    />
</template>
