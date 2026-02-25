<?php

namespace App\Providers;

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
        Relation::enforceMorphMap([
            'attendee' => Attendee::class,
            'bank_detail' => BankDetail::class,
            'banner' => Banner::class,
            'candidate' => Candidate::class,
            'clinic' => Clinic::class,
            'conference' => Conference::class,
            'course' => Course::class,
            'directory_data' => DirectoryData::class,
            'invoice_data' => InvoiceData::class,
            // 'media' => Media::class,
            'member' => Member::class,
            'membership' => Membership::class,
            'payment_method' => PaymentMethod::class,
            'payment' => Payment::class,
            'post' => Post::class,
            'user' => User::class,
            'webinar' => Webinar::class,
            'news' => News::class,
        ]);

        Vite::prefetch(concurrency: 3);

        
    }
}
