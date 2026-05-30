<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\SEOMeta;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $isPartner = $request->query('as') === 'partner';

        $title = __('contact_seo_title');
        $description = __('contact_seo_description');
        $canonicalUrl = url(app()->getLocale() === 'en' ? '/en/contact-us' : '/contact-us');

        SEOTools::setTitle($title, false);
        SEOTools::setDescription($description);
        SEOTools::setCanonical($canonicalUrl);

        SEOMeta::removeMeta('robots');
        SEOMeta::setRobots('index,follow,max-image-preview:large');

        SEOTools::opengraph()->setUrl($canonicalUrl);
        SEOTools::opengraph()->addProperty('type', 'website');
        SEOTools::opengraph()->setTitle($title);
        SEOTools::opengraph()->setDescription($description);

        SEOTools::twitter()->setTitle($title);
        SEOTools::twitter()->setDescription($description);

        return view('public.contact', [
            'isPartner' => $isPartner,
        ]);
    }
}
