import { ref, computed } from "vue";

const navigationItems = ref([]);
const customContent = ref(null);

export function useHeaderNavigation() {
    const setNavigationItems = (items) => {
        navigationItems.value = items || [];
    };

    const clearNavigationItems = () => {
        navigationItems.value = [];
    };

    const setCustomContent = (content) => {
        customContent.value = content;
    };

    const clearCustomContent = () => {
        customContent.value = null;
    };

    const reset = () => {
        navigationItems.value = [];
        customContent.value = null;
    };

    return {
        navigationItems: computed(() => navigationItems.value),
        customContent: computed(() => customContent.value),
        setNavigationItems,
        clearNavigationItems,
        setCustomContent,
        clearCustomContent,
        reset,
    };
}
