import * as yup from "yup";

// Схема валидации для универсальной формы
export const universalFormSchema = yup.object({
    // Общие поля
    name: yup
        .string()
        .required("Укажите ваше имя")
        .min(2, "Имя должно содержать минимум 2 символа")
        .max(50, "Имя не должно превышать 50 символов")
        .matches(
            /^[а-яёa-z\s-]+$/i,
            "Имя может содержать только буквы, пробелы и дефисы"
        ),

    phone: yup
        .string()
        .required("Укажите номер телефона")
        .matches(
            /^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$/,
            "Укажите корректный номер телефона в формате +7 (XXX) XXX-XX-XX"
        ),

    comment: yup
        .string()
        .nullable()
        .max(1000, "Комментарий не должен превышать 1000 символов"),

    agreement: yup.boolean().oneOf([true], "Необходимо согласие с условиями"),

    privacy_agreement: yup
        .boolean()
        .oneOf([true], "Необходимо согласие на обработку персональных данных"),

    // Поля для заточки
    tools_count: yup.number().when("serviceType", {
        is: "sharpening",
        then: (schema) =>
            schema
                .required("Укажите количество инструментов")
                .min(1, "Количество должно быть не менее 1")
                .max(50, "Количество не должно превышать 50")
                .integer("Количество должно быть целым числом")
                .transform((value) => (isNaN(value) ? undefined : value)),
        otherwise: (schema) => schema.nullable().transform(() => undefined),
    }),

    tool_type: yup.string().when("serviceType", {
        is: "sharpening",
        then: (schema) =>
            schema
                .required("Выберите тип инструментов")
                .min(2, "Тип инструментов должен содержать минимум 2 символа")
                .max(100, "Тип инструментов не должен превышать 100 символов"),
        otherwise: (schema) => schema.nullable().transform(() => undefined),
    }),

    needs_delivery: yup.boolean(),

    delivery_address: yup.string().when("needs_delivery", {
        is: true,
        then: (schema) =>
            schema
                .required("Укажите адрес доставки")
                .min(10, "Адрес должен содержать минимум 10 символов")
                .max(500, "Адрес не должен превышать 500 символов"),
        otherwise: (schema) => schema.nullable(),
    }),

    // Поля для ремонта
    equipment_name: yup.string().when("serviceType", {
        is: "repair",
        then: (schema) =>
            schema
                .required("Укажите наименование аппарата")
                .min(3, "Наименование должно содержать минимум 3 символа")
                .max(100, "Наименование не должно превышать 100 символов"),
        otherwise: (schema) => schema.nullable(),
    }),

    equipment_type: yup.string().when("serviceType", {
        is: "repair",
        then: (schema) =>
            schema
                .required("Выберите тип оборудования")
                .min(2, "Тип оборудования должен содержать минимум 2 символа")
                .max(100, "Тип оборудования не должен превышать 100 символов"),
        otherwise: (schema) => schema.nullable(),
    }),

    problem_description: yup.string().when("serviceType", {
        is: "repair",
        then: (schema) =>
            schema
                .required("Опишите проблему")
                .min(10, "Описание должно содержать минимум 10 символов")
                .max(1000, "Описание не должно превышать 1000 символов"),
        otherwise: (schema) => schema.nullable(),
    }),

    urgency: yup.string().when("serviceType", {
        is: "repair",
        then: (schema) =>
            schema
                .required("Выберите срочность ремонта")
                .oneOf(["normal", "urgent"], "Неверное значение срочности"),
        otherwise: (schema) => schema.nullable(),
    }),

    // Общие поля
    comment: yup
        .string()
        .max(300, "Комментарий не должен превышать 300 символов"),
});

// Схема валидации для формы ремонта (для обратной совместимости)
export const repairFormSchema = yup.object({
    equipment_name: yup
        .string()
        .required("Укажите наименование аппарата")
        .min(3, "Наименование должно содержать минимум 3 символа")
        .max(100, "Наименование не должно превышать 100 символов"),

    equipment_type: yup.string().required("Выберите тип оборудования"),

    problem_description: yup
        .string()
        .required("Опишите проблему")
        .min(10, "Описание должно содержать минимум 10 символов")
        .max(500, "Описание не должно превышать 500 символов"),

    name: yup
        .string()
        .required("Укажите ваше имя")
        .min(2, "Имя должно содержать минимум 2 символа")
        .max(50, "Имя не должно превышать 50 символов")
        .matches(
            /^[а-яёa-z\s-]+$/i,
            "Имя может содержать только буквы, пробелы и дефисы"
        ),

    phone: yup
        .string()
        .required("Укажите номер телефона")
        .matches(
            /^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$/,
            "Укажите корректный номер телефона в формате +7 (XXX) XXX-XX-XX"
        ),

    urgency: yup
        .string()
        .required("Выберите срочность ремонта")
        .oneOf(["normal", "urgent"], "Неверное значение срочности"),

    agreement: yup.boolean().oneOf([true], "Необходимо согласие с условиями"),

    privacy_agreement: yup
        .boolean()
        .oneOf([true], "Необходимо согласие на обработку персональных данных"),
});

// Схема валидации для формы заточки
export const sharpeningFormSchema = yup.object({
    tools_count: yup
        .number()
        .required("Укажите количество инструментов")
        .min(1, "Количество должно быть не менее 1")
        .max(50, "Количество не должно превышать 50")
        .integer("Количество должно быть целым числом"),

    tool_type: yup
        .string()
        .required("Выберите тип инструментов")
        .oneOf(["manicure", "hair", "grooming"], "Неверный тип инструментов"),

    name: yup
        .string()
        .required("Укажите ваше имя")
        .min(2, "Имя должно содержать минимум 2 символа")
        .max(50, "Имя не должно превышать 50 символов")
        .matches(
            /^[а-яёa-z\s-]+$/i,
            "Имя может содержать только буквы, пробелы и дефисы"
        ),

    phone: yup
        .string()
        .required("Укажите номер телефона")
        .matches(
            /^[\+]?[0-9\s\-\(\)]{10,15}$/,
            "Укажите корректный номер телефона"
        ),

    comment: yup
        .string()
        .max(300, "Комментарий не должен превышать 300 символов"),

    agreement: yup.boolean().oneOf([true], "Необходимо согласие с условиями"),

    privacy_agreement: yup
        .boolean()
        .oneOf([true], "Необходимо согласие на обработку персональных данных"),
});

// Функция для валидации формы с помощью Yup
export const validateForm = async (schema, data) => {
    try {
        await schema.validate(data, { abortEarly: false });
        return { isValid: true, errors: {} };
    } catch (error) {
        const errors = {};
        error.inner.forEach((err) => {
            errors[err.path] = err.message;
        });
        return { isValid: false, errors };
    }
};

// Схема валидации для формы входа клиента
export const loginFormSchema = yup.object({
    phone: yup
        .string()
        .required("Укажите номер телефона")
        .matches(
            /^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$/,
            "Укажите корректный номер телефона в формате +7 (XXX) XXX-XX-XX"
        ),

    password: yup
        .string()
        .required("Укажите пароль")
        .min(6, "Пароль должен содержать минимум 6 символов"),

    remember: yup.boolean(),
});

// Схема валидации для формы регистрации клиента
export const registerFormSchema = yup.object({
    full_name: yup
        .string()
        .required("Укажите ФИО")
        .min(2, "ФИО должно содержать минимум 2 символа")
        .max(100, "ФИО не должно превышать 100 символов")
        .matches(
            /^[а-яёa-z\s-]+$/i,
            "ФИО может содержать только буквы, пробелы и дефисы"
        ),

    phone: yup
        .string()
        .required("Укажите номер телефона")
        .matches(
            /^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$/,
            "Укажите корректный номер телефона в формате +7 (XXX) XXX-XX-XX"
        ),

    password: yup
        .string()
        .required("Укажите пароль")
        .min(6, "Пароль должен содержать минимум 6 символов")
        .max(50, "Пароль не должен превышать 50 символов"),

    password_confirmation: yup
        .string()
        .required("Подтвердите пароль")
        .oneOf([yup.ref("password"), null], "Пароли не совпадают"),

    telegram: yup
        .string()
        .nullable()
        .matches(
            /^@?[a-zA-Z0-9_]{5,32}$/,
            "Укажите корректный username Telegram"
        ),

    birth_date: yup
        .date()
        .nullable()
        .max(new Date(), "Дата рождения не может быть в будущем"),

    delivery_address: yup
        .string()
        .nullable()
        .max(200, "Адрес не должен превышать 200 символов"),
});

// Схема валидации для редактирования профиля клиента
export const profileEditSchema = yup.object({
    full_name: yup
        .string()
        .required("Укажите ФИО")
        .min(2, "ФИО должно содержать минимум 2 символа")
        .max(100, "ФИО не должно превышать 100 символов")
        .matches(
            /^[а-яёa-z\s-]+$/i,
            "ФИО может содержать только буквы, пробелы и дефисы"
        ),

    phone: yup
        .string()
        .required("Укажите номер телефона")
        .matches(
            /^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$/,
            "Укажите корректный номер телефона в формате +7 (XXX) XXX-XX-XX"
        ),

    telegram: yup
        .string()
        .nullable()
        .matches(
            /^@?[a-zA-Z0-9_]{5,32}$/,
            "Укажите корректный username Telegram"
        ),

    birth_date: yup
        .date()
        .nullable()
        .max(new Date(), "Дата рождения не может быть в будущем"),

    delivery_address: yup
        .string()
        .nullable()
        .max(200, "Адрес не должен превышать 200 символов"),
});
