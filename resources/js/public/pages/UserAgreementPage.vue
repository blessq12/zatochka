<script>
import LegalDocumentPage from "../components/Documents/LegalDocumentPage.vue";

export default {
    name: "UserAgreementPage",
    components: { LegalDocumentPage },
};
</script>

<template>
    <LegalDocumentPage
        slug="user-agreement"
        fallback-title="Пользовательское соглашение"
    />
</template>
