<template>
    <div class="decorative-logo" ref="logoContainer">
        <img ref="logoImg" src="/logo.svg" alt="Заточка ТСК" class="logo-img" />
    </div>
</template>

<script>
import { gsap } from "gsap";

export default {
    name: "DecorativeLogo",

    mounted() {
        // Ждем загрузки изображения перед инициализацией анимаций
        this.$refs.logoImg.addEventListener("load", () => {
            this.initAnimations();
            this.initScrollListener();
        });

        // Обработчик ошибки загрузки
        this.$refs.logoImg.addEventListener("error", () => {
            console.log("Failed to load logo image");
        });

        // Fallback на случай если изображение уже загружено
        if (this.$refs.logoImg.complete) {
            this.initAnimations();
            this.initScrollListener();
        }
    },

    methods: {
        initAnimations() {
            // Проверяем, что элемент существует и загружен
            if (!this.$refs.logoImg || !this.$refs.logoImg.complete) {
                console.log("Logo image not ready for animation");
                return;
            }

            // Начальная анимация появления
            gsap.fromTo(
                this.$refs.logoImg,
                {
                    scale: 0.6,
                    opacity: 0,
                    x: -30,
                    y: 20,
                },
                {
                    scale: 1,
                    opacity: 0.8,
                    x: 0,
                    y: 0,
                    duration: 2.5,
                    ease: "power4.out",
                }
            );
        },

        initScrollListener() {
            // Проверяем, что элементы существуют
            if (!this.$refs.logoImg || !this.$refs.logoContainer) {
                console.log("Logo elements not found for scroll animation");
                return;
            }

            // Сохраняем ссылку на обработчик для правильного удаления
            this.handleScroll = () => {
                if (!this.$refs.logoImg) return;

                const scrolled = window.pageYOffset;
                const windowHeight = window.innerHeight;
                const scrollProgress = Math.min(scrolled / windowHeight, 1);

                // Плавное перемещение вправо при скролле
                const translateX = scrollProgress * 50;

                // Изменение прозрачности - становится более прозрачным при скролле
                const opacity = 0.8 - scrollProgress * 0.3;

                // Масштабирование - немного уменьшается при скролле
                const scale = 1 - scrollProgress * 0.15;

                gsap.to(this.$refs.logoImg, {
                    x: translateX,
                    opacity: opacity,
                    scale: scale,
                    duration: 0.6,
                    ease: "power2.out",
                });
            };

            // Простая анимация при скролле
            window.addEventListener("scroll", this.handleScroll);

            // Плавающая анимация
            gsap.to(this.$refs.logoContainer, {
                y: -20,
                duration: 3,
                repeat: -1,
                yoyo: true,
                ease: "power1.inOut",
            });
        },
    },

    beforeUnmount() {
        if (this.handleScroll) {
            window.removeEventListener("scroll", this.handleScroll);
        }
    },
};
</script>

<style scoped>
.decorative-logo {
    position: fixed;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
    z-index: 1000;
    pointer-events: none;
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

.decorative-logo:hover {
    opacity: 0.8;
}

.logo-img {
    width: 120px;
    height: 90px;
    filter: drop-shadow(0 8px 16px rgba(99, 102, 241, 0.2)) brightness(0.8)
        contrast(1.2) saturate(0.7);
    transition: all 0.3s ease;
    opacity: 0.8;
}

.decorative-logo:hover .logo-img {
    filter: drop-shadow(0 12px 24px rgba(99, 102, 241, 0.4)) brightness(1.1)
        contrast(1.3) saturate(1.2);
    transform: scale(1.1);
    opacity: 1;
}

/* Темная тема */
.dark .decorative-logo {
    opacity: 0.4;
}

.dark .decorative-logo:hover {
    opacity: 0.7;
}

.dark .logo-img {
    filter: drop-shadow(0 8px 16px rgba(139, 92, 246, 0.3)) brightness(1.2)
        contrast(0.8) saturate(0.5) invert(0.1);
}

.dark .decorative-logo:hover .logo-img {
    filter: drop-shadow(0 12px 24px rgba(139, 92, 246, 0.5)) brightness(1.4)
        contrast(1) saturate(0.8) invert(0.05);
}

/* Адаптивность */
@media (max-width: 768px) {
    .decorative-logo {
        right: 10px;
    }

    .logo-img {
        width: 60px;
        height: 45px;
    }
}
</style>
