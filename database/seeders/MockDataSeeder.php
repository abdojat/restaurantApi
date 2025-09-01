<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Table;
use App\Models\Category;
use App\Models\Dish;
use App\Models\DishReview;
use App\Models\Reservation;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user (Syrian style)
        $admin = User::create([
            'name' => 'Omar Al-Khatib',
            'email' => 'admin@shami-restaurant.com',
            'phone_number' => '+963-944-000001',
            'password' => Hash::make('admin123'),
            'role_id' => Role::where('name', 'admin')->first()->id,
            'address' => 'Old City, Damascus, Syria',
            'date_of_birth' => '1982-03-15',
            'preferred_locale' => 'ar',
            'is_banned' => false,
            'failed_deliveries_count' => 0,
            'avatar_path' => null,
        ]);
        $admin->roles()->attach(Role::where('name', 'admin')->first()->id);

        $aliym = User::create([
            'name' => 'Ali Marouf',
            'email' => 'aliym@shami-restaurant.com',
            'phone_number' => '+963-981-896-153',
            'password' => Hash::make('aliym123'),
            'role_id' => Role::where('name', 'quality manager')->first()->id,
            'address' => 'Sahn al-Jamaa, Aleppo, Syria',
            'date_of_birth' => '2004-01-01',
            'preferred_locale' => 'ar',
            'is_banned' => false,
            'failed_deliveries_count' => 0,
            'avatar_path' => null,
        ]);
        $aliym->roles()->attach(Role::where('name', 'quality manager')->first()->id);

        // Create manager users (Syrian names/addresses)
        $managers = [
            [
                'name' => 'Rami Haddad',
                'email' => 'manager1@shami-restaurant.com',
                'phone_number' => '+963-933-000002',
                'address' => 'Abu Rummaneh, Damascus',
                'date_of_birth' => '1988-07-22',
            ],
            [
                'name' => 'Lina Sayegh',
                'email' => 'manager2@shami-restaurant.com',
                'phone_number' => '+963-935-000003',
                'address' => 'Azizieh, Aleppo',
                'date_of_birth' => '1985-11-08',
            ],
            [
                'name' => 'Mohammad ali wassouf',
                'email' => 'mimoali@shami-restaurant.com',
                'phone_number' => '+963-932-000004',
                'address' => 'الكورنيش الشرقي، طرطوس',
                'date_of_birth' => '1985-11-08',
            ],
        ];

        foreach ($managers as $managerData) {
            $manager = User::create([
                'name' => $managerData['name'],
                'email' => $managerData['email'],
                'phone_number' => $managerData['phone_number'],
                'password' => Hash::make('manager123'),
                'role_id' => Role::where('name', 'manager')->first()->id,
                'address' => $managerData['address'],
                'date_of_birth' => $managerData['date_of_birth'],
                'preferred_locale' => $managerData['name'] === 'Mohammad ali wassouf' ? 'ar' : 'ar',
                'is_banned' => false,
                'failed_deliveries_count' => 0,
                'avatar_path' => null,
            ]);
            $manager->roles()->attach(Role::where('name', 'manager')->first()->id);
        }

        // Create cashier users (Syrian style)
        $cashiers = [
            [
                'name' => 'Hiba Al-Zein',
                'email' => 'cashier1@shami-restaurant.com',
                'phone_number' => '+963-944-000004',
                'address' => 'Mezzeh, Damascus',
                'date_of_birth' => '1995-04-12',
            ],
            [
                'name' => 'Yousef Barakat',
                'email' => 'cashier2@shami-restaurant.com',
                'phone_number' => '+963-944-000005',
                'address' => 'Hamidiya Souq, Damascus',
                'date_of_birth' => '1992-09-28',
            ],
            [
                'name' => 'Maya Nassar',
                'email' => 'cashier3@shami-restaurant.com',
                'phone_number' => '+963-944-000006',
                'address' => 'Lattakia Corniche',
                'date_of_birth' => '1998-01-14',
            ],
        ];

        foreach ($cashiers as $cashierData) {
            $cashier = User::create([
                'name' => $cashierData['name'],
                'email' => $cashierData['email'],
                'phone_number' => $cashierData['phone_number'],
                'password' => Hash::make('cashier123'),
                'role_id' => Role::where('name', 'cashier')->first()->id,
                'address' => $cashierData['address'],
                'date_of_birth' => $cashierData['date_of_birth'],
                'preferred_locale' => 'ar',
                'is_banned' => false,
                'failed_deliveries_count' => 0,
                'avatar_path' => null,
            ]);
            $cashier->roles()->attach(Role::where('name', 'cashier')->first()->id);
        }

        // Create customer users (Syrian names/addresses/phones)
        $customers = [
            [
                'name' => 'Ahmad Al-Hamwi',
                'email' => 'ahmad.hamwi@customer.com',
                'phone_number' => '+963-991-100001',
                'address' => 'Al-Midan, Damascus',
                'date_of_birth' => '1990-06-15',
                'is_banned' => false,
                'failed_deliveries_count' => 0,
            ],
            [
                'name' => 'Rana Kheir',
                'email' => 'rana.kheir@customer.com',
                'phone_number' => '+963-991-100002',
                'address' => 'Al-Azizieh, Aleppo',
                'date_of_birth' => '1987-12-03',
                'is_banned' => false,
                'failed_deliveries_count' => 1,
            ],
            [
                'name' => 'Samer Qabbani',
                'email' => 'samer.qabbani@customer.com',
                'phone_number' => '+963-991-100003',
                'address' => 'Homs City Center',
                'date_of_birth' => '1993-02-28',
                'is_banned' => false,
                'failed_deliveries_count' => 0,
            ],
            [
                'name' => 'Nour Al-Sabbagh',
                'email' => 'nour.sabbagh@customer.com',
                'phone_number' => '+963-991-100004',
                'address' => 'Tishreen Street, Damascus',
                'date_of_birth' => '1995-08-19',
                'is_banned' => false,
                'failed_deliveries_count' => 2,
            ],
            [
                'name' => 'Riad Dakkak',
                'email' => 'riad.dakkak@customer.com',
                'phone_number' => '+963-991-100005',
                'address' => 'Al-Ramel Al-Janoubi, Lattakia',
                'date_of_birth' => '1975-05-07',
                'is_banned' => true,
                'failed_deliveries_count' => 5,
                'banned_at' => Carbon::now()->subDays(10),
            ],
            [
                'name' => 'Maha Saadeh',
                'email' => 'maha.saadeh@customer.com',
                'phone_number' => '+963-991-100006',
                'address' => 'Al-Maza, Damascus',
                'date_of_birth' => '1991-10-12',
                'is_banned' => false,
                'failed_deliveries_count' => 0,
            ],
            [
                'name' => 'Khaled Al-Masri',
                'email' => 'khaled.masri@customer.com',
                'phone_number' => '+963-991-100007',
                'address' => 'Banias, Tartous',
                'date_of_birth' => '1989-04-25',
                'is_banned' => false,
                'failed_deliveries_count' => 1,
            ],
            [
                'name' => 'Dalia Rafeh',
                'email' => 'dalia.rafeh@customer.com',
                'phone_number' => '+963-991-100008',
                'address' => 'Jaramana, Damascus Countryside',
                'date_of_birth' => '1986-09-18',
                'is_banned' => false,
                'failed_deliveries_count' => 3,
            ],
            [
                'name' => 'Yazan Hariri',
                'email' => 'yazan.hariri@customer.com',
                'phone_number' => '+963-991-100009',
                'address' => 'Waterfront, Lattakia',
                'date_of_birth' => '1994-01-30',
                'is_banned' => false,
                'failed_deliveries_count' => 0,
            ],
            [
                'name' => 'Hind Al-Sheikh',
                'email' => 'hind.sheikh@customer.com',
                'phone_number' => '+963-991-100010',
                'address' => 'Bab Touma, Damascus',
                'date_of_birth' => '1992-11-22',
                'is_banned' => true,
                'failed_deliveries_count' => 4,
                'banned_at' => Carbon::now()->subDays(5),
            ],
        ];

        foreach ($customers as $customerData) {
            $customer = User::create([
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'phone_number' => $customerData['phone_number'],
                'password' => Hash::make('customer123'),
                'role_id' => Role::where('name', 'customer')->first()->id,
                'address' => $customerData['address'],
                'date_of_birth' => $customerData['date_of_birth'],
                'preferred_locale' => 'ar',
                'is_banned' => $customerData['is_banned'],
                'failed_deliveries_count' => $customerData['failed_deliveries_count'],
                'banned_at' => $customerData['banned_at'] ?? null,
                'avatar_path' => null,
            ]);
            $customer->roles()->attach(Role::where('name', 'customer')->first()->id);
        }

        // Create restaurant tables (Syrian ambiance)
        $tables = [
            [
                'name' => 'Table 01',
                'name_ar' => 'الطاولة 01',
                'capacity' => 2,
                'type' => 'double',
                'status' => 'available',
                'image_path' => 'tables/76KFeYjriQMgiHd7e3qE8PCLyMfEuwWj41orNpZd.jpg',
                'description' => 'Damascene courtyard corner, perfect for quiet dinners',
                'description_ar' => 'زاوية فناء دمشقي، مثالية لعشاء هادئ',
                'is_active' => true,
            ],
            [
                'name' => 'Table 02',
                'name_ar' => 'الطاولة 02',
                'capacity' => 6,
                'type' => 'family',
                'status' => 'available',
                'image_path' => 'tables/EJBoroMMXuSxj6OQqz4S9gEtfncXCsnxzagnFH6s.jpg',
                'description' => 'Central table near the saj oven aroma',
                'description_ar' => 'طاولة وسطية قرب رائحة فرن الساج',
                'is_active' => true,
            ],
            [
                'name' => 'Table 03',
                'name_ar' => 'الطاولة 03',
                'capacity' => 6,
                'type' => 'single',
                'status' => 'available',
                'image_path' => 'tables/V751QElE3hGTUfCUxRZ3otVSbTi9XV4ASAAtRhd1.jpg',
                'description' => 'Family table with traditional copper decor',
                'description_ar' => 'طاولة عائلية مع ديكور نحاسي تراثي',
                'is_active' => true,
            ],
            [
                'name' => 'Table 04',
                'name_ar' => 'الطاولة 04',
                'capacity' => 2,
                'type' => 'double',
                'status' => 'available',
                'image_path' => 'tables/M9BnlmGMk0yuWlleFpknLhmCbiKO8kzbQB4SKTYj.webp',
                'description' => 'Quiet nook by the fountain',
                'description_ar' => 'ركن هادئ بجانب البركة',
                'is_active' => true,
            ],
            [
                'name' => 'Table 05',
                'name_ar' => 'الطاولة 05',
                'capacity' => 8,
                'type' => 'custom',
                'status' => 'available',
                'image_path' => 'tables/q4L82DFTqRnZ0VLJ4CPAZ9BafluiHmUxCDeC60Vy.jpg',
                'description' => 'Celebration table under grape vines',
                'description_ar' => 'طاولة احتفال تحت عريشة العنب',
                'is_active' => true,
            ],
            [
                'name' => 'Table 06',
                'name_ar' => 'الطاولة 06',
                'capacity' => 4,
                'type' => 'special',
                'status' => 'available',
                'image_path' => 'tables/8yuKhKcdO42i249Dh2IlodirOPNPaoo7OGNXgD4M.webp',
                'description' => 'Garden view with natural light',
                'description_ar' => 'إطلالة حديقة مع إضاءة طبيعية',
                'is_active' => true,
            ],
            [
                'name' => 'Table 07',
                'name_ar' => 'الطاولة 07',
                'capacity' => 2,
                'type' => 'single',
                'status' => 'available',
                'image_path' => 'tables/kss7TBjPXtV1Axf9Q02Le3Z9z5UELc3ERyzZ3MAg.jpg',
                'description' => 'High-top near the coffee bar',
                'description_ar' => 'طاولة مرتفعة قرب ركن القهوة',
                'is_active' => true,
            ],
            [
                'name' => 'Table 08',
                'name_ar' => 'الطاولة 08',
                'capacity' => 6,
                'type' => 'family',
                'status' => 'available',
                'image_path' => 'tables/9wL2dATeEWBtUIrxstoIwfnNfVnEpYVtQjpHmpn6.jpg',
                'description' => 'Outdoor terrace with sea breeze (Lattakia corner)',
                'description_ar' => 'تراس خارجي بنسيم البحر (زاوية اللاذقية)',
                'is_active' => true,
            ],
            [
                'name' => 'Table 09',
                'name_ar' => 'الطاولة 09',
                'capacity' => 4,
                'type' => 'single',
                'status' => 'available',
                'image_path' => 'tables/EPGZ5pLqrmJxSniph6ZCrOF3oFzgXgssWm65jEHb.webp',
                'description' => 'Chef\'s table facing the grill (mashawi)',
                'description_ar' => 'طاولة الشيف بإطلالة على المشاوي',
                'is_active' => true,
            ],
            [
                'name' => 'Table 10',
                'name_ar' => 'الطاولة 10',
                'capacity' => 10,
                'type' => 'custom',
                'status' => 'available',
                'image_path' => 'tables/dfo0HwJmljIrHaEfEFFbPCXqSYjqEMAOJxuS9McG.jpg',
                'description' => 'VIP diwan for special occasions',
                'description_ar' => 'ديوان كبار الشخصيات للمناسبات الخاصة',
                'is_active' => true,
            ],
            [
                'name' => 'Table 11',
                'name_ar' => 'الطاولة 11',
                'capacity' => 3,
                'type' => 'special',
                'status' => 'available',
                'image_path' => 'tables/n7fZgMCu0STzCQcT8zDTaZnUHZ7liLIalM19wgGD.webp',
                'description' => 'Artistic triangular table by the lattice window',
                'description_ar' => 'طاولة مثلثة فنية قرب المشربية',
                'is_active' => true,
            ],
            [
                'name' => 'Table 12',
                'name_ar' => 'الطاولة 12',
                'capacity' => 2,
                'type' => 'single',
                'status' => 'available',
                'image_path' => 'tables/tXEuFEebCY9W2wIop9EisMF3IVa2NyhHsSWEyxIA.jpg',
                'description' => 'Balcony table with Old City view',
                'description_ar' => 'طاولة شرفة مطلة على المدينة القديمة',
                'is_active' => true,
            ],
        ];
        foreach ($tables as $tableData) {
            Table::create($tableData);
        }

        // Syrian menu categories
        $categories = [
            ['name' => 'Mezze & Starters', 'name_ar' => 'المقبلات والمازة', 'description' => 'Traditional cold and hot mezze to start your meal', 'description_ar' => 'مازة باردة وساخنة تقليدية لبدء وجبتك', 'sort_order' => 1],
            ['name' => 'Soups', 'name_ar' => 'الشوربات', 'description' => 'Warm and comforting Syrian soups', 'description_ar' => 'شوربات سورية دافئة ومريحة', 'sort_order' => 2],
            ['name' => 'Salads', 'name_ar' => 'السلطات', 'description' => 'Fresh Levantine salads with seasonal herbs', 'description_ar' => 'سلطات شامية طازجة مع أعشاب موسمية', 'sort_order' => 3],
            ['name' => 'Grilled Meats (Mashawi)', 'name_ar' => 'المشاوي', 'description' => 'Authentic Syrian kebabs and grilled meats', 'description_ar' => 'كباب ومشاوي سورية أصيلة', 'sort_order' => 4],
            ['name' => 'Main Dishes', 'name_ar' => 'الأطباق الرئيسية', 'description' => 'Classic Syrian stews and family-style dishes', 'description_ar' => 'أطباق سورية تقليدية مطهية على الطريقة المنزلية', 'sort_order' => 5],
            ['name' => 'Seafood', 'name_ar' => 'المأكولات البحرية', 'description' => 'Fresh seafood with a Syrian touch', 'description_ar' => 'مأكولات بحرية طازجة بلمسة سورية', 'sort_order' => 6],
            ['name' => 'Vegetarian Dishes', 'name_ar' => 'الأطباق النباتية', 'description' => 'Delicious meat-free Levantine recipes', 'description_ar' => 'وصفات شامية لذيذة خالية من اللحوم', 'sort_order' => 7],
            ['name' => 'Pastries & Manaqeesh', 'name_ar' => 'المعجنات والمناقيش', 'description' => 'Oven-baked pies, manaqeesh, and fatayer', 'description_ar' => 'فطائر ومناقيش ومعجنات مشوية بالفرن', 'sort_order' => 8],
            ['name' => 'Desserts & Sweets', 'name_ar' => 'الحلويات', 'description' => 'Famous Syrian desserts and oriental sweets', 'description_ar' => 'حلويات سورية مشهورة وحلويات شرقية', 'sort_order' => 9],
            ['name' => 'Beverages', 'name_ar' => 'المشروبات', 'description' => 'Fresh juices, ayran, tea, and coffee', 'description_ar' => 'عصائر طبيعية ولبن عيران وشاي وقهوة', 'sort_order' => 10],
        ];
        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Dishes (IDs aligned with your order/review mappings)
        $dishes = [
            // 1..3 Mezze & Starters
            [
                'name' => 'Hummus',
                'description' => 'Classic Levantine chickpea dip with tahini and olive oil',
                'price' => 5.99,
                'category_id' => 1,
                'image_path' => 'dishes/kOzstr5UYIl8yt9FEk8P0GQEA9ZGUvQbkRj0KdAo.jpg',
                'is_vegetarian' => true, 'is_vegan' => true, 'is_gluten_free' => true, 'is_available' => true,
                'preparation_time' => 8,
                'ingredients' => 'Chickpeas, tahini, olive oil, lemon juice, garlic, salt',
                'allergens' => 'Sesame',
                'name_ar' => 'حمص',
                'description_ar' => 'حمص شامي كلاسيكي مع الطحينة وزيت الزيتون',
                'ingredients_ar' => 'حمص، طحينة، زيت زيتون، عصير ليمون، ثوم، ملح',
                'allergens_ar' => 'سمسم',
                'sort_order' => 1
            ],
            [
                'name' => 'Baba Ghanoush',
                'description' => 'Smoky roasted eggplant dip with tahini and lemon',
                'price' => 6.49,
                'category_id' => 1,
                'image_path' => 'dishes/U6o73GriszmclGopas4kEyBLYskCZpY0ihg6hnSv.jpg',
                'is_vegetarian' => true, 'is_vegan' => true, 'is_gluten_free' => true, 'is_available' => true,
                'preparation_time' => 10,
                'ingredients' => 'Eggplant, tahini, lemon juice, garlic, olive oil',
                'allergens' => 'Sesame',
                'name_ar' => 'بابا غنوج',
                'description_ar' => 'متبل باذنجان مدخن مع الطحينة والليمون',
                'ingredients_ar' => 'باذنجان، طحينة، عصير ليمون، ثوم، زيت زيتون',
                'allergens_ar' => 'سمسم',
                'sort_order' => 2
            ],
            [
                'name' => 'Falafel',
                'description' => 'Crispy fried chickpea fritters served with tahini sauce',
                'price' => 7.99,
                'category_id' => 1,
                'image_path' => 'dishes/UJRHnqyJuwoxwt8qoMZUDp9tTEJFcpNvvDoomCs5.jpg',
                'is_vegetarian' => true, 'is_vegan' => true, 'is_gluten_free' => false, 'is_available' => true,
                'preparation_time' => 12,
                'ingredients' => 'Chickpeas, parsley, onion, garlic, spices, flour',
                'allergens' => 'Gluten, Sesame',
                'name_ar' => 'فلافل',
                'description_ar' => 'أقراص حمص مقلية مقرمشة تقدم مع صلصة الطحينة',
                'ingredients_ar' => 'حمص، بقدونس، بصل، ثوم، بهارات، دقيق',
                'allergens_ar' => 'جلوتين، سمسم',
                'sort_order' => 3
            ],

            // 4 Soup
            [
                'name' => 'Lentil Soup',
                'description' => 'Comforting red lentil soup with cumin and lemon',
                'price' => 6.99,
                'category_id' => 2,
                'image_path' => 'dishes/hcwFIDZhUy7BjQfwnB3PV3qDVRTvkI9UEMoV3YmE.jpg',
                'is_vegetarian' => true, 'is_vegan' => true, 'is_gluten_free' => true, 'is_available' => true,
                'preparation_time' => 15,
                'ingredients' => 'Red lentils, onion, carrot, cumin, olive oil, lemon',
                'allergens' => 'None',
                'name_ar' => 'شوربة عدس',
                'description_ar' => 'شوربة عدس حمراء مريحة مع الكمون والليمون',
                'ingredients_ar' => 'عدس أحمر، بصل، جزر، كمون، زيت زيتون، ليمون',
                'allergens_ar' => 'لا يوجد',
                'sort_order' => 1
            ],

            // 5..6 Salads
            [
                'name' => 'Tabbouleh',
                'description' => 'Fresh parsley salad with bulgur, tomatoes, mint, and lemon',
                'price' => 7.49,
                'category_id' => 3,
                'image_path' => 'dishes/GKTOBopFRNISTJZvx6SIynxhzzi2dPMZjICQH5oe.jpg',
                'is_vegetarian' => true, 'is_vegan' => true, 'is_gluten_free' => false, 'is_available' => true,
                'preparation_time' => 10,
                'ingredients' => 'Parsley, bulgur, tomato, mint, onion, lemon, olive oil',
                'allergens' => 'Gluten',
                'name_ar' => 'تبولة',
                'description_ar' => 'سلطة بقدونس طازجة مع برغل وطماطم ونعناع وليمون',
                'ingredients_ar' => 'بقدونس، برغل، طماطم، نعناع، بصل، ليمون، زيت زيتون',
                'allergens_ar' => 'جلوتين',
                'sort_order' => 1
            ],
            [
                'name' => 'Fattoush',
                'description' => 'Mixed vegetable salad with crispy pita bread and sumac dressing',
                'price' => 7.99,
                'category_id' => 3,
                'image_path' => 'dishes/9HIFBtA7cRVtRKPrATU2hvleTacbB7FYPVkWKTP6.jpg',
                'is_vegetarian' => true, 'is_vegan' => true, 'is_gluten_free' => false, 'is_available' => true,
                'preparation_time' => 8,
                'ingredients' => 'Lettuce, tomato, cucumber, radish, onion, fried pita, sumac',
                'allergens' => 'Gluten',
                'name_ar' => 'فتوش',
                'description_ar' => 'سلطة خضار مشكلة مع خبز مقلي وصلصة سماق',
                'ingredients_ar' => 'خس، طماطم، خيار، فجل، بصل، خبز مقلي، سماق',
                'allergens_ar' => 'جلوتين',
                'sort_order' => 2
            ],

            // 7..8 more mezze to align IDs before mashawi
            [
                'name' => 'Muhammara',
                'description' => 'Aleppian spicy walnut & red pepper dip with pomegranate molasses',
                'price' => 6.99,
                'category_id' => 1,
                'image_path' => 'dishes/gbksO0Ltbn8vmFJs44iz37N892iRl5Ju6OY5PTj0.jpg',
                'is_vegetarian' => true, 'is_vegan' => true, 'is_gluten_free' => true, 'is_available' => true,
                'preparation_time' => 8,
                'ingredients' => 'Walnuts, red peppers, breadcrumbs (optional), pomegranate molasses, olive oil',
                'allergens' => 'Nuts',
                'name_ar' => 'محمرة',
                'description_ar' => 'محمرة حلبية حارة بالجوز ودبس الرمان',
                'ingredients_ar' => 'جوز، فليفلة حمراء، بقسماط (اختياري)، دبس رمان، زيت زيتون',
                'allergens_ar' => 'مكسرات',
                'sort_order' => 4
            ],
            [
                'name' => 'Yalanji (Vine Leaves)',
                'description' => 'Stuffed vine leaves with rice, parsley, and sour dressing',
                'price' => 7.49,
                'category_id' => 1,
                'image_path' => 'dishes/HU8UmE1puvA3vm3zFKvgNqOsM2gRG1FGvwEYWMYy.jpg',
                'is_vegetarian' => true, 'is_vegan' => true, 'is_gluten_free' => true, 'is_available' => true,
                'preparation_time' => 12,
                'ingredients' => 'Vine leaves, rice, parsley, tomato, lemon, olive oil',
                'allergens' => 'None',
                'name_ar' => 'يلنجي ورق عنب',
                'description_ar' => 'ورق عنب محشي بالأرز والبقدونس بتتبيلة حامضة',
                'ingredients_ar' => 'ورق عنب، أرز، بقدونس، طماطم، ليمون، زيت زيتون',
                'allergens_ar' => 'لا يوجد',
                'sort_order' => 5
            ],

            // 9..10 Mashawi (IDs required by orders)
            [
                'name' => 'Shish Tawook',
                'description' => 'Marinated chicken skewers grilled to perfection',
                'price' => 14.99,
                'category_id' => 4,
                'image_path' => 'dishes/shA92yTUmNA1FXuxlmsuddZxFV2mbxhze6j9vvzC.jpg',
                'is_vegetarian' => false, 'is_vegan' => false, 'is_gluten_free' => true, 'is_available' => true,
                'preparation_time' => 20,
                'ingredients' => 'Chicken breast, yogurt, garlic, lemon, paprika, spices',
                'allergens' => 'Dairy',
                'name_ar' => 'شيش طاووق',
                'description_ar' => 'أسياخ دجاج متبلة ومشوية على الكمال',
                'ingredients_ar' => 'صدر دجاج، لبن، ثوم، ليمون، بابريكا، بهارات',
                'allergens_ar' => 'ألبان',
                'sort_order' => 1
            ],
            [
                'name' => 'Lamb Kebab',
                'description' => 'Ground lamb skewers with parsley and spices',
                'price' => 16.99,
                'category_id' => 4,
                'image_path' => 'dishes/YE60SBVCWggqvitdylMefSS0DpT0JEp1fD1klPPp.jpg',
                'is_vegetarian' => false, 'is_vegan' => false, 'is_gluten_free' => true, 'is_available' => true,
                'preparation_time' => 18,
                'ingredients' => 'Ground lamb, parsley, onion, spices',
                'allergens' => 'None',
                'name_ar' => 'كباب لحم',
                'description_ar' => 'كباب لحم غنم مفروم مع بقدونس وبهارات',
                'ingredients_ar' => 'لحم غنم مفروم، بقدونس، بصل، بهارات',
                'allergens_ar' => 'لا يوجد',
                'sort_order' => 2
            ],

            // 11..12 Main dishes (IDs required)
            [
                'name' => 'Kibbeh Labanieh',
                'description' => 'Fried kibbeh balls cooked in yogurt sauce with garlic and mint',
                'price' => 15.99,
                'category_id' => 5,
                'image_path' => 'dishes/saXAtVP76FJtyW9UMiizL5ICEmAtklmZrECQUfRl.jpg',
                'is_vegetarian' => false, 'is_vegan' => false, 'is_gluten_free' => false, 'is_available' => true,
                'preparation_time' => 30,
                'ingredients' => 'Bulgur, minced lamb, yogurt, garlic, mint, pine nuts',
                'allergens' => 'Gluten, Dairy, Nuts',
                'name_ar' => 'كبة لبنية',
                'description_ar' => 'كرات كبة مقلية مطهوة بصلصة لبن مع الثوم والنعناع',
                'ingredients_ar' => 'برغل، لحم غنم مفروم، لبن، ثوم، نعناع، صنوبر',
                'allergens_ar' => 'جلوتين، ألبان، مكسرات',
                'sort_order' => 1
            ],
            [
                'name' => 'Molokhia',
                'description' => 'Syrian-style jute leaf stew with chicken and rice',
                'price' => 13.99,
                'category_id' => 5,
                'image_path' => 'dishes/VVZEmk2tsZOnet5TsoBBxnXxvjL7Xx1yskph69gj.jpg',
                'is_vegetarian' => false, 'is_vegan' => false, 'is_gluten_free' => true, 'is_available' => true,
                'preparation_time' => 40,
                'ingredients' => 'Molokhia leaves, chicken, garlic, coriander, rice',
                'allergens' => 'None',
                'name_ar' => 'ملوخية',
                'description_ar' => 'ملوخية على الطريقة السورية مع الدجاج والأرز',
                'ingredients_ar' => 'أوراق ملوخية، دجاج، ثوم، كزبرة، أرز',
                'allergens_ar' => 'لا يوجد',
                'sort_order' => 2
            ],

            // 13..14 Pastries & Manaqeesh (IDs required: 13,14)
            [
                'name' => 'Cheese Manakeesh',
                'description' => 'Flatbread topped with akkawi cheese and baked in a stone oven',
                'price' => 5.99,
                'category_id' => 8,
                'image_path' => 'dishes/KX1Ndl4aOmV8x1EtRlc5ctIKSgk6ZI1L5tPneeJW.jpg',
                'is_vegetarian' => true, 'is_vegan' => false, 'is_gluten_free' => false, 'is_available' => true,
                'preparation_time' => 12,
                'ingredients' => 'Flour, akkawi cheese, olive oil, yeast',
                'allergens' => 'Gluten, Dairy',
                'name_ar' => 'مناقيش جبنة',
                'description_ar' => 'خبز مسطح بالجبنة العكاوي مخبوز بالفرن الحجري',
                'ingredients_ar' => 'طحين، جبنة عكاوي، زيت زيتون، خميرة',
                'allergens_ar' => 'جلوتين، ألبان',
                'sort_order' => 1
            ],
            [
                'name' => 'Zaatar Manakeesh',
                'description' => 'Thyme and sesame manakeesh brushed with olive oil',
                'price' => 4.99,
                'category_id' => 8,
                'image_path' => 'dishes/aMk07Y2sdGvfkX1zIo3nULfl0XqpiOJ2ihZWeivd.jpg',
                'is_vegetarian' => true, 'is_vegan' => true, 'is_gluten_free' => false, 'is_available' => true,
                'preparation_time' => 10,
                'ingredients' => 'Flour, zaatar mix (thyme, sesame, sumac), olive oil',
                'allergens' => 'Gluten, Sesame',
                'name_ar' => 'مناقيش زعتر',
                'description_ar' => 'مناقيش بالزعتر والسمسم بدهن زيت الزيتون',
                'ingredients_ar' => 'طحين، زعتر (زعتر، سمسم، سماق)، زيت زيتون',
                'allergens_ar' => 'جلوتين، سمسم',
                'sort_order' => 2
            ],

            // 15 Dessert (ID required)
            [
                'name' => 'Knafeh Nabulsieh',
                'description' => 'Semolina pastry with cheese and sugar syrup',
                'price' => 8.99,
                'category_id' => 9,
                'image_path' => 'dishes/UhgOi1qqp250AcYRwuzSldxx7vagwEvc3mLmVezV.jpg',
                'is_vegetarian' => true, 'is_vegan' => false, 'is_gluten_free' => false, 'is_available' => true,
                'preparation_time' => 15,
                'ingredients' => 'Semolina, cheese, sugar syrup, ghee, pistachios',
                'allergens' => 'Gluten, Dairy, Nuts',
                'name_ar' => 'كنافة نابلسية',
                'description_ar' => 'عجينة سميد مع جبنة وقطر',
                'ingredients_ar' => 'سميد، جبنة، قطر، سمن، فستق حلبي',
                'allergens_ar' => 'جلوتين، ألبان، مكسرات',
                'sort_order' => 1
            ],

            // 16..17 Beverages (IDs required)
            [
                'name' => 'Ayran',
                'description' => 'Refreshing yogurt drink with salt',
                'price' => 3.99,
                'category_id' => 10,
                'image_path' => 'dishes/QLU3C7Rm9M3EoIuFb3usxPwPEXmNkLTlvGyv95WK.jpg',
                'is_vegetarian' => true, 'is_vegan' => false, 'is_gluten_free' => true, 'is_available' => true,
                'preparation_time' => 2,
                'ingredients' => 'Yogurt, water, salt',
                'allergens' => 'Dairy',
                'name_ar' => 'عيران',
                'description_ar' => 'شراب لبن منعش مع ملح',
                'ingredients_ar' => 'لبن، ماء، ملح',
                'allergens_ar' => 'ألبان',
                'sort_order' => 1
            ],
            [
                'name' => 'Syrian Tea with Mint',
                'description' => 'Black tea brewed with fresh mint leaves',
                'price' => 2.99,
                'category_id' => 10,
                'image_path' => 'dishes/4MNUOMdGSL4ZzLGeA3rqSfNd4AplYPnOtndbTTgW.jpg',
                'is_vegetarian' => true, 'is_vegan' => true, 'is_gluten_free' => true, 'is_available' => true,
                'preparation_time' => 5,
                'ingredients' => 'Black tea, mint, sugar, water',
                'allergens' => 'None',
                'name_ar' => 'شاي بالنعناع',
                'description_ar' => 'شاي أسود مغلي مع أوراق نعناع طازج',
                'ingredients_ar' => 'شاي أسود، نعناع، سكر، ماء',
                'allergens_ar' => 'لا يوجد',
                'sort_order' => 2
            ],
        ];

        foreach ($dishes as $dishData) {
            Dish::create($dishData);
        }

        // Reservations (Arabic notes / Syrian context)
        $reservationDates = [
            Carbon::now()->addDays(1)->setTime(18, 0),
            Carbon::now()->addDays(1)->setTime(19, 30),
            Carbon::now()->addDays(2)->setTime(17, 0),
            Carbon::now()->addDays(2)->setTime(20, 0),
            Carbon::now()->addDays(3)->setTime(19, 0),
            Carbon::now()->addDays(5)->setTime(18, 30),
            Carbon::now()->addDays(7)->setTime(17, 30),
        ];

        $activeCustomers = User::where('is_banned', false)->where('role_id', Role::where('name', 'customer')->first()->id)->get();

        $reservations = [
            [
                'user_id' => $activeCustomers[0]->id,
                'table_id' => 1,
                'reservation_date' => $reservationDates[0],
                'guests' => 2,
                'status' => 'confirmed',
                'special_requests' => 'عشاء ذكرى زواج – قرب النافورة لو تكرمتم',
            ],
            [
                'user_id' => $activeCustomers[1]->id,
                'table_id' => 5,
                'reservation_date' => $reservationDates[1],
                'guests' => 6,
                'status' => 'confirmed',
                'special_requests' => 'عيد ميلاد – كرسي أطفال متوفر',
            ],
            [
                'user_id' => $activeCustomers[2]->id,
                'table_id' => 3,
                'reservation_date' => $reservationDates[2],
                'guests' => 4,
                'status' => 'pending',
                'special_requests' => 'عشاء عمل – طاولة هادئة رجاءً',
            ],
            [
                'user_id' => $activeCustomers[3]->id,
                'table_id' => 8,
                'reservation_date' => $reservationDates[3],
                'guests' => 4,
                'status' => 'confirmed',
                'special_requests' => 'جلسة خارجية إن أمكن',
            ],
            [
                'user_id' => $activeCustomers[4]->id,
                'table_id' => 10,
                'reservation_date' => $reservationDates[4],
                'guests' => 8,
                'status' => 'pending',
                'special_requests' => 'عشاء شركة – نحتاج بروجيكتور',
            ],
            [
                'user_id' => $activeCustomers[5]->id,
                'table_id' => 2,
                'reservation_date' => $reservationDates[5],
                'guests' => 3,
                'status' => 'confirmed',
                'special_requests' => 'نحتاج خيارات بدون جلوتين',
            ],
            [
                'user_id' => $activeCustomers[6]->id,
                'table_id' => 9,
                'reservation_date' => $reservationDates[6],
                'guests' => 2,
                'status' => 'cancelled',
                'special_requests' => 'تجربة طاولة الشيف',
            ],
        ];

        foreach ($reservations as $reservationData) {
            Reservation::create($reservationData);
        }

        // Orders (texts localized)
        $orders = [
            [
                'user_id' => $activeCustomers[0]->id,
                'table_id' => 1,
                'type' => 'dine_in',
                'status' => 'delivered',
                'subtotal' => 89.97,
                'tax_amount' => 8.10,
                'total_amount' => 98.07,
                'notes' => 'خدمة ممتازة وطعام لذيذ.',
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'user_id' => $activeCustomers[1]->id,
                'table_id' => 3,
                'type' => 'dine_in',
                'status' => 'preparing',
                'subtotal' => 156.94,
                'tax_amount' => 14.12,
                'total_amount' => 171.06,
                'notes' => 'شيش طاووق متوسط الاستواء وخضار إضافية.',
                'created_at' => Carbon::now()->subMinutes(45),
            ],
            [
                'user_id' => $activeCustomers[2]->id,
                'table_id' => null,
                'type' => 'takeaway',
                'status' => 'with_courier',
                'subtotal' => 42.98,
                'tax_amount' => 3.87,
                'total_amount' => 46.85,
                'notes' => 'جاهز للاستلام من الواجهة.',
                'created_at' => Carbon::now()->subMinutes(20),
            ],
            [
                'user_id' => $activeCustomers[3]->id,
                'table_id' => null,
                'type' => 'delivery',
                'status' => 'out_for_delivery',
                'subtotal' => 67.97,
                'tax_amount' => 6.12,
                'total_amount' => 74.09,
                'notes' => 'توصيل بدون تلامس لو سمحتم.',
                'created_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'user_id' => $activeCustomers[4]->id,
                'table_id' => 5,
                'type' => 'dine_in',
                'status' => 'received',
                'subtotal' => 234.89,
                'tax_amount' => 21.14,
                'total_amount' => 256.03,
                'notes' => 'طلب عائلي كبير – توجد حساسية.',
                'created_at' => Carbon::now()->subMinutes(5),
            ],
            [
                'user_id' => $activeCustomers[5]->id,
                'table_id' => null,
                'type' => 'delivery',
                'status' => 'delivery_failed',
                'subtotal' => 29.99,
                'tax_amount' => 2.70,
                'total_amount' => 32.69,
                'notes' => 'فشل التوصيل – العنوان غير متاح.',
                'created_at' => Carbon::now()->subHours(1),
            ],
        ];

        foreach ($orders as $orderData) {
            Order::create($orderData);
        }

        // Order items (kept dish_id mapping intact)
        $orderItems = [
            // Order 1 items
            [
                'order_id' => 1,
                'dish_id' => 1, // Hummus
                'quantity' => 1,
                'unit_price' => 5.99,
                'total_price' => 5.99,
                'status' => 'served',
                'special_instructions' => 'زيت زيتون إضافي',
            ],
            [
                'order_id' => 1,
                'dish_id' => 9, // Shish Tawook
                'quantity' => 1,
                'unit_price' => 14.99,
                'total_price' => 14.99,
                'status' => 'served',
                'special_instructions' => 'ناضج جيداً',
            ],
            [
                'order_id' => 1,
                'dish_id' => 15, // Knafeh Nabulsieh
                'quantity' => 1,
                'unit_price' => 8.99,
                'total_price' => 8.99,
                'status' => 'served',
                'special_instructions' => null,
            ],

            // Order 2 items
            [
                'order_id' => 2,
                'dish_id' => 2, // Baba Ghanoush
                'quantity' => 2,
                'unit_price' => 6.49,
                'total_price' => 12.98,
                'status' => 'served',
                'special_instructions' => null,
            ],
            [
                'order_id' => 2,
                'dish_id' => 10, // Lamb Kebab
                'quantity' => 1,
                'unit_price' => 16.99,
                'total_price' => 16.99,
                'status' => 'preparing',
                'special_instructions' => 'متوسط',
            ],
            [
                'order_id' => 2,
                'dish_id' => 11, // Kibbeh Labanieh
                'quantity' => 1,
                'unit_price' => 15.99,
                'total_price' => 15.99,
                'status' => 'preparing',
                'special_instructions' => 'صلصة لبن إضافية',
            ],
            [
                'order_id' => 2,
                'dish_id' => 5, // Tabbouleh
                'quantity' => 1,
                'unit_price' => 7.49,
                'total_price' => 7.49,
                'status' => 'pending',
                'special_instructions' => 'ليمون إضافي',
            ],
            [
                'order_id' => 2,
                'dish_id' => 17, // Syrian Tea with Mint
                'quantity' => 2,
                'unit_price' => 2.99,
                'total_price' => 5.98,
                'status' => 'served',
                'special_instructions' => null,
            ],

            // Order 3 items
            [
                'order_id' => 3,
                'dish_id' => 3, // Falafel
                'quantity' => 1,
                'unit_price' => 7.99,
                'total_price' => 7.99,
                'status' => 'ready',
                'special_instructions' => 'صلصة طحينة إضافية',
            ],
            [
                'order_id' => 3,
                'dish_id' => 6, // Fattoush
                'quantity' => 1,
                'unit_price' => 7.99,
                'total_price' => 7.99,
                'status' => 'ready',
                'special_instructions' => null,
            ],

            // Order 4 items
            [
                'order_id' => 4,
                'dish_id' => 12, // Molokhia
                'quantity' => 1,
                'unit_price' => 13.99,
                'total_price' => 13.99,
                'status' => 'ready',
                'special_instructions' => 'أرز إضافي',
            ],
            [
                'order_id' => 4,
                'dish_id' => 4, // Lentil Soup
                'quantity' => 1,
                'unit_price' => 6.99,
                'total_price' => 6.99,
                'status' => 'ready',
                'special_instructions' => 'ليمون إضافي',
            ],
            [
                'order_id' => 4,
                'dish_id' => 15, // Knafeh
                'quantity' => 1,
                'unit_price' => 8.99,
                'total_price' => 8.99,
                'status' => 'ready',
                'special_instructions' => null,
            ],
            [
                'order_id' => 4,
                'dish_id' => 16, // Ayran
                'quantity' => 2,
                'unit_price' => 3.99,
                'total_price' => 7.98,
                'status' => 'ready',
                'special_instructions' => 'بارد جداً',
            ],

            // Order 5 items
            [
                'order_id' => 5,
                'dish_id' => 13, // Cheese Manakeesh
                'quantity' => 3,
                'unit_price' => 5.99,
                'total_price' => 17.97,
                'status' => 'pending',
                'special_instructions' => 'واحدة جبنة إضافية',
            ],
            [
                'order_id' => 5,
                'dish_id' => 1, // Hummus
                'quantity' => 2,
                'unit_price' => 5.99,
                'total_price' => 11.98,
                'status' => 'pending',
                'special_instructions' => 'خبز إضافي',
            ],
            [
                'order_id' => 5,
                'dish_id' => 9, // Shish Tawook
                'quantity' => 2,
                'unit_price' => 14.99,
                'total_price' => 29.98,
                'status' => 'pending',
                'special_instructions' => 'واحد متوسط وواحد مستوي جيداً',
            ],
            [
                'order_id' => 5,
                'dish_id' => 10, // Lamb Kebab
                'quantity' => 1,
                'unit_price' => 16.99,
                'total_price' => 16.99,
                'status' => 'pending',
                'special_instructions' => 'نص استواء',
            ],
            [
                'order_id' => 5,
                'dish_id' => 5, // Tabbouleh
                'quantity' => 1,
                'unit_price' => 7.49,
                'total_price' => 7.49,
                'status' => 'pending',
                'special_instructions' => 'حجم كبير',
            ],
            [
                'order_id' => 5,
                'dish_id' => 17, // Tea with Mint
                'quantity' => 4,
                'unit_price' => 2.99,
                'total_price' => 11.96,
                'status' => 'pending',
                'special_instructions' => null,
            ],

            // Order 6 items (failed delivery)
            [
                'order_id' => 6,
                'dish_id' => 12, // Molokhia
                'quantity' => 1,
                'unit_price' => 13.99,
                'total_price' => 13.99,
                'status' => 'ready',
                'special_instructions' => null,
            ],
        ];

        foreach ($orderItems as $itemData) {
            OrderItem::create($itemData);
        }

        $this->command->info('Enhanced Syrian mock data seeded successfully!');
        $this->command->info('=====================================');
        $this->command->info('ADMIN ACCOUNTS:');
        $this->command->info('Email: admin@shami-restaurant.com | Password: admin123');
        $this->command->info('=====================================');
        $this->command->info('MANAGER ACCOUNTS:');
        $this->command->info('Email: manager1@shami-restaurant.com | Password: manager123');
        $this->command->info('Email: manager2@shami-restaurant.com | Password: manager123');
        $this->command->info('Email: mimoali@shami-restaurant.com | Password: manager123');
        $this->command->info('=====================================');
        $this->command->info('CASHIER ACCOUNTS:');
        $this->command->info('Email: cashier1@shami-restaurant.com | Password: cashier123');
        $this->command->info('Email: cashier2@shami-restaurant.com | Password: cashier123');
        $this->command->info('Email: cashier3@shami-restaurant.com | Password: cashier123');
        $this->command->info('=====================================');
        $this->command->info('CUSTOMER ACCOUNTS (Active):');
        $this->command->info('Email: ahmad.hamwi@customer.com | Password: customer123');
        $this->command->info('Email: rana.kheir@customer.com | Password: customer123');
        $this->command->info('Email: samer.qabbani@customer.com | Password: customer123');
        $this->command->info('Email: nour.sabbagh@customer.com | Password: customer123');
        $this->command->info('Email: maha.saadeh@customer.com | Password: customer123');
        $this->command->info('Email: khaled.masri@customer.com | Password: customer123');
        $this->command->info('Email: dalia.rafeh@customer.com | Password: customer123');
        $this->command->info('Email: yazan.hariri@customer.com | Password: customer123');
        $this->command->info('=====================================');
        $this->command->info('BANNED CUSTOMERS:');
        $this->command->info('Email: riad.dakkak@customer.com (5 failed deliveries)');
        $this->command->info('Email: hind.sheikh@customer.com (4 failed deliveries)');
        $this->command->info('=====================================');
        $this->command->info('DATABASE CONTAINS:');
        $this->command->info('- ' . User::count() . ' users total');
        $this->command->info('- ' . User::where('is_banned', true)->count() . ' banned users');
        $this->command->info('- ' . Table::count() . ' restaurant tables');
        $this->command->info('- ' . Category::count() . ' menu categories');
        $this->command->info('- ' . Dish::count() . ' dishes');
        $this->command->info('- ' . Reservation::count() . ' reservations');
        $this->command->info('- ' . Order::count() . ' orders');
        $this->command->info('- ' . OrderItem::count() . ' order items');

        // Dish reviews (IDs preserved)
        $this->createDishReviews();

        // Discounts (1 day, IDs preserved)
        $this->createDiscounts();

        $this->command->info('- ' . DishReview::count() . ' dish reviews');
        $this->command->info('- ' . Dish::where('is_on_discount', true)->count() . ' dishes on discount');
    }

    private function createDishReviews()
    {
        $customers = User::whereHas('roles', function($query) {
            $query->where('name', 'customer');
        })->where('is_banned', false)->get();

        $dishes = Dish::all();

        $reviewsData = [
            // High rated dishes for recommendations
            ['dish_id' => 1, 'ratings' => [5, 5, 4, 5, 4]], // Hummus
            ['dish_id' => 9, 'ratings' => [5, 5, 5, 4, 5]], // Shish Tawook
            ['dish_id' => 10, 'ratings' => [4, 5, 5, 5, 4]], // Lamb Kebab
            ['dish_id' => 11, 'ratings' => [5, 4, 5, 5, 5]], // Kibbeh Labanieh
            ['dish_id' => 12, 'ratings' => [4, 5, 4, 5, 5]], // Molokhia

            // Medium rated dishes
            ['dish_id' => 2, 'ratings' => [4, 3, 4, 4, 3]], // Baba Ghanoush
            ['dish_id' => 4, 'ratings' => [3, 4, 4, 3, 4]], // Lentil Soup
            ['dish_id' => 13, 'ratings' => [4, 4, 3, 4, 4]], // Cheese Manakeesh

            // Lower rated dishes
            ['dish_id' => 3, 'ratings' => [3, 3, 2, 3, 4]], // Falafel
            ['dish_id' => 6, 'ratings' => [2, 3, 3, 2, 3]], // Fattoush
        ];

        $comments = [
            'Absolutely delicious! Will definitely order again.',
            'Perfect flavors and presentation. Highly recommended!',
            'Good dish but a bit overpriced for the portion size.',
            'Amazing taste! The chef really knows what they\'re doing.',
            'Fresh ingredients and excellent preparation.',
            'Not bad, but I\'ve had better elsewhere.',
            'Outstanding! This is why I keep coming back.',
            'Decent food, service could be improved.',
            'Exceptional quality and taste. Worth every penny!',
            'Good but nothing special. Average experience.',
            'Fantastic! The best dish I\'ve tried here.',
            'Okay meal, met expectations but didn\'t exceed them.',
            'Incredible flavors! Chef\'s special indeed.',
            'Fair quality for the price point.',
            'Superb! This restaurant never disappoints.',
        ];

        foreach ($reviewsData as $dishData) {
            $dishId = $dishData['dish_id'];
            $ratings = $dishData['ratings'];

            foreach ($ratings as $index => $rating) {
                if ($index < count($customers)) {
                    $customer = $customers[$index];

                    DishReview::create([
                        'dish_id' => $dishId,
                        'user_id' => $customer->id,
                        'rating' => $rating,
                        'comment' => $comments[array_rand($comments)],
                        'created_at' => Carbon::now()->subDays(rand(1, 30)),
                        'updated_at' => Carbon::now()->subDays(rand(1, 30)),
                    ]);
                }
            }
        }
    }

    private function createDiscounts()
    {
        // Apply discounts to some dishes (always one day as requested)
        $discountedDishes = [
            ['dish_id' => 2, 'percentage' => 25.00], // Baba Ghanoush - 25% off
            ['dish_id' => 6, 'percentage' => 30.00], // Fattoush - 30% off
            ['dish_id' => 13, 'percentage' => 20.00], // Cheese Manakeesh - 20% off
            ['dish_id' => 15, 'percentage' => 15.00], // Knafeh Nabulsieh - 15% off
            ['dish_id' => 3, 'percentage' => 35.00], // Falafel - 35% off
        ];

        foreach ($discountedDishes as $discountData) {
            $dish = Dish::find($discountData['dish_id']);
            if ($dish) {
                $dish->update([
                    'discount_percentage' => $discountData['percentage'],
                    'discount_start_date' => Carbon::now(),
                    'discount_end_date' => Carbon::now()->addDay(),
                    'is_on_discount' => true
                ]);
            }
        }
    }
}
