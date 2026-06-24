<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::query()->updateOrCreate(['slug' => 'privacy-policy'], [
            'title' => [
                'ar' => 'سياسة الخصوصية',
                'en' => 'Privacy Policy',
                'es' => 'Política de Privacidad',
                'id' => 'Kebijakan Privasi',
                'tr' => 'Gizlilik Politikası',
            ],
            'content' => [
                'ar' => <<<'HTML'
<h2>مقدمة</h2>
<p>نحن في <strong>منصة ساهم</strong> (يشار إليها فيما يلي بـ "المنصة" أو "نحن" أو "خاصتنا") نلتزم بحماية خصوصية وأمن بيانات المستخدمين والمتبرعين والزوار. توضح سياسة الخصوصية هذه كيفية جمع واستخدام وحماية المعلومات الشخصية التي تقدمها لنا عند استخدام منصتنا الإلكترونية أو التبرع أو التواصل معنا.</p>
<p>باستخدامك لمنصة ساهم، فإنك توافق على جمع واستخدام معلوماتك وفقاً لهذه السياسة. يرجى قراءة هذه السياسة بعناية لفهم ممارساتنا فيما يتعلق ببياناتك الشخصية.</p>

<h2>المعلومات التي نجمعها</h2>

<h3>1. المعلومات التي تقدمها طواعية</h3>
<ul>
<li><strong>معلومات الحساب:</strong> الاسم الكامل، عنوان البريد الإلكتروني، رقم الهاتف، وكلمة المرور عند إنشاء حساب متبرع.</li>
<li><strong>معلومات الدفع:</strong> بيانات بطاقة الائتمان أو معلومات الحساب البنكي (يتم معالجتها عبر بوابات دفع آمنة مثل Stripe، PayPal، و Wise ولا يتم تخزينها على خوادمنا).</li>
<li><strong>معلومات التبرع:</strong> المبلغ، العملة، وتاريخ التبرع وأي رسائل أو تعليقات تختار إضافتها.</li>
<li><strong>معلومات التواصل:</strong> عندما تتصل بنا عبر نموذج الاتصال أو البريد الإلكتروني أو واتساب.</li>
<li><strong>معلومات التطوع:</strong> المهارات، والتوفر، والمؤهلات عند التسجيل كمتطوع.</li>
<li><strong>معلومات النشرة البريدية:</strong> البريد الإلكتروني عند الاشتراك في النشرة البريدية.</li>
</ul>

<h3>2. المعلومات التي نجمعها تلقائياً</h3>
<ul>
<li><strong>بيانات التصفح:</strong> عنوان IP، نوع المتصفح، نظام التشغيل، الصفحات التي تزورها، الوقت الذي تقضيه على المنصة.</li>
<li><strong>ملفات تعريف الارتباط (Cookies):</strong> نستخدم ملفات تعريف الارتباط لتحسين تجربة التصفح وتخصيص المحتوى وتحليل حركة المرور.</li>
<li><strong>بيانات الموقع:</strong> الموقع الجغرافي التقريبي بناءً على عنوان IP الخاص بك.</li>
</ul>

<h2>كيف نستخدم معلوماتك</h2>
<p>نستخدم المعلومات التي نجمعها للأغراض التالية:</p>
<ul>
<li><strong>معالجة التبرعات:</strong> لاستكمال وإدارة عمليات التبرع الخاصة بك وتحديثك بحالة تبرعك.</li>
<li><strong>التواصل معك:</strong> للرد على استفساراتك، وإرسال إشعارات حول تبرعاتك، وتقديم الدعم الفني.</li>
<li><strong>تحسين المنصة:</strong> لتحليل كيفية استخدام المنصة وتحسين تجربة المستخدم وتطوير ميزات جديدة.</li>
<li><strong>إرسال التحديثات:</strong> لإرسال النشرات البريدية والتحديثات حول مشاريعنا (بموافقتك المسبقة).</li>
<li><strong>الامتثال القانوني:</strong> للامتثال للالتزامات القانونية والتنظيمية السارية.</li>
<li><strong>منع الاحتيال:</strong> للكشف عن الأنشطة الاحتيالية أو غير المصرح بها ومنعها.</li>
</ul>

<h2>حماية بيانات الدفع</h2>
<p>نحن نأخذ أمن معلومات الدفع على محمل الجد. يرجى ملاحظة ما يلي:</p>
<ul>
<li>نحن <strong>لا نخزن</strong> أرقام بطاقات الائتمان الكاملة أو رموز CVV أو معلومات الحساب البنكي على خوادمنا.</li>
<li>تتم معالجة جميع مدفوعات بطاقات الائتمان من خلال بوابات دفع موثوقة ومتوافقة مع معايير PCI DSS (Stripe، PayPal).</li>
<li>يتم تشفير جميع معلومات الدفع أثناء النقل باستخدام بروتوكول TLS (طبقة المقابس الآمنة).</li>
<li>نستخدم توثيقاً إضافياً (مثل 3D Secure) لبعض المعاملات لتعزيز الأمان.</li>
<li>بالنسبة للمدفوعات بالعملات الرقمية (Crypto)، يتم توفير عنوان المحفظة فقط ولا نقوم بتخزين مفاتيحك الخاصة.</li>
</ul>

<h2>مشاركة المعلومات مع أطراف ثالثة</h2>
<p>لا نقوم ببيع معلوماتك الشخصية أو تأجيرها أو مشاركتها مع أطراف ثالثة للأغراض التسويقية. قد نشارك معلوماتك في الحالات التالية:</p>
<ul>
<li><strong>بوابات الدفع:</strong> نشارك معلومات الدفع الضرورية مع Stripe، PayPal، و Wise لمعالجة تبرعاتك.</li>
<li><strong>مزودي الخدمة:</strong> مع شركات استضافة الويب، خدمات التحليلات، وخدمات البريد الإلكتروني التي تساعدنا في تشغيل المنصة.</li>
<li><strong>الالتزام القانوني:</strong> عندما يقتضي القانون ذلك أو استجابة لطلب قانوني ساري المفعول.</li>
<li><strong>حماية الحقوق:</strong> لحماية حقوقنا أو ممتلكاتنا أو سلامة مستخدمينا.</li>
</ul>
<p>جميع الأطراف الثالثة التي نشارك بياناتك معها ملزمة باتفاقيات تعاقدية تحمي بياناتك وتحد من استخدامها للأغراض المحددة فقط.</p>

<h2>ملفات تعريف الارتباط (Cookies)</h2>
<p>نستخدم الأنواع التالية من ملفات تعريف الارتباط:</p>
<ul>
<li><strong>الأساسية (Essential):</strong> ضرورية لتشغيل المنصة بشكل صحيح وتمكين الميزات الأساسية مثل تسجيل الدخول وإدارة الجلسات.</li>
<li><strong>التحليلية (Analytics):</strong> تساعدنا على فهم كيفية تفاعل الزوار مع المنصة، وتحسين الأداء العام، وتحديد الصفحات الأكثر زيارة.</li>
<li><strong>التسويقية (Marketing):</strong> تُستخدم لتتبع المستخدمين عبر المواقع وتقديم محتوى وحملات مخصصة.</li>
</ul>
<p>يمكنك التحكم في إعدادات ملفات تعريف الارتباط من خلال لوحة الإعدادات المتاحة عند زيارتك للمنصة. يمكنك أيضاً ضبط إعدادات المتصفح لرفض جميع ملفات تعريف الارتباط.</p>

<h2>حقوقك</h2>
<p>لديك الحقوق التالية فيما يتعلق ببياناتك الشخصية:</p>
<ul>
<li><strong>حق الوصول:</strong> طلب نسخة من البيانات الشخصية التي نحتفظ بها عنك.</li>
<li><strong>حق التصحيح:</strong> طلب تصحيح أي بيانات غير دقيقة أو غير كاملة.</li>
<li><strong>حق الحذف:</strong> طلب حذف بياناتك الشخصية في ظروف معينة.</li>
<li><strong>حق تقييد المعالجة:</strong> طلب تقييد معالجة بياناتك في ظروف معينة.</li>
<li><strong>حق نقل البيانات:</strong> طلب نقل بياناتك إلى منظمة أخرى بتنسيق منظم وشائع الاستخدام.</li>
<li><strong>حق الاعتراض:</strong> الاعتراض على معالجة بياناتك للأغراض التسويقية.</li>
<li><strong>حق سحب الموافقة:</strong> سحب موافقتك في أي وقت عندما تعتمد المعالجة على الموافقة.</li>
</ul>
<p>لممارسة أي من هذه الحقوق، يرجى الاتصال بنا على <strong>info@sahem.org</strong>.</p>

<h2>حماية البيانات والأمان</h2>
<p>نطبق إجراءات أمنية صارمة لحماية بياناتك من الوصول غير المصرح به أو التعديل أو الإفشاء أو الإتلاف:</p>
<ul>
<li><strong>التشفير:</strong> جميع البيانات المنقولة بين متصفحك وخوادمنا مشفرة باستخدام TLS 1.3.</li>
<li><strong>التحكم في الوصول:</strong> الوصول إلى بياناتك مقصور على الموظفين المصرح لهم فقط الذين يحتاجون إليها لأداء مهامهم.</li>
<li><strong>التدقيق المنتظم:</strong> نقوم بمراجعة أمنية دورية للأنظمة والخوادم لضمان أعلى مستويات الحماية.</li>
<li><strong>النسخ الاحتياطي:</strong> نأخذ نسخاً احتياطية منتظمة للبيانات لضمان الاستمرارية في حالات الطوارئ.</li>
<li><strong>التحديثات الأمنية:</strong> نقوم بتحديث جميع الأنظمة والبرامج بأحدث التصحيحات الأمنية بشكل منتظم.</li>
</ul>

<h2>الاحتفاظ بالبيانات</h2>
<p>نحتفظ ببياناتك الشخصية فقط للمدة اللازمة لتحقيق الأغراض التي جمعت من أجلها، أو للامتثال للالتزامات القانونية. يتم حذف البيانات أو إخفاء هويتها بشكل آمن عندما لا تعود هناك حاجة إليها:</p>
<ul>
<li><strong>بيانات الحساب:</strong> تُحتفظ بها طالما أن حسابك نشط، ولمدة 3 سنوات بعد آخر نشاط.</li>
<li><strong>سجلات التبرع:</strong> تُحتفظ بها لمدة 7 سنوات للامتثال للمتطلبات القانونية والمحاسبية.</li>
<li><strong>سجلات الاتصال:</strong> تُحتفظ بها لمدة سنتين من تاريخ آخر اتصال.</li>
<li><strong>بيانات التحليلات:</strong> تُحتفظ بها بشكل مجهول لمدة 26 شهراً.</li>
</ul>

<h2>الخدمات الخارجية والروابط</h2>
<p>قد تحتوي منصتنا على روابط لمواقع خارجية مثل PayPal و Stripe ومنصات التواصل الاجتماعي. نحن لسنا مسؤولين عن ممارسات الخصوصية لهذه المواقع الخارجية. نوصي بمراجعة سياسات الخصوصية الخاصة بها قبل تقديم أي معلومات شخصية.</p>

<h2>خصوصية الأطفال</h2>
<p>منصتنا غير موجهة للأطفال دون سن 18 عاماً. نحن لا نجمع عمداً معلومات شخصية من الأطفال دون سن 18. إذا علمنا أننا جمعنا معلومات شخصية من طفل دون سن 18، فسنتخذ الخطوات اللازمة لحذف هذه المعلومات.</p>

<h2>التعديلات على سياسة الخصوصية</h2>
<p>قد نقوم بتحديث سياسة الخصوصية هذه من وقت لآخر. سنقوم بإخطارك بأي تغييرات جوهرية عن طريق نشر السياسة الجديدة على هذه الصفحة وتحديث تاريخ "آخر تحديث" في أعلى الصفحة. نشجعك على مراجعة هذه الصفحة بشكل دوري للاطلاع على أي تغييرات.</p>

<h2>الاتصال بنا</h2>
<p>إذا كانت لديك أي أسئلة أو استفسارات حول سياسة الخصوصية هذه أو ممارسات الخصوصية لدينا، يرجى الاتصال بنا عبر:</p>
<ul>
<li><strong>البريد الإلكتروني:</strong> info@sahem.org</li>
<li><strong>الهاتف:</strong> +972 59 918 4228</li>
<li><strong>واتساب:</strong> +972599184228</li>
</ul>
HTML
,
                'en' => <<<'HTML'
<h2>Introduction</h2>
<p>At <strong>Sahem Platform</strong> (referred to as "the Platform," "we," "us," or "our"), we are committed to protecting the privacy and security of our users, donors, and visitors. This Privacy Policy explains how we collect, use, and safeguard the personal information you provide when using our platform, making donations, or contacting us.</p>
<p>By using Sahem Platform, you agree to the collection and use of your information in accordance with this policy. Please read this policy carefully to understand our practices regarding your personal data.</p>

<h2>Information We Collect</h2>

<h3>1. Information You Voluntarily Provide</h3>
<ul>
<li><strong>Account Information:</strong> Full name, email address, phone number, and password when creating a donor account.</li>
<li><strong>Payment Information:</strong> Credit card data or bank account details (processed through secure payment gateways such as Stripe, PayPal, and Wise; not stored on our servers).</li>
<li><strong>Donation Information:</strong> Amount, currency, date of donation, and any messages or comments you choose to add.</li>
<li><strong>Contact Information:</strong> When you contact us via the contact form, email, or WhatsApp.</li>
<li><strong>Volunteer Information:</strong> Skills, availability, and qualifications when registering as a volunteer.</li>
<li><strong>Newsletter Information:</strong> Email address when subscribing to our newsletter.</li>
</ul>

<h3>2. Information Collected Automatically</h3>
<ul>
<li><strong>Browsing Data:</strong> IP address, browser type, operating system, pages visited, time spent on the platform.</li>
<li><strong>Cookies:</strong> We use cookies to enhance your browsing experience, personalize content, and analyze traffic.</li>
<li><strong>Location Data:</strong> Approximate geographic location based on your IP address.</li>
</ul>

<h2>How We Use Your Information</h2>
<p>We use the information we collect for the following purposes:</p>
<ul>
<li><strong>Processing Donations:</strong> To complete and manage your donation transactions and update you on their status.</li>
<li><strong>Communication:</strong> To respond to your inquiries, send notifications about your donations, and provide technical support.</li>
<li><strong>Platform Improvement:</strong> To analyze platform usage, improve user experience, and develop new features.</li>
<li><strong>Sending Updates:</strong> To send newsletters and updates about our projects (with your prior consent).</li>
<li><strong>Legal Compliance:</strong> To comply with applicable legal and regulatory obligations.</li>
<li><strong>Fraud Prevention:</strong> To detect and prevent fraudulent or unauthorized activities.</li>
</ul>

<h2>Payment Data Protection</h2>
<p>We take the security of payment information seriously. Please note:</p>
<ul>
<li>We <strong>do not store</strong> full credit card numbers, CVV codes, or bank account details on our servers.</li>
<li>All credit card payments are processed through trusted, PCI DSS-compliant payment gateways (Stripe, PayPal).</li>
<li>All payment information is encrypted during transmission using TLS protocol.</li>
<li>We use additional authentication (such as 3D Secure) for certain transactions to enhance security.</li>
<li>For cryptocurrency payments, only the wallet address is provided; we do not store your private keys.</li>
</ul>

<h2>Information Sharing with Third Parties</h2>
<p>We do not sell, rent, or share your personal information with third parties for marketing purposes. We may share your information in the following cases:</p>
<ul>
<li><strong>Payment Gateways:</strong> We share necessary payment information with Stripe, PayPal, and Wise to process your donations.</li>
<li><strong>Service Providers:</strong> With web hosting companies, analytics services, and email services that help us operate the platform.</li>
<li><strong>Legal Compliance:</strong> When required by law or in response to a valid legal request.</li>
<li><strong>Protection of Rights:</strong> To protect our rights, property, or the safety of our users.</li>
</ul>
<p>All third parties with whom we share your data are bound by contractual agreements that protect your data and limit its use to specified purposes only.</p>

<h2>Cookies</h2>
<p>We use the following types of cookies:</p>
<ul>
<li><strong>Essential:</strong> Necessary for the platform to function properly and enable basic features such as login and session management.</li>
<li><strong>Analytics:</strong> Help us understand how visitors interact with the platform, improve performance, and identify most visited pages.</li>
<li><strong>Marketing:</strong> Used to track users across websites and deliver personalized content and campaigns.</li>
</ul>
<p>You can control cookie settings through the settings panel available when you visit the platform. You can also configure your browser to reject all cookies.</p>

<h2>Your Rights</h2>
<p>You have the following rights regarding your personal data:</p>
<ul>
<li><strong>Right of Access:</strong> Request a copy of the personal data we hold about you.</li>
<li><strong>Right to Rectification:</strong> Request correction of inaccurate or incomplete data.</li>
<li><strong>Right to Erasure:</strong> Request deletion of your personal data under certain circumstances.</li>
<li><strong>Right to Restrict Processing:</strong> Request restriction of processing under certain circumstances.</li>
<li><strong>Right to Data Portability:</strong> Request transfer of your data to another organization in a structured format.</li>
<li><strong>Right to Object:</strong> Object to processing of your data for marketing purposes.</li>
<li><strong>Right to Withdraw Consent:</strong> Withdraw your consent at any time when processing is based on consent.</li>
</ul>
<p>To exercise any of these rights, please contact us at <strong>info@sahem.org</strong>.</p>

<h2>Data Security</h2>
<p>We implement strict security measures to protect your data from unauthorized access, alteration, disclosure, or destruction:</p>
<ul>
<li><strong>Encryption:</strong> All data transmitted between your browser and our servers is encrypted using TLS 1.3.</li>
<li><strong>Access Control:</strong> Access to your data is restricted to authorized personnel who need it to perform their duties.</li>
<li><strong>Regular Audits:</strong> We conduct periodic security reviews of systems and servers to ensure the highest level of protection.</li>
<li><strong>Backup:</strong> We perform regular data backups to ensure continuity in emergencies.</li>
<li><strong>Security Updates:</strong> We update all systems and software with the latest security patches regularly.</li>
</ul>

<h2>Data Retention</h2>
<p>We retain your personal data only for as long as necessary to fulfill the purposes for which it was collected, or to comply with legal obligations. Data is securely deleted or anonymized when no longer needed:</p>
<ul>
<li><strong>Account Data:</strong> Retained while your account is active, and for 3 years after last activity.</li>
<li><strong>Donation Records:</strong> Retained for 7 years to comply with legal and accounting requirements.</li>
<li><strong>Contact Records:</strong> Retained for 2 years from the date of last contact.</li>
<li><strong>Analytics Data:</strong> Retained in anonymized form for 26 months.</li>
</ul>

<h2>External Services and Links</h2>
<p>Our platform may contain links to external sites such as PayPal, Stripe, and social media platforms. We are not responsible for the privacy practices of these external sites. We recommend reviewing their privacy policies before submitting any personal information.</p>

<h2>Children's Privacy</h2>
<p>Our platform is not directed to children under 18. We do not knowingly collect personal information from children under 18. If we become aware that we have collected personal information from a child under 18, we will take steps to delete that information.</p>

<h2>Changes to This Privacy Policy</h2>
<p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy on this page and updating the "Last Updated" date at the top of the page. We encourage you to review this page periodically for any changes.</p>

<h2>Contact Us</h2>
<p>If you have any questions or concerns about this Privacy Policy or our privacy practices, please contact us at:</p>
<ul>
<li><strong>Email:</strong> info@sahem.org</li>
<li><strong>Phone:</strong> +972 59 918 4228</li>
<li><strong>WhatsApp:</strong> +972599184228</li>
</ul>
HTML
,
                'es' => <<<'HTML'
<h2>Introducción</h2>
<p>En <strong>Plataforma Sahem</strong>, estamos comprometidos con la protección de la privacidad y seguridad de nuestros usuarios, donantes y visitantes. Esta Política de Privacidad explica cómo recopilamos, utilizamos y protegemos la información personal que proporciona al utilizar nuestra plataforma.</p>
HTML
,
                'id' => <<<'HTML'
<h2>Pendahuluan</h2>
<p>Di <strong>Platform Sahem</strong>, kami berkomitmen untuk melindungi privasi dan keamanan pengguna, donatur, dan pengunjung kami. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi yang Anda berikan saat menggunakan platform kami.</p>
HTML
,
                'tr' => <<<'HTML'
<h2>Giriş</h2>
<p><strong>Sahem Platformu</strong>'nda, kullanıcılarımızın, bağışçılarımızın ve ziyaretçilerimizin gizliliğini ve güvenliğini korumaya kararlıyız. Bu Gizlilik Politikası, platformumuzu kullanırken sağladığınız kişisel bilgileri nasıl topladığımızı, kullandığımızı ve koruduğumuzu açıklamaktadır.</p>
HTML
,
            ],
            'is_active' => true,
        ]);
    }
}
