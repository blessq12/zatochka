/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            fontFamily: {
                jost: ["Jost", "sans-serif"],
            },
            colors: {
                primary: "#1C2526", // темно-синий
                accent: "#F50057", // розовый
                "accent-light": "#FF80AB", // светло-розовый
                gray: {
                    dark: "#333333", // темно-серый
                    DEFAULT: "#666666", // серый
                    light: "#B0BEC5", // светло-серый
                },
                blue: "#0288D1", // синий
            },
            spacing: {
                safe: "15%", // охранное поле
                "ui-sm": "16px", // малый отступ UI
                "ui-md": "20px", // средний отступ UI
                "ui-lg": "24px", // большой отступ UI
            },
            borderRadius: {
                "ui-sm": "4px", // малый радиус
                "ui-md": "6px", // средний радиус
                "ui-lg": "8px", // большой радиус
                card: "12px", // радиус карточек
            },
        },
    },
    plugins: [],
};
