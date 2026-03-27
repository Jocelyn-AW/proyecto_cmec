<?php

namespace App\Providers;

use App\Listeners\StripeWebhookListener;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

use App\Models\Attendee;
use App\Models\BankDetail;
use App\Models\Banner;
use App\Models\Post;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Candidate;
use App\Models\Clinic;
use App\Models\Course;
use App\Models\DirectoryData;
use App\Models\InvoiceData;
use App\Models\Media;
use App\Models\Member;
use App\Models\Membership;
use App\Models\User;
use App\Models\Webinar;
use App\Models\Conference;
use App\Models\News;
use App\Models\AcademicSession;
use App\Models\Album;
use App\Models\MembershipPrice;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookReceived;
use Stripe\Stripe;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Relation::enforceMorphMap([
            'attendee' => Attendee::class,
            'bank_detail' => BankDetail::class,
            'banner' => Banner::class,
            'candidate' => Candidate::class,
            'clinic' => Clinic::class,
            'conference' => Conference::class,
            'pre_conference' => Conference::class,
            'trans_conference' => Conference::class,
            'course' => Course::class,
            'directory_data' => DirectoryData::class,
            'invoice_data' => InvoiceData::class,
            'member' => Member::class,
            'membership' => Membership::class,
            'membership_price' => MembershipPrice::class,
            'payment_method' => PaymentMethod::class,
            'payment' => Payment::class,
            'post' => Post::class,
            'user' => User::class,
            'webinar' => Webinar::class,
            'news' => News::class,
            'academic_session' => AcademicSession::class,
            'album' => Album::class,
        ]);

        Vite::prefetch(concurrency: 3);

        //Stripe
        $this->setStripeKeys();
        Cashier::useCustomerModel(Member::class);
        Event::listen(WebhookReceived::class, StripeWebhookListener::class);
    }

    private function setStripeKeys () :void 
    {
        try {
            $stripeSecret = Setting::get('stripe_secret');
            $stripeKey = Setting::get('stripe_key');

            if ($stripeSecret && $stripeKey) {
                config(['cashier.secret' => $stripeSecret]);
                config(['cashier.key'    => $stripeKey]);
                Stripe::setApiKey($stripeSecret);
            }
        } catch (Exception) {
            // si no tiene datos se usara el .env por defecto
        }
    }
}
