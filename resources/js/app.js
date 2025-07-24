/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import "@mdi/font/css/materialdesignicons.min.css";
import { createApp } from "vue";
import "./bootstrap";
/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({});

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

Object.entries(import.meta.glob("./**/*.vue", { eager: true })).forEach(
    ([path, definition]) => {
        app.component(
            path
                .split("/")
                .pop()
                .replace(/\.\w+$/, ""),
            definition.default
        );
    }
);

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.mount("#app");

// Мобильное меню
const mobileMenuBtn = document.querySelector(".mobile-menu-btn");
const mobileMenu = document.querySelector(".nav-menu");

if (mobileMenuBtn && mobileMenu) {
    mobileMenuBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("active");
    });
}

// Эффект скролла для навбара
const navbar = document.querySelector(".navbar");
let lastScroll = 0;

window.addEventListener("scroll", () => {
    if (!navbar) return;

    const currentScroll = window.pageYOffset;

    // Добавляем тень при скролле
    if (currentScroll > 0) {
        navbar.classList.add("scrolled");
    } else {
        navbar.classList.remove("scrolled");
    }

    lastScroll = currentScroll;
});

// Плавный скролл для якорных ссылок
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute("href"));
        if (target) {
            target.scrollIntoView({
                behavior: "smooth",
            });
            // Закрываем мобильное меню при клике
            if (mobileMenu) {
                mobileMenu.classList.remove("active");
            }
        }
    });
});

// Обработка клика по scroll-down
const scrollDownBtn = document.querySelector(".scroll-down");
if (scrollDownBtn) {
    scrollDownBtn.addEventListener("click", () => {
        const nextSection = document.querySelector("section:nth-of-type(2)");
        if (nextSection) {
            nextSection.scrollIntoView({
                behavior: "smooth",
            });
        }
    });
}

// Скрываем scroll-down при скролле
window.addEventListener("scroll", () => {
    if (!scrollDownBtn) return;

    if (window.pageYOffset > 100) {
        scrollDownBtn.style.opacity = "0";
        scrollDownBtn.style.pointerEvents = "none";
    } else {
        scrollDownBtn.style.opacity = "1";
        scrollDownBtn.style.pointerEvents = "auto";
    }
});

// Слайдер отзывов
const testimonialSlider = {
    currentSlide: 0,
    slides: document.querySelectorAll(".testimonial-slide"),
    dots: document.querySelectorAll(".slider-dot"),
    track: document.querySelector(".testimonials-track"),

    init() {
        if (!this.slides.length || !this.dots.length || !this.track) return;

        // Обработчики для точек
        this.dots.forEach((dot, index) => {
            dot.addEventListener("click", () => this.goToSlide(index));
        });

        // Автопереключение слайдов
        setInterval(() => {
            this.nextSlide();
        }, 5000);

        // Свайп на мобильных
        let touchStartX = 0;
        let touchEndX = 0;

        this.track.addEventListener("touchstart", (e) => {
            touchStartX = e.touches[0].clientX;
        });

        this.track.addEventListener("touchend", (e) => {
            touchEndX = e.changedTouches[0].clientX;
            if (touchStartX - touchEndX > 50) {
                this.nextSlide();
            } else if (touchEndX - touchStartX > 50) {
                this.prevSlide();
            }
        });
    },

    goToSlide(index) {
        this.currentSlide = index;
        this.updateSlider();
    },

    nextSlide() {
        this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        this.updateSlider();
    },

    prevSlide() {
        this.currentSlide =
            (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        this.updateSlider();
    },

    updateSlider() {
        // Обновляем активный класс для слайдов
        this.slides.forEach((slide, index) => {
            if (index === this.currentSlide) {
                slide.classList.add("active");
            } else {
                slide.classList.remove("active");
            }
        });

        // Обновляем точки
        this.dots.forEach((dot, index) => {
            if (index === this.currentSlide) {
                dot.classList.add("active");
            } else {
                dot.classList.remove("active");
            }
        });

        // Анимируем трек
        this.track.style.transform = `translateX(-${this.currentSlide * 100}%)`;
    },
};

// Инициализация слайдера
testimonialSlider.init();

// Форма заказа
const orderForm = {
    init() {
        const form = document.querySelector(".order-form");
        const serviceOptions = document.querySelectorAll(".service-option");
        const phoneInput = document.querySelector('input[name="phone"]');

        if (!form || !serviceOptions.length || !phoneInput) return;

        // Обработка выбора услуги
        serviceOptions.forEach((option) => {
            const radio = option.querySelector('input[type="radio"]');

            option.addEventListener("click", () => {
                // Снимаем выделение со всех опций
                serviceOptions.forEach((opt) =>
                    opt.classList.remove("selected")
                );
                // Выделяем выбранную опцию
                option.classList.add("selected");
                radio.checked = true;
            });
        });

        // Маска для телефона
        let phoneMask = "+7 (___) ___-__-__";
        let phonePattern = /\d/g;
        let phoneValue = "";

        phoneInput.addEventListener("input", (e) => {
            phoneValue = e.target.value.replace(/\D/g, "");
            let formattedValue = phoneMask;

            for (let i = 0; i < phoneValue.length && i < 10; i++) {
                formattedValue = formattedValue.replace("_", phoneValue[i]);
            }

            e.target.value = formattedValue;
        });

        phoneInput.addEventListener("keydown", (e) => {
            if (e.key === "Backspace" && phoneValue.length > 0) {
                phoneValue = phoneValue.slice(0, -1);
                let formattedValue = phoneMask;

                for (let i = 0; i < phoneValue.length && i < 10; i++) {
                    formattedValue = formattedValue.replace("_", phoneValue[i]);
                }

                e.target.value = formattedValue;
                e.preventDefault();
            }
        });

        // Отправка формы
        form.addEventListener("submit", (e) => {
            e.preventDefault();

            // Валидация
            let isValid = true;
            const requiredFields = form.querySelectorAll(
                "input[required], select[required], textarea[required]"
            );

            requiredFields.forEach((field) => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add("error");
                } else {
                    field.classList.remove("error");
                }
            });

            if (!isValid) {
                alert("Пожалуйста, заполните все обязательные поля");
                return;
            }

            // Здесь будет отправка формы на сервер
            console.log("Форма отправлена", {
                service: form.querySelector('input[name="service"]:checked')
                    ?.value,
                quantity: form.querySelector('select[name="quantity"]').value,
                name: form.querySelector('input[name="name"]').value,
                phone: phoneValue,
                comment: form.querySelector('textarea[name="comment"]').value,
            });

            // Очистка формы
            form.reset();
            serviceOptions.forEach((opt) => opt.classList.remove("selected"));
        });
    },
};

// Инициализация формы
orderForm.init();

// FAQ аккордеон
const faqAccordion = {
    init() {
        const faqItems = document.querySelectorAll(".faq-item");
        if (!faqItems.length) return;

        faqItems.forEach((item) => {
            const question = item.querySelector(".faq-question");

            question.addEventListener("click", () => {
                const isActive = item.classList.contains("active");

                // Закрываем все активные элементы
                faqItems.forEach((otherItem) => {
                    if (otherItem !== item) {
                        otherItem.classList.remove("active");
                    }
                });

                // Переключаем текущий элемент
                item.classList.toggle("active");

                // Плавная анимация скролла при открытии на мобильных
                if (!isActive && window.innerWidth < 768) {
                    const offset = item.offsetTop - 20;
                    window.scrollTo({
                        top: offset,
                        behavior: "smooth",
                    });
                }
            });
        });

        // Открываем первый вопрос по умолчанию
        if (faqItems[0]) {
            faqItems[0].classList.add("active");
        }
    },
};

// Инициализация FAQ
faqAccordion.init();

// Форма обратной связи
const contactForm = {
    init() {
        const form = document.querySelector(".contact-form form");
        if (!form) return;

        form.addEventListener("submit", (e) => {
            e.preventDefault();

            // Валидация
            let isValid = true;
            const requiredFields = form.querySelectorAll("[required]");

            requiredFields.forEach((field) => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add("error");
                    this.showError(field);
                } else {
                    field.classList.remove("error");
                    this.hideError(field);
                }

                // Дополнительная валидация email
                if (
                    field.type === "email" &&
                    !this.validateEmail(field.value)
                ) {
                    isValid = false;
                    field.classList.add("error");
                    this.showError(
                        field,
                        "Пожалуйста, введите корректный email"
                    );
                }
            });

            if (!isValid) return;

            // Здесь будет отправка формы на сервер
            console.log("Сообщение отправлено", {
                name: form.querySelector("#contact-name").value,
                email: form.querySelector("#contact-email").value,
                message: form.querySelector("#contact-message").value,
            });

            // Очистка формы и показ сообщения об успехе
            form.reset();
            this.showSuccess();
        });

        // Убираем ошибки при вводе
        form.querySelectorAll(".form-input").forEach((input) => {
            input.addEventListener("input", () => {
                input.classList.remove("error");
                this.hideError(input);
            });
        });
    },

    validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    showError(field, message) {
        let errorDiv = field.parentElement.querySelector(".error-message");
        if (!errorDiv) {
            errorDiv = document.createElement("div");
            errorDiv.className = "error-message text-sm text-red-500 mt-1";
            field.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = message || "Это поле обязательно для заполнения";
    },

    hideError(field) {
        const errorDiv = field.parentElement.querySelector(".error-message");
        if (errorDiv) errorDiv.remove();
    },

    showSuccess() {
        const successDiv = document.createElement("div");
        successDiv.className =
            "success-message bg-green-100 text-green-700 p-4 rounded-lg mt-4";
        successDiv.textContent =
            "Спасибо! Ваше сообщение отправлено. Мы свяжемся с вами в ближайшее время.";

        const form = document.querySelector(".contact-form form");
        form.parentElement.appendChild(successDiv);

        setTimeout(() => {
            successDiv.remove();
        }, 5000);
    },
};

// Инициализация формы обратной связи
contactForm.init();
