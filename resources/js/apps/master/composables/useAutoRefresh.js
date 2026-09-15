import { onMounted, onUnmounted, ref } from "vue";

/**
 * Composable для автоматического обновления данных с заданным интервалом
 * @param {Function} refreshFn - Функция для обновления данных (может принимать параметр silent)
 * @param {number} intervalMs - Интервал обновления в миллисекундах (по умолчанию 20000 = 20 секунд)
 * @param {boolean} immediate - Выполнить обновление сразу при монтировании (по умолчанию false)
 * @param {boolean} silent - Тихий режим обновления (не передавать флаг silent в refreshFn, по умолчанию true)
 * @returns {Object} { isRefreshing, startRefresh, stopRefresh }
 */
export function useAutoRefresh(
    refreshFn,
    intervalMs = 20000,
    immediate = false,
    silent = true
) {
    const isRefreshing = ref(false);
    let intervalId = null;
    let isFirstLoad = true;

    const startRefresh = () => {
        if (intervalId) {
            return; // Уже запущено
        }

        const refresh = async (isSilent = false) => {
            if (isRefreshing.value) {
                return; // Предотвращаем параллельные обновления
            }

            isRefreshing.value = true;
            try {
                // Если функция принимает параметр silent, передаем его
                if (refreshFn.length > 0) {
                    await refreshFn(isSilent);
                } else {
                    await refreshFn();
                }
            } catch (error) {
                console.error("Auto refresh error:", error);
            } finally {
                isRefreshing.value = false;
            }
        };

        // Выполняем сразу если нужно (первая загрузка - не тихая)
        if (immediate) {
            refresh(false);
            isFirstLoad = false;
        }

        // Устанавливаем интервал (последующие обновления - тихие)
        intervalId = setInterval(() => {
            if (!isFirstLoad) {
                refresh(silent);
            } else {
                isFirstLoad = false;
            }
        }, intervalMs);
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
