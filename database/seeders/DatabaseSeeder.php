<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Faq;
use App\Models\Newsletter;
use App\Models\Page;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use App\Models\QuickAction;
use App\Models\SiteSetting;
use App\Models\Slider;
use App\Models\Statistic;
use App\Models\Story;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        User::query()->updateOrCreate(
            ['email' => 'admin@sahem.org'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'role' => 'super_admin',
                'is_active' => true,
                'preferred_locale' => 'ar',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@etelafrelief.org'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'role' => 'super_admin',
                'is_active' => true,
                'preferred_locale' => 'en',
            ]
        );

        SiteSetting::query()->updateOrCreate([], [
            'site_name' => [
                'ar' => 'ساهم',
                'en' => 'Sahem',
                'es' => 'Sahem',
                'id' => 'Sahem',
                'tr' => 'Sahem',
            ],
            'tagline' => [
                'ar' => 'منصة التبرع والإغاثة الإنسانية',
                'en' => 'Humanitarian Contribution Platform',
                'es' => 'Plataforma humanitaria de contribución',
                'id' => 'Platform Kontribusi Kemanusiaan',
                'tr' => 'İnsani Yardım Platformu',
            ],
            'hero_title' => [
                'ar' => 'معاً لدعم أهلنا النازحين',
                'en' => 'Together to Support Our Displaced People',
                'es' => 'Juntos para apoyar a nuestros desplazados',
                'id' => 'Bersama Mendukung Pengungsi Kami',
                'tr' => 'Yerinden Edilen Halkımızı Desteklemek İçin Birlikte',
            ],
            'hero_subtitle' => [
                'ar' => 'نعمل من أجل تقديم الدعم الإنساني والإغاثي للأسر المتضررة في قطاع غزة',
                'en' => 'We work to provide humanitarian and relief support to affected families in Gaza',
                'es' => 'Trabajamos para brindar apoyo humanitario a las familias afectadas en Gaza',
                'id' => 'Kami bekerja memberikan dukungan kemanusiaan kepada keluarga yang terkena dampak di Gaza',
                'tr' => 'Gazze\'deki etkilenen ailelere insani yardım sağlamak için çalışıyoruz',
            ],
            'about_title' => [
                'ar' => 'ساهم',
                'en' => 'Sahem',
                'es' => 'Sahem',
                'id' => 'Sahem',
                'tr' => 'Sahem',
            ],
            'about_content' => [
                'ar' => 'ساهم منصة إنسانية تجمع المتبرعين والمتطوعين والمؤسسات لتقديم الدعم الإغاثي للأسر المتضررة في قطاع غزة.',
                'en' => 'Sahem is a humanitarian platform connecting donors, volunteers, and organizations to deliver relief to affected families in Gaza.',
                'es' => 'Sahem es una plataforma humanitaria que conecta donantes, voluntarios y organizaciones para brindar ayuda a familias afectadas en Gaza.',
                'id' => 'Sahem adalah platform kemanusiaan yang menghubungkan donatur, relawan, dan organisasi untuk memberikan bantuan kepada keluarga yang terkena dampak di Gaza.',
                'tr' => 'Sahem, Gazze\'deki etkilenen ailelere yardım ulaştırmak için bağışçıları, gönüllüleri ve kuruluşları bir araya getiren insani bir platformdur.',
            ],
            'about_features' => [
                'ar' => ['شفافية في التبرعات والتوزيع', 'شراكات مع منظمات محلية ودولية', 'فرق ميدانية في مراكز التوزيع'],
                'en' => ['Transparency in donations', 'Local and international partnerships', 'Field teams at distribution centers'],
                'es' => ['Transparencia en donaciones', 'Alianzas locales e internacionales', 'Equipos de campo en centros de distribución'],
                'id' => ['Transparansi dalam donasi', 'Kemitraan lokal dan internasional', 'Tim lapangan di pusat distribusi'],
                'tr' => ['Bağışlarda şeffaflık', 'Yerel ve uluslararası ortaklıklar', 'Dağıtım merkezlerinde saha ekipleri'],
            ],
            'donate_title' => [
                'ar' => 'ساهم في إنقاذ حياة',
                'en' => 'Help Save Lives',
                'es' => 'Ayuda a salvar vidas',
                'id' => 'Bantu Selamatkan Jiwa',
                'tr' => 'Hayat Kurtarmaya Yardım Edin',
            ],
            'donate_description' => [
                'ar' => 'كل تبرع يصل مباشرة إلى الأسر المحتاجة. نضمن الشفافية في صرف التبرعات.',
                'en' => 'Every donation reaches families in need. We ensure transparency.',
                'es' => 'Cada donación llega a las familias necesitadas con total transparencia.',
                'id' => 'Setiap donasi sampai ke keluarga yang membutuhkan. Kami menjamin transparansi.',
                'tr' => 'Her bağış ihtiyaç sahibi ailelere ulaşır. Şeffaflığı garanti ediyoruz.',
            ],
            'donate_methods' => [
                'ar' => ['تحويل بنكي', 'بطاقات ائتمان', 'PayPal / Wise'],
                'en' => ['Bank transfer', 'Credit cards', 'PayPal / Wise'],
                'es' => ['Transferencia bancaria', 'Tarjetas de crédito', 'PayPal / Wise'],
                'id' => ['Transfer bank', 'Kartu kredit', 'PayPal / Wise'],
                'tr' => ['Banka havalesi', 'Kredi kartları', 'PayPal / Wise'],
            ],
            'footer_description' => [
                'ar' => 'نعمل من أجل تخفيف المعاناة الإنسانية وتقديم الدعم الإغاثي للأسر المتضررة في قطاع غزة.',
                'en' => 'Working to alleviate humanitarian suffering and provide relief in Gaza.',
                'es' => 'Trabajamos para aliviar el sufrimiento humanitario en Gaza.',
                'id' => 'Bekerja untuk mengurangi penderitaan kemanusiaan dan memberikan bantuan di Gaza.',
                'tr' => 'Gazze\'de insani acıyı hafifletmek ve yardım sağlamak için çalışıyoruz.',
            ],
            'phone' => '+972 59 918 4228',
            'email' => 'info@sahem.org',
            'whatsapp' => '+972599184228',
            'twitter' => 'https://twitter.com/sahem',
            'facebook' => 'https://facebook.com/sahem',
        ]);

        $quickActions = [
            ['icon' => '💚', 'title' => ['ar' => 'تبرع الآن', 'en' => 'Donate Now', 'es' => 'Donar ahora', 'id' => 'Donasi Sekarang', 'tr' => 'Şimdi Bağış Yap'], 'description' => ['ar' => 'ساهم في دعم الأسر المتضررة', 'en' => 'Support affected families', 'es' => 'Apoya a familias afectadas', 'id' => 'Dukung keluarga yang terkena dampak', 'tr' => 'Etkilenen aileleri destekleyin'], 'link' => '#donate'],
            ['icon' => '💳', 'title' => ['ar' => 'طرق الدفع', 'en' => 'Payment Methods', 'es' => 'Métodos de pago', 'id' => 'Metode Pembayaran', 'tr' => 'Ödeme Yöntemleri'], 'description' => ['ar' => 'وسائل الدفع المعتمدة', 'en' => 'Approved payment options', 'es' => 'Opciones de pago', 'id' => 'Opsi pembayaran', 'tr' => 'Onaylı ödeme seçenekleri'], 'link' => '#donate'],
            ['icon' => '✉️', 'title' => ['ar' => 'بريد المتبرعين', 'en' => 'Donor Mail', 'es' => 'Correo de donantes', 'id' => 'Email Donatur', 'tr' => 'Bağışçı Postası'], 'description' => ['ar' => 'استفسارات مالية', 'en' => 'Financial inquiries', 'es' => 'Consultas financieras', 'id' => 'Pertanyaan keuangan', 'tr' => 'Mali sorular'], 'link' => '#contact'],
            ['icon' => '🙋', 'title' => ['ar' => 'طلب تطوع', 'en' => 'Volunteer', 'es' => 'Voluntariado', 'id' => 'Relawan', 'tr' => 'Gönüllü Ol'], 'description' => ['ar' => 'انضم لفريقنا', 'en' => 'Join our team', 'es' => 'Únete al equipo', 'id' => 'Bergabung dengan tim kami', 'tr' => 'Ekibimize katılın'], 'link' => '#contact'],
        ];
        foreach ($quickActions as $i => $data) {
            QuickAction::query()->updateOrCreate(['sort_order' => $i + 1], [...$data, 'sort_order' => $i + 1, 'is_active' => true]);
        }

        $achievements = [
            [560, null, ['ar' => 'مبادرة إنسانية', 'en' => 'Humanitarian initiatives', 'es' => 'Iniciativas humanitarias', 'id' => 'Inisiatif kemanusiaan', 'tr' => 'İnsani girişim']],
            [105, null, ['ar' => 'شريكاً خارجياً', 'en' => 'External partners', 'es' => 'Socios externos', 'id' => 'Mitra eksternal', 'tr' => 'Dış ortak']],
            [25, null, ['ar' => 'اتفاقية تعاون', 'en' => 'Cooperation agreements', 'es' => 'Acuerdos de cooperación', 'id' => 'Perjanjian kerja sama', 'tr' => 'İşbirliği anlaşması']],
            [50, null, ['ar' => 'مركز توزيع', 'en' => 'Distribution centers', 'es' => 'Centros de distribución', 'id' => 'Pusat distribusi', 'tr' => 'Dağıtım merkezi']],
        ];
        foreach ($achievements as $i => [$value, $prefix, $label]) {
            Statistic::query()->updateOrCreate(
                ['type' => Statistic::TYPE_ACHIEVEMENT, 'sort_order' => $i + 1],
                ['value' => $value, 'prefix' => $prefix, 'label' => $label, 'is_active' => true]
            );
        }

        $humanitarian = [
            [52000, '+', ['ar' => 'شهيد', 'en' => 'Martyrs', 'es' => 'Mártires', 'id' => 'Syuhada', 'tr' => 'Şehit']],
            [118000, '+', ['ar' => 'مصاب', 'en' => 'Injured', 'es' => 'Heridos', 'id' => 'Terluka', 'tr' => 'Yaralı']],
            [310000, '+', ['ar' => 'منزل متضرر', 'en' => 'Damaged homes', 'es' => 'Hogares dañados', 'id' => 'Rumah rusak', 'tr' => 'Hasarlı ev']],
            [10000, '+', ['ar' => 'مفقود', 'en' => 'Missing', 'es' => 'Desaparecidos', 'id' => 'Hilang', 'tr' => 'Kayıp']],
            [22, null, ['ar' => 'مستشفى خارج الخدمة', 'en' => 'Hospitals out of service', 'es' => 'Hospitales fuera de servicio', 'id' => 'Rumah sakit tidak berfungsi', 'tr' => 'Hizmet dışı hastane']],
        ];
        foreach ($humanitarian as $i => [$value, $prefix, $label]) {
            Statistic::query()->updateOrCreate(
                ['type' => Statistic::TYPE_HUMANITARIAN, 'sort_order' => $i + 1],
                ['value' => $value, 'prefix' => $prefix, 'label' => $label, 'is_active' => true]
            );
        }

        Project::query()->updateOrCreate(['slug' => 'meat-distribution'], [
            'title' => ['ar' => 'مشروع توزيع لحوم', 'en' => 'Meat Distribution Project', 'es' => 'Proyecto de distribución de carne', 'id' => 'Proyek Distribusi Daging', 'tr' => 'Et Dağıtım Projesi'],
            'description' => ['ar' => 'توفير اللحوم والدواجن للأسر النازحة', 'en' => 'Providing meat and poultry to displaced families', 'es' => 'Carne y aves para familias desplazadas', 'id' => 'Menyediakan daging dan unggas untuk keluarga pengungsi', 'tr' => 'Yerinden edilmiş ailelere et ve kümes hayvanları sağlanması'],
            'is_featured' => true, 'sort_order' => 1, 'is_active' => true,
        ]);
        Project::query()->updateOrCreate(['slug' => 'collective-joy'], [
            'title' => ['ar' => 'مشروع الفرح الجماعي', 'en' => 'Collective Joy Project', 'es' => 'Proyecto Alegría Colectiva', 'id' => 'Proyek Kegembiraan Kolektif', 'tr' => 'Toplu Mutluluk Projesi'],
            'description' => ['ar' => 'دعم الشباب المقبلين على الزواج', 'en' => 'Supporting young couples', 'es' => 'Apoyo a jóvenes matrimonios', 'id' => 'Mendukung pasangan muda', 'tr' => 'Genç çiftlere destek'],
            'is_featured' => true, 'sort_order' => 2, 'is_active' => true,
        ]);
        Project::query()->updateOrCreate(['slug' => 'water-well'], [
            'title' => ['ar' => 'مشروع حفر الآبار', 'en' => 'Water Well Project', 'es' => 'Proyecto de pozos de agua', 'id' => 'Proyek Sumur Air', 'tr' => 'Su Kuyusu Projesi'],
            'description' => ['ar' => 'توفير مياه الشرب النظيفة للأسر النازحة', 'en' => 'Providing clean drinking water to displaced families', 'es' => 'Agua potable para familias desplazadas', 'id' => 'Menyediakan air minum bersih untuk keluarga pengungsi', 'tr' => 'Yerinden edilmiş ailelere temiz içme suyu sağlanması'],
            'is_featured' => true, 'sort_order' => 3, 'is_active' => true,
        ]);
        Project::query()->updateOrCreate(['slug' => 'winter-aid'], [
            'title' => ['ar' => 'مشروع المساعدات الشتوية', 'en' => 'Winter Aid Project', 'es' => 'Proyecto de ayuda invernal', 'id' => 'Proyek Bantuan Musim Dingin', 'tr' => 'Kış Yardımı Projesi'],
            'description' => ['ar' => 'توزيع البطانيات والملابس الشتوية', 'en' => 'Distributing blankets and winter clothes', 'es' => 'Distribución de mantas y ropa de invierno', 'id' => 'Mendistribusikan selimut dan pakaian musim dingin', 'tr' => 'Battaniye ve kışlık kıyafet dağıtımı'],
            'is_featured' => true, 'sort_order' => 4, 'is_active' => true,
        ]);

        $programs = [
            ['⛺', ['ar' => 'توزيع الخيام', 'en' => 'Tent Distribution', 'es' => 'Distribución de tiendas', 'id' => 'Distribusi Tenda', 'tr' => 'Çadır Dağıtımı'], ['ar' => 'توفير الخيام للأسر النازحة', 'en' => 'Tents for displaced families', 'es' => 'Tiendas para familias desplazadas', 'id' => 'Tenda untuk keluarga pengungsi', 'tr' => 'Yerinden edilmiş aileler için çadır']],
            ['💧', ['ar' => 'سقيا الماء', 'en' => 'Water Supply', 'es' => 'Suministro de agua', 'id' => 'Pasokan Air', 'tr' => 'Su Temini'], ['ar' => 'مياه صالحة للشرب', 'en' => 'Clean drinking water', 'es' => 'Agua potable', 'id' => 'Air minum bersih', 'tr' => 'Temiz içme suyu']],
            ['💵', ['ar' => 'مساعدات مالية', 'en' => 'Cash Assistance', 'es' => 'Ayuda en efectivo', 'id' => 'Bantuan Tunai', 'tr' => 'Nakit Yardım'], ['ar' => 'دعم نقدي عاجل', 'en' => 'Urgent cash support', 'es' => 'Apoyo en efectivo urgente', 'id' => 'Dukungan tunai mendesak', 'tr' => 'Acil nakit desteği']],
            ['🍞', ['ar' => 'المخابز', 'en' => 'Bakeries', 'es' => 'Panaderías', 'id' => 'Toko Roti', 'tr' => 'Fırınlar'], ['ar' => 'تشغيل المخابز وتوزيع الخبز', 'en' => 'Operating bakeries and distributing bread', 'es' => 'Panaderías y distribución de pan', 'id' => 'Mengoperasikan toko roti dan mendistribusikan roti', 'tr' => 'Fırınların işletilmesi ve ekmek dağıtımı']],
            ['🏥', ['ar' => 'الدعم الصحي', 'en' => 'Health Support', 'es' => 'Apoyo sanitario', 'id' => 'Dukungan Kesehatan', 'tr' => 'Sağlık Desteği'], ['ar' => 'دعم المستشفيات والمراكز الصحية', 'en' => 'Supporting hospitals and health centers', 'es' => 'Apoyo a hospitales y centros de salud', 'id' => 'Mendukung rumah sakit dan pusat kesehatan', 'tr' => 'Hastane ve sağlık merkezlerine destek']],
            ['📚', ['ar' => 'التعليم', 'en' => 'Education', 'es' => 'Educación', 'id' => 'Pendidikan', 'tr' => 'Eğitim'], ['ar' => 'دعم العملية التعليمية للأطفال', 'en' => 'Supporting education for children', 'es' => 'Apoyo educativo para niños', 'id' => 'Mendukung pendidikan anak-anak', 'tr' => 'Çocuklar için eğitim desteği']],
        ];
        foreach ($programs as $i => [$icon, $title, $desc]) {
            Program::query()->updateOrCreate(['sort_order' => $i + 1], [
                'icon' => $icon, 'title' => $title, 'description' => $desc, 'is_active' => true,
            ]);
        }

        Page::query()->updateOrCreate(['slug' => 'about'], [
            'title' => ['ar' => 'من نحن', 'en' => 'About Us', 'es' => 'Sobre nosotros', 'id' => 'Tentang Kami', 'tr' => 'Hakkımızda'],
            'content' => ['ar' => '<p>منصة ساهم هي منصة إنسانية تهدف إلى جمع التبرعات وتقديم الدعم الإغاثي للأسر المتضررة في قطاع غزة.</p>', 'en' => '<p>Sahem is a humanitarian platform aimed at collecting donations and providing relief support to affected families in Gaza.</p>', 'es' => '<p>Sahem es una plataforma humanitaria destinada a recaudar donaciones y brindar apoyo de socorro a las familias afectadas en Gaza.</p>', 'id' => '<p>Sahem adalah platform kemanusiaan yang bertujuan mengumpulkan donasi dan memberikan dukungan bantuan kepada keluarga yang terkena dampak di Gaza.</p>', 'tr' => '<p>Sahem, Gazze\'deki etkilenen ailelere bağış toplamak ve yardım desteği sağlamak amacıyla oluşturulmuş insani bir platformdur.</p>'],
            'is_active' => true,
        ]);

        Page::query()->updateOrCreate(['slug' => 'contact'], [
            'title' => ['ar' => 'اتصل بنا', 'en' => 'Contact Us', 'es' => 'Contáctenos', 'id' => 'Hubungi Kami', 'tr' => 'Bize Ulaşın'],
            'content' => ['ar' => '<p>يمكنكم التواصل معنا عبر البريد الإلكتروني أو الهاتف أو واتساب.</p>', 'en' => '<p>You can contact us via email, phone, or WhatsApp.</p>', 'es' => '<p>Puede contactarnos por correo electrónico, teléfono o WhatsApp.</p>', 'id' => '<p>Anda dapat menghubungi kami melalui email, telepon, atau WhatsApp.</p>', 'tr' => '<p>Bize e-posta, telefon veya WhatsApp ile ulaşabilirsiniz.</p>'],
            'is_active' => true,
        ]);

        Post::query()->updateOrCreate(['slug' => 'aid-500-families'], [
            'type' => Post::TYPE_ANNOUNCEMENT,
            'title' => ['ar' => 'مساعدات عاجلة لأكثر من 500 أسرة', 'en' => 'Urgent aid for 500+ families', 'es' => 'Ayuda urgente para más de 500 familias', 'id' => 'Bantuan mendesak untuk 500+ keluarga', 'tr' => '500+ aile için acil yardım'],
            'published_at' => now()->subDays(16), 'is_active' => true,
        ]);
        Post::query()->updateOrCreate(['slug' => 'north-gaza-campaign'], [
            'type' => Post::TYPE_ANNOUNCEMENT,
            'title' => ['ar' => 'حملة إغاثية شمال قطاع غزة', 'en' => 'Relief campaign in north Gaza', 'es' => 'Campaña de ayuda en el norte de Gaza', 'id' => 'Kampanye bantuan di Gaza utara', 'tr' => 'Kuzey Gazze\'de yardım kampanyası'],
            'published_at' => now()->subDays(19), 'is_active' => true,
        ]);
        Post::query()->updateOrCreate(['slug' => 'winter-aid-2025'], [
            'type' => Post::TYPE_ANNOUNCEMENT,
            'title' => ['ar' => 'حملة المساعدات الشتوية 2025', 'en' => 'Winter Aid Campaign 2025', 'es' => 'Campaña de ayuda invernal 2025', 'id' => 'Kampanye Bantuan Musim Dingin 2025', 'tr' => '2025 Kış Yardımı Kampanyası'],
            'published_at' => now()->subDays(5), 'is_active' => true,
        ]);

        Post::query()->updateOrCreate(['slug' => 'environment-project'], [
            'type' => Post::TYPE_SUCCESS_STORY,
            'title' => ['ar' => 'مشروع بيئة أفضل استهدف 460 أسرة', 'en' => 'Better environment project helped 460 families', 'es' => 'Proyecto ambiental ayudó a 460 familias', 'id' => 'Proyek lingkungan yang lebih baik membantu 460 keluarga', 'tr' => 'Daha iyi çevre projesi 460 aileye yardım etti'],
            'published_at' => now()->subDays(41), 'is_active' => true,
        ]);
        Post::query()->updateOrCreate(['slug' => 'food-support-3000'], [
            'type' => Post::TYPE_SUCCESS_STORY,
            'title' => ['ar' => 'دعم غذائي لأكثر من 3000 أسرة', 'en' => 'Food support for 3000+ families', 'es' => 'Apoyo alimentario para más de 3000 familias', 'id' => 'Dukungan pangan untuk 3000+ keluarga', 'tr' => '3000+ aile için gıda desteği'],
            'published_at' => now()->subDays(60), 'is_active' => true,
        ]);

        Slider::query()->updateOrCreate(['sort_order' => 1], [
            'title' => ['ar' => 'معاً لدعم أهلنا في غزة', 'en' => 'Together for Gaza', 'es' => 'Juntos por Gaza', 'id' => 'Bersama untuk Gaza', 'tr' => 'Gazze İçin Birlikte'],
            'subtitle' => ['ar' => 'تبرع الآن وساهم في إنقاذ الأرواح', 'en' => 'Donate now and help save lives', 'es' => 'Done ahora y ayuda a salvar vidas', 'id' => 'Donasi sekarang dan bantu selamatkan jiwa', 'tr' => 'Şimdi bağış yapın ve hayat kurtarmaya yardım edin'],
            'button_text' => ['ar' => 'تبرع الآن', 'en' => 'Donate Now', 'es' => 'Donar ahora', 'id' => 'Donasi Sekarang', 'tr' => 'Şimdi Bağış Yap'],
            'button_link' => '#donate',
            'is_active' => true,
        ]);

        Campaign::query()->updateOrCreate(['slug' => 'emergency-relief'], [
            'title' => ['ar' => 'حملة الإغاثة العاجلة', 'en' => 'Emergency Relief Campaign', 'es' => 'Campaña de ayuda de emergencia', 'id' => 'Kampanye Bantuan Darurat', 'tr' => 'Acil Yardım Kampanyası'],
            'description' => ['ar' => 'دعم عاجل للأسر المتضررة في قطاع غزة', 'en' => 'Urgent support for affected families in Gaza', 'es' => 'Apoyo urgente a familias afectadas en Gaza', 'id' => 'Dukungan mendesak untuk keluarga yang terkena dampak di Gaza', 'tr' => 'Gazze\'deki etkilenen aileler için acil destek'],
            'goal_amount' => 500000,
            'raised_amount' => 0,
            'is_active' => true,
        ]);

        Campaign::query()->updateOrCreate(['slug' => 'winter-aid-2025'], [
            'title' => ['ar' => 'حملة المساعدات الشتوية', 'en' => 'Winter Aid Campaign', 'es' => 'Campaña de ayuda invernal', 'id' => 'Kampanye Bantuan Musim Dingin', 'tr' => 'Kış Yardımı Kampanyası'],
            'description' => ['ar' => 'توزيع البطانيات والملابس الشتوية والوقود', 'en' => 'Distributing blankets, winter clothes, and fuel', 'es' => 'Distribución de mantas, ropa de invierno y combustible', 'id' => 'Mendistribusikan selimut, pakaian musim dingin, dan bahan bakar', 'tr' => 'Battaniye, kışlık kıyafet ve yakıt dağıtımı'],
            'goal_amount' => 250000,
            'raised_amount' => 0,
            'is_active' => true,
        ]);

        $bankGateway = PaymentGateway::query()->updateOrCreate(['driver' => 'bank_transfer'], [
            'name' => 'تحويل بنكي',
            'config' => ['bank_name' => 'البنك الإسلامي الفلسطيني', 'account_name' => 'مؤسسة إغاثة', 'account_number' => '1234567890'],
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $paypalGateway = PaymentGateway::query()->updateOrCreate(['driver' => 'paypal'], [
            'name' => 'PayPal',
            'config' => ['email' => 'paypal@etelaf-relief.org'],
            'sort_order' => 2,
            'is_active' => true,
        ]);
        $stripeGateway = PaymentGateway::query()->updateOrCreate(['driver' => 'stripe'], [
            'name' => 'Stripe',
            'config' => ['publishable_key' => 'pk_test_xxx', 'secret_key' => 'sk_test_xxx'],
            'sort_order' => 3,
            'is_active' => true,
        ]);
        $wiseGateway = PaymentGateway::query()->updateOrCreate(['driver' => 'wise'], [
            'name' => 'Wise',
            'config' => ['email' => 'wise@etelaf-relief.org', 'account_number' => 'WISE123456'],
            'sort_order' => 4,
            'is_active' => true,
        ]);

        PaymentMethod::query()->updateOrCreate(['sort_order' => 1], [
            'name' => 'تحويل بنكي',
            'description' => 'تحويل بنكي مباشر إلى حساب المنصة',
            'icon' => 'fas fa-university',
            'gateway_id' => $bankGateway->id,
            'instructions' => 'يرجى تحويل المبلغ إلى حساب البنك الإسلامي الفلسطيني رقم 1234567890',
            'is_active' => true,
        ]);
        PaymentMethod::query()->updateOrCreate(['sort_order' => 2], [
            'name' => 'PayPal',
            'description' => 'الدفع عبر PayPal',
            'icon' => 'fab fa-paypal',
            'gateway_id' => $paypalGateway->id,
            'is_active' => true,
        ]);
        PaymentMethod::query()->updateOrCreate(['sort_order' => 3], [
            'name' => 'بطاقة ائتمان',
            'description' => 'فيزا / ماستركارد',
            'icon' => 'fas fa-credit-card',
            'gateway_id' => $stripeGateway->id,
            'is_active' => true,
        ]);
        PaymentMethod::query()->updateOrCreate(['sort_order' => 4], [
            'name' => 'Wise',
            'description' => 'تحويل عبر Wise',
            'icon' => 'fas fa-money-bill-transfer',
            'gateway_id' => $wiseGateway->id,
            'is_active' => true,
        ]);

        $cryptoGateway = PaymentGateway::query()->updateOrCreate(['driver' => 'crypto'], [
            'name' => 'USDT (TRC20)',
            'config' => [
                'wallet_address' => 'TX8aR8g5QaQkqQ5X5Z5X5a5X5a5X5a5X5a5X5a5',
                'network' => 'TRC20',
                'currency_symbol' => 'USDT',
                'min_amount' => 10,
                'conversion_rate' => 1,
                'additional_info' => 'يرجى التأكد من اختيار شبكة TRC20 عند التحويل',
            ],
            'sort_order' => 5,
            'is_active' => true,
        ]);

        PaymentMethod::query()->updateOrCreate(['sort_order' => 5], [
            'name' => 'USDT (TRC20)',
            'description' => 'تبرع بعملة USDT عبر شبكة TRC20',
            'icon' => 'fab fa-bitcoin',
            'gateway_id' => $cryptoGateway->id,
            'instructions' => 'يرجى تحويل مبلغ USDT (TRC20) إلى عنوان المحفظة أدناه',
            'is_active' => true,
        ]);

        Story::query()->updateOrCreate(['sort_order' => 1], [
            'title' => ['ar' => 'أمل يعود إلى منزل مدمر', 'en' => 'Hope Returns to a Destroyed Home', 'es' => 'La esperanza regresa a un hogar destruido', 'id' => 'Harapan Kembali ke Rumah yang Hancur', 'tr' => 'Yıkılan Eve Umut Geri Dönüyor'],
            'content' => ['ar' => '<p>عائلة أبو عمر فقدت منزلها في القصف، لكن بفضل تبرعاتكم استطاعت العودة للحياة.</p>', 'en' => '<p>Abu Omar\'s family lost their home in the bombing, but thanks to your donations they were able to return to life.</p>', 'es' => '<p>La familia de Abu Omar perdió su hogar en los bombardeos, pero gracias a sus donaciones pudieron volver a la vida.</p>', 'id' => '<p>Keluarga Abu Omar kehilangan rumah mereka dalam pemboman, tetapi berkat donasi Anda mereka bisa kembali hidup.</p>', 'tr' => '<p>Ebu Ömer\'in ailesi bombalamada evlerini kaybetti, ancak bağışlarınız sayesinde hayata geri dönebildiler.</p>'],
            'person_name' => 'أحمد أبو عمر',
            'age' => '45',
            'location' => 'غزة',
            'is_active' => true,
        ]);
        Story::query()->updateOrCreate(['sort_order' => 2], [
            'title' => ['ar' => 'طفولة تحت الأنقاض', 'en' => 'Childhood Under the Rubble', 'es' => 'Infancia bajo los escombros', 'id' => 'Masa Kecil di Bawah Reruntuhan', 'tr' => 'Enkaz Altında Çocukluk'],
            'content' => ['ar' => '<p>سارة ذات الـ 10 أعوام فقدت كل شيء، لكنها مازالت تحلم بأن تصبح طبيبة.</p>', 'en' => '<p>Sara, 10 years old, lost everything but still dreams of becoming a doctor.</p>', 'es' => '<p>Sara, de 10 años, perdió todo pero aún sueña con ser médica.</p>', 'id' => '<p>Sara, 10 tahun, kehilangan segalanya namun masih bermimpi menjadi dokter.</p>', 'tr' => '<p>10 yaşındaki Sara her şeyini kaybetti ama hala doktor olmayı hayal ediyor.</p>'],
            'person_name' => 'سارة',
            'age' => '10',
            'location' => 'شمال غزة',
            'is_active' => true,
        ]);
        Story::query()->updateOrCreate(['sort_order' => 3], [
            'title' => ['ar' => 'أم تحارب من أجل أطفالها', 'en' => 'A Mother Fighting for Her Children', 'es' => 'Una madre luchando por sus hijos', 'id' => 'Seorang Ibu Berjuang untuk Anak-anaknya', 'tr' => 'Çocukları İçin Savaşan Bir Anne'],
            'content' => ['ar' => '<p>أم محمد تنام مع أطفالها في خيام النزوح، لكنها لم تفقد الأمل في غد أفضل.</p>', 'en' => '<p>Um Mohammed sleeps with her children in displacement tents, but she hasn\'t lost hope for a better tomorrow.</p>', 'es' => '<p>Um Mohammed duerme con sus hijos en tiendas de desplazados, pero no pierde la esperanza.</p>', 'id' => '<p>Um Mohammed tidur dengan anak-anaknya di tenda pengungsian, namun tidak kehilangan harapan.</p>', 'tr' => '<p>Ümmü Muhammed çocuklarıyla birlikte göç çadırlarında uyuyor, ancak daha iyi bir yarın için umudunu kaybetmedi.</p>'],
            'person_name' => 'أم محمد',
            'age' => '38',
            'location' => 'رفح',
            'is_active' => true,
        ]);

        Faq::query()->updateOrCreate(['sort_order' => 1], [
            'question' => ['ar' => 'كيف يمكنني التبرع؟', 'en' => 'How can I donate?', 'es' => '¿Cómo puedo donar?', 'id' => 'Bagaimana cara berdonasi?', 'tr' => 'Nasıl bağış yapabilirim?'],
            'answer' => ['ar' => 'يمكنك التبرع عبر النموذج الموجود في صفحة التبرع أو عبر التحويل البنكي.', 'en' => 'You can donate through the form on the donation page or via bank transfer.', 'es' => 'Puede donar a través del formulario en la página de donación o mediante transferencia bancaria.', 'id' => 'Anda dapat berdonasi melalui formulir di halaman donasi atau melalui transfer bank.', 'tr' => 'Bağış sayfasındaki form aracılığıyla veya banka havalesi yoluyla bağış yapabilirsiniz.'],
            'is_active' => true,
        ]);
        Faq::query()->updateOrCreate(['sort_order' => 2], [
            'question' => ['ar' => 'هل تبرعي آمن؟', 'en' => 'Is my donation secure?', 'es' => '¿Mi donación es segura?', 'id' => 'Apakah donasi saya aman?', 'tr' => 'Bağışım güvenli mi?'],
            'answer' => ['ar' => 'نعم، جميع معلومات الدفع مشفرة وآمنة تماماً.', 'en' => 'Yes, all payment information is encrypted and completely secure.', 'es' => 'Sí, toda la información de pago está encriptada y es completamente segura.', 'id' => 'Ya, semua informasi pembayaran dienkripsi dan sepenuhnya aman.', 'tr' => 'Evet, tüm ödeme bilgileri şifrelenmiştir ve tamamen güvenlidir.'],
            'is_active' => true,
        ]);
        Faq::query()->updateOrCreate(['sort_order' => 3], [
            'question' => ['ar' => 'كيف أتطوع معكم؟', 'en' => 'How can I volunteer?', 'es' => '¿Cómo puedo ser voluntario?', 'id' => 'Bagaimana cara menjadi relawan?', 'tr' => 'Nasıl gönüllü olabilirim?'],
            'answer' => ['ar' => 'يمكنك التسجيل عبر نموذج التطوع في صفحة الاتصال وسنتواصل معك.', 'en' => 'You can register through the volunteer form on the contact page and we will contact you.', 'es' => 'Puede registrarse a través del formulario de voluntariado en la página de contacto.', 'id' => 'Anda dapat mendaftar melalui formulir relawan di halaman kontak.', 'tr' => 'İletişim sayfasındaki gönüllü formu aracılığıyla kayıt olabilirsiniz.'],
            'is_active' => true,
        ]);

        Testimonial::query()->updateOrCreate(['donor_name' => 'أحمد علي'], [
            'content' => ['ar' => 'منصة رائعة وأشعر بالثقة عند التبرع عبرهم.', 'en' => 'Great platform, I feel confident donating through them.', 'es' => 'Excelente plataforma, me siento seguro donando.', 'id' => 'Platform yang bagus, saya percaya berdonasi melalui mereka.', 'tr' => 'Harika bir platform, onlar aracılığıyla bağış yaparken kendime güveniyorum.'],
            'rating' => 5,
            'is_active' => true,
        ]);

        Testimonial::query()->updateOrCreate(['donor_name' => 'Sara Johnson'], [
            'content' => ['ar' => 'سعيد جداً بدعم هذه القضية النبيلة.', 'en' => 'Very happy to support this noble cause.', 'es' => 'Muy feliz de apoyar esta noble causa.', 'id' => 'Sangat senang mendukung tujuan mulia ini.', 'tr' => 'Bu asil amacı desteklemekten çok mutluyum.'],
            'rating' => 5,
            'is_active' => true,
        ]);

        Volunteer::query()->create([
            'name' => 'خالد محمود',
            'email' => 'khalid@example.com',
            'phone' => '+970599999999',
            'skills' => 'الإسعافات الأولية، التنظيم',
            'availability' => 'دوام كامل',
            'status' => 'pending',
            'locale' => 'ar',
        ]);

        Newsletter::query()->firstOrCreate(
            ['email' => 'subscriber@example.com'],
            [
                'is_subscribed' => true,
                'subscribed_at' => now(),
            ]
        );
    }
}
