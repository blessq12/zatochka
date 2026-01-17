import { onMounted, onUnmounted, ref } from "vue";

/**
 * Composable для автоматического обновления данных с заданным интервалом
 * @param {Function} refreshFn - Функция для обновления данных
 * @param {number} intervalMs - Интервал обновления в миллисекундах (по умолчанию 20000 = 20 секунд)
 * @param {boolean} immediate - Выполнить обновление сразу при монтировании (по умолчанию false)
 * @returns {Object} { isRefreshing, startRefresh, stopRefresh }
 */
export function useAutoRefresh(
    refreshFn,
    intervalMs = 20000,
    immediate = false
) {
    const isRefreshing = ref(false);
    let intervalId = null;

    const startRefresh = () => {
        if (intervalId) {
            return; // Уже запущено
        }

        const refresh = async () => {
            if (isRefreshing.value) {
                return; // Предотвращаем параллельные обновления
            }

            isRefreshing.value = true;
            try {
                await refreshFn();
            } catch (error) {
                console.error("Auto refresh error:", error);
            } finally {
                isRefreshing.value = false;
            }
        };

        // Выполняем сразу если нужно
        if (immediate) {
            refresh();
        }

        // Устанавливаем интервал
        intervalId = setInterval(refresh, intervalMs);
    };

    const stopRefresh = () => {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    };

    onMounted(() => {
        startRefresh();
    });

    onUnmounted(() => {
        stopRefresh();
    });

    return {
        isRefreshing,
        startRefresh,
        stopRefresh,
    };
}
