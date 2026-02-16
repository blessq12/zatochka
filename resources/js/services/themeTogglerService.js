export default {
    init() {
        // Всегда устанавливаем темную тему при инициализации
        document.documentElement.classList.add("dark");
        localStorage.setItem("theme", "dark");
    },
};
