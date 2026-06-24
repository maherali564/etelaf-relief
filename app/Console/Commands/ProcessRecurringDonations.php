<?php

namespace App\Console\Commands;

use App\Models\Donation;
use App\Services\Payment\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * أمر معالجة التبرعات المتكررة (Recurring Donations Command)
 *
 * أمر Artisan مخصص لمعالجة التبرعات المتكررة المستحقة الدفع.
 * يتم تشغيله عادةً عبر مجدول المهام (Task Scheduler / Cron Job).
 *
 * يقوم الأمر بما يلي:
 * - البحث عن جميع التبرعات المتكررة النشطة (is_recurring = true, status = completed)
 * - تصفية التبرعات المستحقة بناءً على الفاصل الزمني (شهري، ربع سنوي، سنوي)
 * - إنشاء تبرع جديد لكل تبرع مستحق بنفس بيانات المتبرع والمبلغ
 * - إذا كان التبرع الأصلي يحتوي على اشتراك Stripe، يتم تخطيه (تتم المعالجة عبر Webhook)
 * - محاولة بدء عملية الدفع عبر خدمة الدفع (PaymentService)
 * - تسجيل أي أخطاء تحدث أثناء المعالجة
 */
class ProcessRecurringDonations extends Command
{
    /**
     * توقيع الأمر Artisan المستخدم لتشغيله من سطر الأوامر.
     *
     * @var string
     */
    protected $signature = 'donations:process-recurring';

    /**
     * وصف قصير للأمر يظهر في قائمة Artisan.
     *
     * @var string
     */
    protected $description = 'Process recurring donations that are due';

    /**
     * تنفيذ أمر معالجة التبرعات المتكررة.
     *
     * يبحث عن التبرعات المتكررة المستحقة بناءً على الفاصل الزمني لكل تبرع،
     * وينشئ تبرعات جديدة بنفس البيانات ويحاول بدء عملية الدفع.
     *
     * خطوات المعالجة:
     * 1. جلب جميع التبرعات المتكررة النشطة
     * 2. تصفية التبرعات المستحقة حسب الفاصل الزمني (شهري، ربع سنوي، سنوي)
     * 3. تخطي التبرعات التي تديرها اشتراكات Stripe
     * 4. إنشاء تبرع جديد وحفظه
     * 5. بدء عملية الدفع عبر PaymentService إن وجدت طريقة دفع
     * 6. تسجيل النتائج والأخطاء
     *
     * @return int رمز النجاح (Command::SUCCESS) أو الفشل
     */
    public function handle(): int
    {
        $now = now();
        $dueDonations = Donation::where('is_recurring', true)
            ->where('status', 'completed')
            ->whereNotNull('recurring_interval')
            ->get()
            ->filter(function ($donation) use ($now) {
                $lastDonated = $donation->donated_at ?? $donation->created_at;

                return match ($donation->recurring_interval) {
                    'monthly' => $lastDonated->copy()->addMonth()->lte($now),
                    'quarterly' => $lastDonated->copy()->addMonths(3)->lte($now),
                    'yearly' => $lastDonated->copy()->addYear()->lte($now),
                    default => false,
                };
            });

        $processed = 0;

        foreach ($dueDonations as $original) {
            try {
                // If the original donation has a Stripe subscription, skip it — webhook handles payments
                if ($original->stripe_subscription_id) {
                    $this->info("Skipping donation {$original->id}: managed by Stripe subscription {$original->stripe_subscription_id}");

                    continue;
                }

                $donation = new Donation();
                $donation->fill([
                    'donor_name' => $original->donor_name,
                    'email' => $original->email,
                    'phone' => $original->phone,
                    'amount' => $original->amount,
                    'currency' => $original->currency,
                    'is_anonymous' => $original->is_anonymous,
                    'is_recurring' => true,
                    'recurring_interval' => $original->recurring_interval,
                    'locale' => $original->locale,
                    'status' => 'pending',
                    'donated_at' => $now,
                ]);
                $donation->donor_id = $original->donor_id;
                $donation->payment_method_id = $original->payment_method_id;
                $donation->project_id = $original->project_id;
                $donation->post_id = $original->post_id;
                $donation->story_id = $original->story_id;
                $donation->save();

                if ($donation->payment_method_id) {
                    $payment = PaymentService::fromDonation($donation);
                    $result = $payment->initPayment($donation);

                    if ($result['type'] === 'redirect' && ! empty($result['url'])) {
                        $donation->update([
                            'status' => 'pending',
                            'notes' => 'Recurring donation pending redirect to '.$result['url'],
                        ]);
                        Log::info('Recurring donation redirect created', [
                            'donation_id' => $donation->id,
                            'url' => $result['url'],
                        ]);
                    } elseif ($result['type'] === 'instructions') {
                        $donation->update(['status' => 'pending']);
                    }
                } else {
                    $donation->update(['status' => 'completed']);
                }

                $processed++;
            } catch (\Exception $e) {
                Log::error('Failed to process recurring donation', [
                    'original_donation_id' => $original->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Processed {$processed} recurring donations.");

        return Command::SUCCESS;
    }
}
