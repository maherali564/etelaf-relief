<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\DonationReviewController;
use App\Http\Controllers\AdminChatController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConfirmationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DonorAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\TaxInvoiceController;
use App\Http\Controllers\TransparencyController;
use App\Http\Controllers\VolunteerController;
use App\Http\Controllers\WebhookController;
use App\Livewire\AdminChatPanel;
use App\Models\Donation;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/'.config('app.locale', 'ar')));

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::prefix('{locale}')
    ->where(['locale' => 'ar|en|es|id|tr|sv'])
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/news', [PostController::class, 'index'])->name('posts.index');
        Route::get('/news/{slug}', [PostController::class, 'show'])->name('posts.show');
        Route::get('/page/{slug}', [PageController::class, 'show'])->name('pages.show');
        Route::get('/about', [AboutController::class, 'index'])->name('about.index');
        Route::get('/transparency', [TransparencyController::class, 'index'])->name('transparency.index');
        Route::get('/announcements', [PostController::class, 'announcements'])->name('announcements.index');
        Route::get('/success-stories', [PostController::class, 'successStories'])->name('success-stories.index');
        Route::get('/stories', [StoryController::class, 'index'])->name('stories.index');
        Route::get('/stories/{id}', [StoryController::class, 'show'])->name('stories.show');
        Route::get('/donate/project/{slug}', [DonationController::class, 'projectPage'])->name('donate.project');
        Route::get('/donate/post/{slug}', [DonationController::class, 'postPage'])->name('donate.post');
        Route::get('/donate/story/{id}', [DonationController::class, 'storyPage'])->name('donate.story');
        Route::get('/donate', function () {
            $locale = request()->route('locale');
            $params = http_build_query(request()->only(['campaign_id', 'project_id', 'post_id']));

            return redirect()->to('/'.$locale.'#donate'.($params ? '?'.$params : ''));
        })->name('donate.page');
        Route::get('/donor-wall', [DonationController::class, 'donorWall'])->name('donor.wall');
        Route::post('/donate', [DonationController::class, 'store'])->name('donate.store')->middleware('throttle:donations');
        Route::post('/contact', [ContactController::class, 'store'])->name('contact.store')->middleware('throttle:contact');
        Route::get('/volunteer', [VolunteerController::class, 'dashboard'])->name('volunteer.dashboard');
        Route::get('/volunteer/register', [VolunteerController::class, 'register'])->name('volunteer.register');
        Route::post('/volunteer', [VolunteerController::class, 'store'])->name('volunteer.store')->middleware('throttle:volunteer');
        Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store')->middleware('throttle:newsletter');
        Route::get('/newsletter/verify/{token}', [NewsletterController::class, 'verify'])->name('newsletter.verify');

        Route::get('/payment/success/{donation}', [PaymentController::class, 'success'])->name('payment.success');
        Route::get('/payment/cancel/{donation}', [PaymentController::class, 'cancel'])->name('payment.cancel');
        Route::get('/payment/instructions/{donation}', [PaymentController::class, 'instructions'])->name('payment.instructions');
        Route::get('/payment/certificate/{donation}', [CertificateController::class, 'download'])->name('payment.certificate');
        Route::get('/payment/tax-invoice/{donation}', [TaxInvoiceController::class, 'download'])->name('payment.tax_invoice');

        // Payment confirmation routes
        Route::get('/payment/confirm/{donation}', [ConfirmationController::class, 'create'])->name('payment.confirm');
        Route::post('/payment/confirm/{donation}', [ConfirmationController::class, 'store'])->middleware('throttle:10,1');

        // Donor auth
        Route::get('/donor/register', [DonorAuthController::class, 'showRegister'])->name('donor.register');
        Route::post('/donor/register', [DonorAuthController::class, 'register'])->name('donor.register.post')->middleware('throttle:donor_register');
        Route::get('/donor/login', [DonorAuthController::class, 'showLogin'])->name('donor.login');
        Route::post('/donor/login', [DonorAuthController::class, 'login'])->name('donor.login.post')->middleware('throttle:donor_login');
        Route::post('/donor/logout', [DonorAuthController::class, 'logout'])->name('donor.logout');
        Route::get('/donor/dashboard', [DonorAuthController::class, 'dashboard'])->name('donor.dashboard')->middleware('auth:donor');

        // Donor wall polling
        Route::get('/currency/rates', [CurrencyController::class, 'rates'])->name('currency.rates');
        Route::get('/donations/latest', function () {
            $limit = (int) request('limit', 50);
            $donations = Donation::completed()->latest()->limit(min($limit, 100))->get();
            $html = view('partials.donor-feed-items', ['donations' => $donations])->render();

            return response()->json([
                'html' => $html,
                'totals' => [
                    'raised' => (int) $donations->sum('amount'),
                    'donors' => $donations->pluck('email')->unique()->count(),
                    'donations' => $donations->count(),
                ],
            ]);
        })->name('donations.latest');
    });

Route::post('/webhook/stripe', [WebhookController::class, 'stripe'])->name('payment.webhook.stripe')->middleware('throttle:60,1');
Route::post('/webhook/paypal', [WebhookController::class, 'paypal'])->name('payment.webhook.paypal')->middleware('throttle:60,1');
Route::post('/webhook/wise', [WebhookController::class, 'wise'])->name('payment.webhook.wise')->middleware('throttle:60,1');

// Live Chat Routes (Visitor)
Route::post('/chat/start', [ChatController::class, 'start'])->name('chat.start')->middleware('throttle:10,1');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send')->middleware('throttle:30,1');
Route::post('/chat/typing', [ChatController::class, 'typing'])->name('chat.typing')->middleware('throttle:10,1');
Route::post('/chat/close', [ChatController::class, 'close'])->name('chat.close')->middleware('throttle:10,1');
Route::get('/chat/messages/{sessionId}', [ChatController::class, 'messages'])->name('chat.messages')->whereNumber('sessionId');

// Admin Chat Routes
Route::middleware(['auth', 'chat-access'])->prefix('admin/chats')->group(function () {
    Route::get('/', AdminChatPanel::class)->name('admin.chats');
    Route::get('/sessions', [AdminChatController::class, 'sessions']);
    Route::get('/messages/{sessionId}', [AdminChatController::class, 'sessionMessages'])->whereNumber('sessionId');
    Route::post('/assign', [AdminChatController::class, 'assign']);
    Route::post('/send', [AdminChatController::class, 'send']);
    Route::post('/close', [AdminChatController::class, 'close']);
    Route::post('/typing', [AdminChatController::class, 'typing']);
    Route::get('/unread-count', [AdminChatController::class, 'unreadCount']);
});

Route::middleware(['auth'])->prefix('admin/donations')->group(function () {
    Route::post('/{donation}/approve', [DonationReviewController::class, 'approve'])->name('admin.donations.approve');
    Route::post('/{donation}/reject', [DonationReviewController::class, 'reject'])->name('admin.donations.reject');
});

// Admin locale switcher
Route::get('/admin/locale/{locale}', function ($locale) {
    $supported = config('app.supported_locales', ['ar', 'en', 'es', 'id', 'tr', 'sv']);
    if (in_array($locale, $supported)) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }

    return redirect()->back();
})->name('admin.locale')->middleware('auth');
