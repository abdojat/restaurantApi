<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'يجب قبول :attribute.',
    'accepted_if' => 'يجب قبول :attribute عندما يكون :other هو :value.',
    'active_url' => ':attribute ليس عنوان URL صحيحاً.',
    'after' => 'يجب أن يكون :attribute تاريخاً بعد :date.',
    'after_or_equal' => 'يجب أن يكون :attribute تاريخاً بعد أو يساوي :date.',
    'alpha' => 'يمكن أن يحتوي :attribute على أحرف فقط.',
    'alpha_dash' => 'يمكن أن يحتوي :attribute على أحرف وأرقام وشرطات وشرطات سفلية فقط.',
    'alpha_num' => 'يمكن أن يحتوي :attribute على أحرف وأرقام فقط.',
    'array' => 'يجب أن يكون :attribute مصفوفة.',
    'ascii' => 'يمكن أن يحتوي :attribute على أحرف وأرقام ورموز أحادية البايت فقط.',
    'before' => 'يجب أن يكون :attribute تاريخاً قبل :date.',
    'before_or_equal' => 'يجب أن يكون :attribute تاريخاً قبل أو يساوي :date.',
    'between' => [
        'array' => 'يجب أن يحتوي :attribute على :min إلى :max عنصر.',
        'file' => 'يجب أن يكون :attribute بين :min و :max كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute بين :min و :max.',
        'string' => 'يجب أن يكون :attribute بين :min و :max حرف.',
    ],
    'boolean' => 'يجب أن يكون حقل :attribute صحيحاً أو خاطئاً.',
    'can' => 'يحتوي حقل :attribute على قيمة غير مصرح بها.',
    'confirmed' => 'تأكيد :attribute لا يتطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => ':attribute ليس تاريخاً صحيحاً.',
    'date_equals' => 'يجب أن يكون :attribute تاريخاً يساوي :date.',
    'date_format' => ':attribute لا يتطابق مع التنسيق :format.',
    'decimal' => 'يجب أن يحتوي :attribute على :decimal منازل عشرية.',
    'declined' => 'يجب رفض :attribute.',
    'declined_if' => 'يجب رفض :attribute عندما يكون :other هو :value.',
    'different' => 'يجب أن يكون :attribute و :other مختلفين.',
    'digits' => 'يجب أن يكون :attribute :digits أرقام.',
    'digits_between' => 'يجب أن يكون :attribute بين :min و :max أرقام.',
    'dimensions' => ':attribute له أبعاد صورة غير صحيحة.',
    'distinct' => 'حقل :attribute له قيمة مكررة.',
    'doesnt_end_with' => 'قد لا ينتهي :attribute بأحد القيم التالية: :values.',
    'doesnt_start_with' => 'قد لا يبدأ :attribute بأحد القيم التالية: :values.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صحيح.',
    'ends_with' => 'يجب أن ينتهي :attribute بأحد القيم التالية: :values.',
    'enum' => ':attribute المحدد غير صحيح.',
    'exists' => ':attribute المحدد غير صحيح.',
    'extensions' => 'يجب أن يكون لـ :attribute أحد الامتدادات التالية: :values.',
    'failed' => 'هذه البيانات لا تتطابق مع سجلاتنا.',
    'file' => 'يجب أن يكون :attribute ملفاً.',
    'filled' => 'يجب أن يحتوي حقل :attribute على قيمة.',
    'gt' => [
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عنصر.',
        'file' => 'يجب أن يكون :attribute أكبر من :value كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute أكبر من :value.',
        'string' => 'يجب أن يكون :attribute أكبر من :value حرف.',
    ],
    'gte' => [
        'array' => 'يجب أن يحتوي :attribute على :value عنصر أو أكثر.',
        'file' => 'يجب أن يكون :attribute أكبر من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute أكبر من أو يساوي :value.',
        'string' => 'يجب أن يكون :attribute أكبر من أو يساوي :value حرف.',
    ],
    'hex_color' => 'يجب أن يكون حقل :attribute لوناً سداسياً صحيحاً.',
    'image' => 'يجب أن يكون :attribute صورة.',
    'in' => ':attribute المحدد غير صحيح.',
    'in_array' => 'حقل :attribute غير موجود في :other.',
    'integer' => 'يجب أن يكون :attribute رقماً صحيحاً.',
    'ip' => 'يجب أن يكون :attribute عنوان IP صحيح.',
    'ipv4' => 'يجب أن يكون :attribute عنوان IPv4 صحيح.',
    'ipv6' => 'يجب أن يكون :attribute عنوان IPv6 صحيح.',
    'json' => 'يجب أن يكون :attribute سلسلة JSON صحيحة.',
    'lowercase' => 'يجب أن يكون :attribute بأحرف صغيرة.',
    'lt' => [
        'array' => 'يجب أن يحتوي :attribute على أقل من :value عنصر.',
        'file' => 'يجب أن يكون :attribute أقل من :value كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute أقل من :value.',
        'string' => 'يجب أن يكون :attribute أقل من :value حرف.',
    ],
    'lte' => [
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :value عنصر.',
        'file' => 'يجب أن يكون :attribute أقل من أو يساوي :value كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute أقل من أو يساوي :value.',
        'string' => 'يجب أن يكون :attribute أقل من أو يساوي :value حرف.',
    ],
    'mac_address' => 'يجب أن يكون :attribute عنوان MAC صحيح.',
    'max' => [
        'array' => 'قد لا يحتوي :attribute على أكثر من :max عنصر.',
        'file' => 'قد لا يكون :attribute أكبر من :max كيلوبايت.',
        'numeric' => 'قد لا يكون :attribute أكبر من :max.',
        'string' => 'قد لا يكون :attribute أكبر من :max حرف.',
    ],
    'max_digits' => 'يجب ألا يحتوي :attribute على أكثر من :max أرقام.',
    'mimes' => 'يجب أن يكون :attribute ملفاً من نوع: :values.',
    'mimetypes' => 'يجب أن يكون :attribute ملفاً من نوع: :values.',
    'min' => [
        'array' => 'يجب أن يحتوي :attribute على :min عنصر على الأقل.',
        'file' => 'يجب أن يكون :attribute :min كيلوبايت على الأقل.',
        'numeric' => 'يجب أن يكون :attribute :min على الأقل.',
        'string' => 'يجب أن يكون :attribute :min حرف على الأقل.',
    ],
    'min_digits' => 'يجب أن يحتوي :attribute على :min أرقام على الأقل.',
    'missing' => 'حقل :attribute مفقود.',
    'missing_if' => 'حقل :attribute مفقود عندما يكون :other هو :value.',
    'missing_unless' => 'حقل :attribute مفقود ما لم يكن :other هو :value.',
    'missing_with' => 'حقل :attribute مفقود عندما يكون :values موجوداً.',
    'missing_with_all' => 'حقل :attribute مفقود عندما تكون :values موجودة.',
    'multiple_of' => 'يجب أن يكون :attribute مضاعفاً لـ :value.',
    'not_in' => ':attribute المحدد غير صحيح.',
    'not_regex' => 'تنسيق :attribute غير صحيح.',
    'numeric' => 'يجب أن يكون :attribute رقماً.',
    'password' => [
        'letters' => 'يجب أن يحتوي :attribute على حرف واحد على الأقل.',
        'mixed' => 'يجب أن يحتوي :attribute على حرف كبير وحرف صغير واحد على الأقل.',
        'numbers' => 'يجب أن يحتوي :attribute على رقم واحد على الأقل.',
        'symbols' => 'يجب أن يحتوي :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'ظهر :attribute المحدد في تسريب بيانات. يرجى اختيار :attribute مختلف.',
    ],
    'present' => 'يجب أن يكون حقل :attribute موجوداً.',
    'present_if' => 'يجب أن يكون حقل :attribute موجوداً عندما يكون :other هو :value.',
    'present_unless' => 'يجب أن يكون حقل :attribute موجوداً ما لم يكن :other هو :value.',
    'present_with' => 'يجب أن يكون حقل :attribute موجوداً عندما يكون :values موجوداً.',
    'present_with_all' => 'يجب أن يكون حقل :attribute موجوداً عندما تكون :values موجودة.',
    'prohibited' => 'حقل :attribute محظور.',
    'prohibited_if' => 'حقل :attribute محظور عندما يكون :other هو :value.',
    'prohibited_unless' => 'حقل :attribute محظور ما لم يكن :other هو :value.',
    'prohibits' => 'حقل :attribute يمنع :other من الوجود.',
    'regex' => 'تنسيق :attribute غير صحيح.',
    'required' => 'حقل :attribute مطلوب.',
    'required_array_keys' => 'يجب أن يحتوي حقل :attribute على إدخالات لـ: :values.',
    'required_if' => 'حقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_if_accepted' => 'حقل :attribute مطلوب عندما يتم قبول :other.',
    'required_unless' => 'حقل :attribute مطلوب ما لم يكن :other في :values.',
    'required_with' => 'حقل :attribute مطلوب عندما يكون :values موجوداً.',
    'required_with_all' => 'حقل :attribute مطلوب عندما تكون :values موجودة.',
    'required_without' => 'حقل :attribute مطلوب عندما لا يكون :values موجوداً.',
    'required_without_all' => 'حقل :attribute مطلوب عندما لا تكون أي من :values موجودة.',
    'same' => 'يجب أن يتطابق :attribute و :other.',
    'size' => [
        'array' => 'يجب أن يحتوي :attribute على :size عنصر.',
        'file' => 'يجب أن يكون :attribute :size كيلوبايت.',
        'numeric' => 'يجب أن يكون :attribute :size.',
        'string' => 'يجب أن يكون :attribute :size حرف.',
    ],
    'starts_with' => 'يجب أن يبدأ :attribute بأحد القيم التالية: :values.',
    'string' => 'يجب أن يكون :attribute سلسلة نصية.',
    'timezone' => 'يجب أن يكون :attribute منطقة زمنية صحيحة.',
    'unique' => 'تم أخذ :attribute بالفعل.',
    'uploaded' => 'فشل رفع :attribute.',
    'uppercase' => 'يجب أن يكون :attribute بأحرف كبيرة.',
    'url' => 'تنسيق :attribute غير صحيح.',
    'ulid' => 'يجب أن يكون :attribute ULID صحيح.',
    'uuid' => 'يجب أن يكون :attribute UUID صحيح.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'رسالة مخصصة',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'phone_number' => 'رقم الهاتف',
        'address' => 'العنوان',
        'date_of_birth' => 'تاريخ الميلاد',
        'category_id' => 'الفئة',
        'price' => 'السعر',
        'description' => 'الوصف',
        'ingredients' => 'المكونات',
        'allergens' => 'مسببات الحساسية',
        'preparation_time' => 'وقت التحضير',
        'sort_order' => 'ترتيب الفرز',
        'is_active' => 'حالة النشاط',
        'is_available' => 'التوفر',
        'is_vegetarian' => 'نباتي',
        'is_vegan' => 'نباتي صرف',
        'is_gluten_free' => 'خالي من الغلوتين',
        'table_number' => 'رقم الطاولة',
        'capacity' => 'السعة',
        'reservation_date' => 'تاريخ الحجز',
        'reservation_time' => 'وقت الحجز',
        'guest_count' => 'عدد الضيوف',
        'status' => 'الحالة',
        'total_amount' => 'المبلغ الإجمالي',
        'quantity' => 'الكمية',
        'notes' => 'ملاحظات',
    ],

];
