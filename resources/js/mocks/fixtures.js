const now = new Date();
const iso = (offsetDays = 0) =>
    new Date(now.getTime() - offsetDays * 86400000).toISOString();

export const demoClient = {
    id: 1,
    full_name: "Иван Петров",
    phone: "+7 (900) 123-45-67",
    email: "ivan@example.com",
    birth_date: null,
    delivery_address: null,
    requires_password_set: false,
};

export const demoMaster = {
    id: 1,
    name: "Алексей",
    surname: "Смирнов",
    email: "master@zatochka.local",
    phone: "+7 (900) 555-00-11",
    telegram_username: "master_smirnov",
    notifications_enabled: true,
    telegram_verified_at: null,
};

export const demoEquipment = [
    {
        id: 1,
        name: "Газонокосилка Husqvarna LC 353V",
        full_name: "Газонокосилка Husqvarna LC 353V",
        brand: "Husqvarna",
        model: "LC 353V",
        serial_number: [
            { name: "Двигатель", serial_number: "SN-ENG-001" },
            { name: "Редуктор", serial_number: "SN-RED-002" },
        ],
        serial_numbers_display: "SN-ENG-001, SN-RED-002",
    },
    {
        id: 2,
        name: "Триммер STIHL FS 55",
        full_name: "Триммер STIHL FS 55",
        brand: "STIHL",
        model: "FS 55",
        serial_number: [{ serial_number: "ST-55-7788" }],
        serial_numbers_display: "ST-55-7788",
    },
];

export const demoWarehouseItems = [
    {
        id: 1,
        name: "Масло 2T 1л",
        sku: "OIL-2T-1",
        quantity: 24,
        unit: "шт",
        price: 450,
        category_name: "Расходники",
    },
    {
        id: 2,
        name: "Свеча зажигания BPR6ES",
        sku: "SPK-BPR6ES",
        quantity: 18,
        unit: "шт",
        price: 320,
        category_name: "Запчасти",
    },
    {
        id: 3,
        name: "Леска 2.4 мм",
        sku: "LINE-24",
        quantity: 40,
        unit: "шт",
        price: 180,
        category_name: "Расходники",
    },
];

export const createDemoOrders = () => [
    {
        id: 101,
        order_number: "Z-2026-0101",
        service_type: "repair",
        status: "new",
        urgency: "normal",
        price: 3500,
        problem_description: "Не заводится, требуется диагностика карбюратора.",
        created_at: iso(0),
        updated_at: iso(0),
        client: { id: 1, full_name: "Иван Петров", phone: "+7 (900) 123-45-67" },
        branch: { id: 1, name: "Центральный филиал" },
        master: { id: 1, name: "Алексей", surname: "Смирнов" },
        equipment: demoEquipment[0],
        equipment_name: demoEquipment[0].name,
        tools: [],
        is_warranty: false,
        master_id: 1,
        is_deleted: false,
    },
    {
        id: 102,
        order_number: "Z-2026-0102",
        service_type: "sharpening",
        status: "in_work",
        urgency: "urgent",
        price: 1200,
        problem_description: "Заточка 5 ножей для косилки.",
        created_at: iso(1),
        updated_at: iso(0),
        client: { id: 2, full_name: "Мария Сидорова", phone: "+7 (900) 222-33-44" },
        branch: { id: 1, name: "Центральный филиал" },
        master: { id: 1, name: "Алексей", surname: "Смирнов" },
        equipment: null,
        equipment_name: null,
        tools: [
            { id: 1, name: "Нож косилки", quantity: 5 },
        ],
        is_warranty: false,
        master_id: 1,
        is_deleted: false,
    },
    {
        id: 103,
        order_number: "Z-2026-0103",
        service_type: "repair",
        status: "waiting_parts",
        urgency: "normal",
        price: 5800,
        problem_description: "Замена редуктора, ожидаем поставку.",
        created_at: iso(3),
        updated_at: iso(1),
        client: { id: 3, full_name: "Пётр Козлов", phone: "+7 (900) 333-44-55" },
        branch: { id: 1, name: "Центральный филиал" },
        master: { id: 1, name: "Алексей", surname: "Смирнов" },
        equipment: demoEquipment[1],
        equipment_name: demoEquipment[1].name,
        tools: [],
        is_warranty: false,
        master_id: 1,
        is_deleted: false,
    },
    {
        id: 104,
        order_number: "Z-2026-0104",
        service_type: "repair",
        status: "ready",
        urgency: "normal",
        price: 2400,
        problem_description: "Профилактика триммера выполнена.",
        created_at: iso(5),
        updated_at: iso(2),
        client: { id: 4, full_name: "Анна Волкова", phone: "+7 (900) 444-55-66" },
        branch: { id: 1, name: "Центральный филиал" },
        master: { id: 1, name: "Алексей", surname: "Смирнов" },
        equipment: demoEquipment[1],
        equipment_name: demoEquipment[1].name,
        tools: [],
        is_warranty: false,
        master_id: 1,
        is_deleted: false,
    },
    {
        id: 105,
        order_number: "Z-2026-0099",
        service_type: "sharpening",
        status: "issued",
        urgency: "normal",
        price: 900,
        problem_description: "Заточка секатора.",
        created_at: iso(10),
        updated_at: iso(8),
        client: { id: 1, full_name: "Иван Петров", phone: "+7 (900) 123-45-67" },
        branch: { id: 1, name: "Центральный филиал" },
        master: { id: 1, name: "Алексей", surname: "Смирнов" },
        equipment: null,
        tools: [{ id: 2, name: "Секатор", quantity: 1 }],
        is_warranty: false,
        master_id: 1,
        is_deleted: false,
    },
];

export const demoPriceBlocks = {
    sharpening: [
        {
            title: "Ножи и лезвия",
            items: [
                { name: "Нож косилки", price: 250, description: "за 1 шт." },
                { name: "Секатор", price: 300 },
            ],
        },
        {
            title: "Садовый инструмент",
            items: [
                { name: "Топор", price: 400 },
                { name: "Коса", price: 350 },
            ],
        },
    ],
    repair: [
        {
            title: "Диагностика",
            items: [
                { name: "Диагностика бензоинструмента", price: 500 },
                { name: "Диагностика электроинструмента", price: 400 },
            ],
        },
        {
            title: "Типовые работы",
            items: [
                { name: "Замена свечи", price: 300 },
                { name: "Профилактика карбюратора", price: 1200 },
            ],
        },
    ],
};

export const createDemoWorks = () => ({
    102: [
        {
            id: 1,
            order_id: 102,
            description: "Заточка ножей, проверка балансировки",
            equipment_component_name: null,
            equipment_component_serial_number: null,
            created_at: iso(0),
        },
    ],
    103: [
        {
            id: 2,
            order_id: 103,
            description: "Разборка редуктора, дефектовка",
            equipment_component_name: "Редуктор",
            equipment_component_serial_number: "SN-RED-002",
            created_at: iso(2),
        },
    ],
});

export const createDemoMaterials = () => ({
    103: [
        {
            id: 1,
            warehouse_item_id: 2,
            name: "Свеча зажигания BPR6ES",
            quantity: 1,
            unit_price: 320,
            total_price: 320,
        },
    ],
});
