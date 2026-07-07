<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validatsiya tili satrlari
    |--------------------------------------------------------------------------
    |
    | Quyidagi satrlar Laravel validator klassi tomonidan ishlatiladigan
    | standart xato xabarlari. Ba'zi qoidalarda bir nechta variant bor
    | (masalan: size, numeric, string, file, array).
    |
    */

    'accepted'             => ":attribute qabul qilinishi kerak.",
    'accepted_if'          => ":other :value bo'lganda, :attribute qabul qilinishi kerak.",
    'active_url'           => ":attribute yaroqli manzil emas.",
    'after'                => ":attribute :date dan keyingi sana bo'lishi kerak.",
    'after_or_equal'       => ":attribute :date sanasi yoki undan keyingi sana bo'lishi kerak.",
    'alpha'                => ":attribute faqat harflardan iborat bo'lishi kerak.",
    'alpha_dash'           => ":attribute faqat harflar, raqamlar, tire va pastki chiziqlardan iborat bo'lishi kerak.",
    'alpha_num'            => ":attribute faqat harflar va raqamlardan iborat bo'lishi kerak.",
    'array'                => ":attribute massiv (array) bo'lishi kerak.",
    'ascii'                => ":attribute faqat bir baytli alifbo-raqamli belgilar va belgilardan iborat bo'lishi kerak.",
    'before'               => ":attribute :date dan oldingi sana bo'lishi kerak.",
    'before_or_equal'      => ":attribute :date sanasi yoki undan oldingi sana bo'lishi kerak.",
    'between'              => [
        'array'   => ":attribute :min dan :max gacha elementga ega bo'lishi kerak.",
        'file'    => ":attribute :min dan :max kilobaytgacha bo'lishi kerak.",
        'numeric' => ":attribute :min dan :max gacha bo'lishi kerak.",
        'string'  => ":attribute :min dan :max belgigacha bo'lishi kerak.",
    ],
    'boolean'              => ":attribute maydoni true yoki false bo'lishi kerak.",
    'confirmed'            => ":attribute tasdiqlash mos kelmadi.",
    'current_password'     => "Parol noto'g'ri.",
    'date'                 => ":attribute yaroqli sana emas.",
    'date_equals'          => ":attribute :date sanasiga teng bo'lishi kerak.",
    'date_format'          => ":attribute :format formatiga mos kelmaydi.",
    'decimal'              => ":attribute :decimal xonali kasr bo'lishi kerak.",
    'declined'             => ":attribute rad etilgan bo'lishi kerak.",
    'declined_if'          => ":other :value bo'lganda, :attribute rad etilgan bo'lishi kerak.",
    'different'            => ":attribute va :other bir xil bo'lmasligi kerak.",
    'digits'               => ":attribute :digits ta raqamdan iborat bo'lishi kerak.",
    'digits_between'       => ":attribute :min dan :max tagacha raqamdan iborat bo'lishi kerak.",
    'dimensions'           => ":attribute rasm o'lchamlari noto'g'ri.",
    'distinct'             => ":attribute qiymati takrorlanmoqda.",
    'doesnt_end_with'      => ":attribute quyidagilardan biri bilan tugamasligi kerak: :values.",
    'doesnt_start_with'    => ":attribute quyidagilardan biri bilan boshlanmasligi kerak: :values.",
    'email'                => ":attribute yaroqli elektron pochta manzili bo'lishi kerak.",
    'ends_with'            => ":attribute quyidagilardan biri bilan tugashi kerak: :values.",
    'enum'                 => "Tanlangan :attribute yaroqsiz.",
    'exists'               => "Tanlangan :attribute yaroqsiz.",
    'extensions'           => ":attribute quyidagi kengaytmalardan biriga ega bo'lishi kerak: :values.",
    'file'                 => ":attribute fayl bo'lishi kerak.",
    'filled'               => ":attribute maydoni to'ldirilishi shart.",
    'gt'                   => [
        'array'   => ":attribute :value tadan ko'p elementga ega bo'lishi kerak.",
        'file'    => ":attribute :value kilobaytdan katta bo'lishi kerak.",
        'numeric' => ":attribute :value dan katta bo'lishi kerak.",
        'string'  => ":attribute :value belgidan uzun bo'lishi kerak.",
    ],
    'gte'                  => [
        'array'   => ":attribute :value ta yoki undan ko'p elementga ega bo'lishi kerak.",
        'file'    => ":attribute :value kilobayt yoki undan katta bo'lishi kerak.",
        'numeric' => ":attribute :value ga teng yoki undan katta bo'lishi kerak.",
        'string'  => ":attribute :value belgi yoki undan uzun bo'lishi kerak.",
    ],
    'image'                => ":attribute rasm bo'lishi kerak.",
    'in'                   => "Tanlangan :attribute yaroqsiz.",
    'in_array'             => ":attribute maydoni :other ichida mavjud emas.",
    'integer'              => ":attribute butun son bo'lishi kerak.",
    'ip'                   => ":attribute yaroqli IP manzil bo'lishi kerak.",
    'ipv4'                 => ":attribute yaroqli IPv4 manzil bo'lishi kerak.",
    'ipv6'                 => ":attribute yaroqli IPv6 manzil bo'lishi kerak.",
    'json'                 => ":attribute yaroqli JSON matni bo'lishi kerak.",
    'lowercase'            => ":attribute kichik harflardan iborat bo'lishi kerak.",
    'lt'                   => [
        'array'   => ":attribute :value tadan kam elementga ega bo'lishi kerak.",
        'file'    => ":attribute :value kilobaytdan kichik bo'lishi kerak.",
        'numeric' => ":attribute :value dan kichik bo'lishi kerak.",
        'string'  => ":attribute :value belgidan qisqa bo'lishi kerak.",
    ],
    'lte'                  => [
        'array'   => ":attribute :value tadan ortiq elementga ega bo'lmasligi kerak.",
        'file'    => ":attribute :value kilobaytdan katta bo'lmasligi kerak.",
        'numeric' => ":attribute :value dan katta bo'lmasligi kerak.",
        'string'  => ":attribute :value belgidan uzun bo'lmasligi kerak.",
    ],
    'mac_address'          => ":attribute yaroqli MAC manzil bo'lishi kerak.",
    'max'                  => [
        'array'   => ":attribute :max tadan ortiq elementga ega bo'lmasligi kerak.",
        'file'    => ":attribute :max kilobaytdan katta bo'lmasligi kerak.",
        'numeric' => ":attribute :max dan katta bo'lmasligi kerak.",
        'string'  => ":attribute :max ta belgidan oshmasligi kerak.",
    ],
    'max_digits'           => ":attribute :max ta raqamdan oshmasligi kerak.",
    'mimes'                => ":attribute quyidagi turdagi fayl bo'lishi kerak: :values.",
    'mimetypes'            => ":attribute quyidagi turdagi fayl bo'lishi kerak: :values.",
    'min'                  => [
        'array'   => ":attribute kamida :min ta elementga ega bo'lishi kerak.",
        'file'    => ":attribute kamida :min kilobayt bo'lishi kerak.",
        'numeric' => ":attribute kamida :min bo'lishi kerak.",
        'string'  => ":attribute kamida :min ta belgidan iborat bo'lishi kerak.",
    ],
    'min_digits'           => ":attribute kamida :min ta raqamdan iborat bo'lishi kerak.",
    'missing'              => ":attribute maydoni bo'lmasligi kerak.",
    'missing_if'           => ":other :value bo'lganda, :attribute maydoni bo'lmasligi kerak.",
    'missing_unless'       => ":other :value bo'lmasa, :attribute maydoni bo'lmasligi kerak.",
    'missing_with'         => ":values mavjud bo'lganda, :attribute maydoni bo'lmasligi kerak.",
    'missing_with_all'     => ":values mavjud bo'lganda, :attribute maydoni bo'lmasligi kerak.",
    'multiple_of'          => ":attribute :value ning karrali bo'lishi kerak.",
    'not_in'               => "Tanlangan :attribute yaroqsiz.",
    'not_regex'            => ":attribute formati yaroqsiz.",
    'numeric'              => ":attribute son bo'lishi kerak.",
    'password'             => [
        'letters'       => ":attribute kamida bitta harfdan iborat bo'lishi kerak.",
        'mixed'         => ":attribute kamida bitta katta va bitta kichik harfdan iborat bo'lishi kerak.",
        'numbers'       => ":attribute kamida bitta raqamdan iborat bo'lishi kerak.",
        'symbols'       => ":attribute kamida bitta belgidan iborat bo'lishi kerak.",
        'uncompromised' => "Kiritilgan :attribute ma'lumotlar sizib chiqishida topilgan. Boshqa :attribute tanlang.",
    ],
    'present'              => ":attribute maydoni mavjud bo'lishi kerak.",
    'present_if'           => ":other :value bo'lganda, :attribute maydoni mavjud bo'lishi kerak.",
    'present_unless'       => ":other :value bo'lmasa, :attribute maydoni mavjud bo'lishi kerak.",
    'present_with'         => ":values mavjud bo'lganda, :attribute maydoni mavjud bo'lishi kerak.",
    'present_with_all'     => ":values mavjud bo'lganda, :attribute maydoni mavjud bo'lishi kerak.",
    'prohibited'           => ":attribute maydoni taqiqlangan.",
    'prohibited_if'        => ":other :value bo'lganda, :attribute maydoni taqiqlangan.",
    'prohibited_unless'    => ":other qiymati :values ichida bo'lmasa, :attribute maydoni taqiqlangan.",
    'prohibits'            => ":attribute maydoni :other mavjud bo'lishini taqiqlaydi.",
    'regex'                => ":attribute formati yaroqsiz.",
    'required'             => ":attribute maydoni to'ldirilishi shart.",
    'required_array_keys'  => ":attribute quyidagi kalitlarni o'z ichiga olishi kerak: :values.",
    'required_if'          => ":other :value bo'lganda, :attribute maydoni to'ldirilishi shart.",
    'required_if_accepted' => ":other qabul qilinganda, :attribute maydoni to'ldirilishi shart.",
    'required_unless'      => ":other qiymati :values ichida bo'lmasa, :attribute maydoni to'ldirilishi shart.",
    'required_with'        => ":values mavjud bo'lganda, :attribute maydoni to'ldirilishi shart.",
    'required_with_all'    => ":values mavjud bo'lganda, :attribute maydoni to'ldirilishi shart.",
    'required_without'     => ":values mavjud bo'lmaganda, :attribute maydoni to'ldirilishi shart.",
    'required_without_all' => "Quyidagilarning hech biri mavjud bo'lmasa: :values, :attribute maydoni to'ldirilishi shart.",
    'same'                 => ":attribute va :other bir xil bo'lishi kerak.",
    'size'                 => [
        'array'   => ":attribute :size ta elementdan iborat bo'lishi kerak.",
        'file'    => ":attribute :size kilobayt bo'lishi kerak.",
        'numeric' => ":attribute :size ga teng bo'lishi kerak.",
        'string'  => ":attribute :size ta belgidan iborat bo'lishi kerak.",
    ],
    'starts_with'          => ":attribute quyidagilardan biri bilan boshlanishi kerak: :values.",
    'string'               => ":attribute matn (satr) bo'lishi kerak.",
    'timezone'             => ":attribute yaroqli vaqt zonasi bo'lishi kerak.",
    'unique'               => "Bunday :attribute allaqachon mavjud.",
    'uploaded'             => ":attribute yuklab bo'lmadi.",
    'uppercase'            => ":attribute katta harflardan iborat bo'lishi kerak.",
    'url'                  => ":attribute yaroqli manzil (URL) bo'lishi kerak.",
    'ulid'                 => ":attribute yaroqli ULID bo'lishi kerak.",
    'uuid'                 => ":attribute yaroqli UUID bo'lishi kerak.",

    /*
    |--------------------------------------------------------------------------
    | Maxsus validatsiya satrlari
    |--------------------------------------------------------------------------
    |
    | Bu yerda "attribute.rule" nomlash konventsiyasi orqali muayyan
    | maydon+qoida uchun alohida xabar belgilash mumkin.
    |
    */

    'custom' => [
        'kod' => [
            'max' => "Faollashtirish kodi juda uzun (maksimum :max belgi). Kodni to'g'ri nusxa ko'chirganingizga ishonch hosil qiling.",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Maydon nomlarining tarjimasi
    |--------------------------------------------------------------------------
    |
    | Xabarlardagi ":attribute" o'rniga maydon nomini o'qishga qulay
    | ko'rinishda almashtirish uchun.
    |
    */

    'attributes' => [
        'kod'              => "Faollashtirish kodi",
        'ism'              => "Ism",
        'familiya'         => "Familiya",
        'otasining_ismi'   => "Otasining ismi",
        'telefon'          => "Telefon",
        'email'            => "Elektron pochta",
        'password'         => "Parol",
        'password_confirmation' => "Parolni tasdiqlash",
        'sana'             => "Sana",
        'dan_sana'         => "Dan sana",
        'gacha_sana'       => "Gacha sana",
        'summa'            => "Summa",
        'izoh'             => "Izoh",
        'nomi'             => "Nomi",
        'holat'            => "Holat",
        'filial_id'        => "Filial",
        'mijoz_id'         => "Mijoz",
        'tovar_id'         => "Tovar",
        'miqdor'           => "Miqdor",
        'narx'             => "Narx",
        'passport_seriya'  => "Passport seriyasi",
        'passport_raqam'   => "Passport raqami",
        'manzil'           => "Manzil",
    ],

];
