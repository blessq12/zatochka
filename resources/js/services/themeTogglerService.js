export default {
    init() {
        this.setThemeFromLocalStorage();
        this.watchSystemTheme();
    },

    watchSystemTheme() {
        window
            .matchMedia("(prefers-color-scheme: dark)")
            .addEventListener("change", (e) => {
                const savedTheme = localStorage.getItem("theme");
                if (!savedTheme) {
                    if (e.matches) {
                        document.documentElement.classList.add("dark");
                    } else {
                        document.documentElement.classList.remove("dark");
                    }
                }
            });
    },

    toggleTheme() {
        document.documentElement.classList.toggle("dark");
        localStorage.setItem(
            "theme",
            document.documentElement.classList.contains("dark")
                ? "dark"
                : "light"
        );
    },

    setThemeFromLocalStorage() {
        const theme = localStorage.getItem("theme");
        if (theme === "dark") {
            document.documentElement.classList.add("dark");
        } else if (theme === "light") {
            document.documentElement.classList.remove("dark");
        } else {
            if (window.matchMedia("(prefers-color-scheme: dark)").matches) {
                document.documentElement.classList.add("dark");
                localStorage.setItem("theme", "dark");
            } else {
                document.documentElement.classList.remove("dark");
                localStorage.setItem("theme", "light");
            }
        }
    },
};
