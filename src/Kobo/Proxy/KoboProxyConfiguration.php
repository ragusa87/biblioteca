<?php

namespace App\Kobo\Proxy;

use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Request;

class KoboProxyConfiguration
{
    private bool $useProxy = true;
    private bool $useProxyEverywhere = false;

    private string $imageApiUrl = '';
    private string $storeApiUrl = '';
    private string $readingServiceUrl = '';

    public function useProxy(): bool
    {
        return $this->useProxy;
    }

    public function useProxyEverywhere(): bool
    {
        return $this->useProxyEverywhere;
    }

    public function getStoreApiUrl(): string
    {
        if ($this->storeApiUrl === '') {
            throw new \InvalidArgumentException('Store API URL is not set');
        }

        return $this->storeApiUrl;
    }

    public function setStoreApiUrl(string $storeApiUrl): self
    {
        $this->storeApiUrl = $storeApiUrl;

        return $this;
    }

    public function getImageApiUrl(): string
    {
        if ($this->storeApiUrl === '') {
            throw new \InvalidArgumentException('Image API URL is not set');
        }

        return $this->imageApiUrl;
    }

    public function isImageHostUrl(Request|RequestInterface $request): bool
    {
        $uri = $request instanceof Request ? $request->getRequestUri() : (string) $request->getUri();

        return str_ends_with($uri, '.jpg')
            || str_ends_with($uri, '.png')
            || str_ends_with($uri, '.jpeg');
    }

    public function isReadingServiceUrl(Request|RequestInterface $request): bool
    {
        $uri = $request instanceof Request ? $request->getRequestUri() : (string) $request->getUri();

        return str_contains($uri, '/api/v3/content/');
    }

    public function setImageApiUrl(string $imageApiUrl): KoboProxyConfiguration
    {
        $this->imageApiUrl = $imageApiUrl;

        return $this;
    }

    public function setEnabled(bool $useProxy): KoboProxyConfiguration
    {
        $this->useProxy = $useProxy;

        return $this;
    }

    public function getNativeInitializationJson(): array
    {
        return [
            'account_page' => 'https://www.kobo.com/account/settings',
            'account_page_rakuten' => 'https://my.rakuten.co.jp/',
            'add_device' => 'https://storeapi.kobo.com/v1/user/add-device',
            'add_entitlement' => 'https://storeapi.kobo.com/v1/library/{RevisionIds}',
            'affiliaterequest' => 'https://storeapi.kobo.com/v1/affiliate',
            'assets' => 'https://storeapi.kobo.com/v1/assets',
            'audiobook' => 'https://storeapi.kobo.com/v1/products/audiobooks/{ProductId}',
            'audiobook_detail_page' => 'https://www.kobo.com/{region}/{language}/audiobook/{slug}',
            'audiobook_landing_page' => 'https://www.kobo.com/{region}/{language}/audiobooks',
            'audiobook_preview' => 'https://storeapi.kobo.com/v1/products/audiobooks/{Id}/preview',
            'audiobook_purchase_withcredit' => 'https://storeapi.kobo.com/v1/store/audiobook/{Id}',
            'audiobook_subscription_orange_deal_inclusion_url' => 'https://authorize.kobo.com/inclusion',
            'authorproduct_recommendations' => 'https://storeapi.kobo.com/v1/products/books/authors/recommendations',
            'autocomplete' => 'https://storeapi.kobo.com/v1/products/autocomplete',
            'blackstone_header' => [
                'key' => 'x-amz-request-payer',
                'value' => 'requester',
            ],
            'book' => 'https://storeapi.kobo.com/v1/products/books/{ProductId}',
            'book_detail_page' => 'https://www.kobo.com/{region}/{language}/ebook/{slug}',
            'book_detail_page_rakuten' => 'http://books.rakuten.co.jp/rk/{crossrevisionid}',
            'book_landing_page' => 'https://www.kobo.com/ebooks',
            'book_subscription' => 'https://storeapi.kobo.com/v1/products/books/subscriptions',
            'browse_history' => 'https://storeapi.kobo.com/v1/user/browsehistory',
            'categories' => 'https://storeapi.kobo.com/v1/categories',
            'categories_page' => 'https://www.kobo.com/ebooks/categories',
            'category' => 'https://storeapi.kobo.com/v1/categories/{CategoryId}',
            'category_featured_lists' => 'https://storeapi.kobo.com/v1/categories/{CategoryId}/featured',
            'category_products' => 'https://storeapi.kobo.com/v1/categories/{CategoryId}/products',
            'checkout_borrowed_book' => 'https://storeapi.kobo.com/v1/library/borrow',
            'client_authd_referral' => 'https://authorize.kobo.com/api/AuthenticatedReferral/client/v1/getLink',
            'configuration_data' => 'https://storeapi.kobo.com/v1/configuration',
            'content_access_book' => 'https://storeapi.kobo.com/v1/products/books/{ProductId}/access',
            'customer_care_live_chat' => 'https://v2.zopim.com/widget/livechat.html?key=Y6gwUmnu4OATxN3Tli4Av9bYN319BTdO',
            'daily_deal' => 'https://storeapi.kobo.com/v1/products/dailydeal',
            'deals' => 'https://storeapi.kobo.com/v1/deals',
            'delete_entitlement' => 'https://storeapi.kobo.com/v1/library/{Ids}',
            'delete_tag' => 'https://storeapi.kobo.com/v1/library/tags/{TagId}',
            'delete_tag_items' => 'https://storeapi.kobo.com/v1/library/tags/{TagId}/items/delete',
            'device_auth' => 'https://storeapi.kobo.com/v1/auth/device',
            'device_refresh' => 'https://storeapi.kobo.com/v1/auth/refresh',
            'dictionary_host' => 'https://ereaderfiles.kobo.com',
            'discovery_host' => 'https://discovery.kobobooks.com',
            'display_parental_controls_enabled' => 'False',
            'ereaderdevices' => 'https://storeapi.kobo.com/v2/products/EReaderDeviceFeeds',
            'eula_page' => 'https://www.kobo.com/termsofuse?style=onestore',
            'exchange_auth' => 'https://storeapi.kobo.com/v1/auth/exchange',
            'external_book' => 'https://storeapi.kobo.com/v1/products/books/external/{Ids}',
            'facebook_sso_page' => 'https://authorize.kobo.com/signin/provider/Facebook/login?returnUrl=http://kobo.com/',
            'featured_list' => 'https://storeapi.kobo.com/v1/products/featured/{FeaturedListId}',
            'featured_lists' => 'https://storeapi.kobo.com/v1/products/featured',
            'featuredlist2' => 'https://storeapi.kobo.com/v2/products/list/featured',
            'free_books_page' => [
                'EN' => 'https://www.kobo.com/{region}/{language}/p/free-ebooks',
                'FR' => 'https://www.kobo.com/{region}/{language}/p/livres-gratuits',
                'IT' => 'https://www.kobo.com/{region}/{language}/p/libri-gratuiti',
                'NL' => 'https://www.kobo.com/{region}/{language}/List/bekijk-het-overzicht-van-gratis-ebooks/QpkkVWnUw8sxmgjSlCbJRg',
                'PT' => 'https://www.kobo.com/{region}/{language}/p/livros-gratis',
            ],
            'fte_feedback' => 'https://storeapi.kobo.com/v1/products/ftefeedback',
            'funnel_metrics' => 'https://storeapi.kobo.com/v1/funnelmetrics',
            'get_download_keys' => 'https://storeapi.kobo.com/v1/library/downloadkeys',
            'get_download_link' => 'https://storeapi.kobo.com/v1/library/downloadlink',
            'get_tests_request' => 'https://storeapi.kobo.com/v1/analytics/gettests',
            'giftcard_epd_redeem_url' => 'https://www.kobo.com/{storefront}/{language}/redeem-ereader',
            'giftcard_redeem_url' => 'https://www.kobo.com/{storefront}/{language}/redeem',
            'gpb_flow_enabled' => 'False',
            'help_page' => 'http://www.kobo.com/help',
            'image_host' => '//cdn.kobo.com/book-images/',
            'image_url_quality_template' => 'https://cdn.kobo.com/book-images/{ImageId}/{Width}/{Height}/{Quality}/{IsGreyscale}/image.jpg',
            'image_url_template' => 'https://cdn.kobo.com/book-images/{ImageId}/{Width}/{Height}/false/image.jpg',
            'kobo_audiobooks_credit_redemption' => 'False',
            'kobo_audiobooks_enabled' => 'True',
            'kobo_audiobooks_orange_deal_enabled' => 'False',
            'kobo_audiobooks_subscriptions_enabled' => 'False',
            'kobo_display_price' => 'True',
            'kobo_dropbox_link_account_enabled' => 'False',
            'kobo_google_tax' => 'False',
            'kobo_googledrive_link_account_enabled' => 'False',
            'kobo_nativeborrow_enabled' => 'False',
            'kobo_onedrive_link_account_enabled' => 'False',
            'kobo_onestorelibrary_enabled' => 'False',
            'kobo_privacyCentre_url' => 'https://www.kobo.com/privacy',
            'kobo_redeem_enabled' => 'True',
            'kobo_shelfie_enabled' => 'False',
            'kobo_subscriptions_enabled' => 'True',
            'kobo_superpoints_enabled' => 'False',
            'kobo_wishlist_enabled' => 'True',
            'library_book' => 'https://storeapi.kobo.com/v1/user/library/books/{LibraryItemId}',
            'library_items' => 'https://storeapi.kobo.com/v1/user/library',
            'library_metadata' => 'https://storeapi.kobo.com/v1/library/{Ids}/metadata',
            'library_prices' => 'https://storeapi.kobo.com/v1/user/library/previews/prices',
            'library_search' => 'https://storeapi.kobo.com/v1/library/search',
            'library_sync' => 'https://storeapi.kobo.com/v1/library/sync',
            'love_dashboard_page' => 'https://www.kobo.com/{region}/{language}/kobosuperpoints',
            'love_points_redemption_page' => 'https://www.kobo.com/{region}/{language}/KoboSuperPointsRedemption?productId={ProductId}',
            'magazine_landing_page' => 'https://www.kobo.com/emagazines',
            'more_sign_in_options' => 'https://authorize.kobo.com/signin?returnUrl=http://kobo.com/#allProviders',
            'notebooks' => 'https://storeapi.kobo.com/api/internal/notebooks',
            'notifications_registration_issue' => 'https://storeapi.kobo.com/v1/notifications/registration',
            'oauth_host' => 'https://oauth.kobo.com',
            'password_retrieval_page' => 'https://www.kobo.com/passwordretrieval.html',
            'personalizedrecommendations' => 'https://storeapi.kobo.com/v2/users/personalizedrecommendations',
            'pocket_link_account_start' => 'https://authorize.kobo.com/{region}/{language}/linkpocket',
            'post_analytics_event' => 'https://storeapi.kobo.com/v1/analytics/event',
            'ppx_purchasing_url' => 'https://purchasing.kobo.com',
            'privacy_page' => 'https://www.kobo.com/privacypolicy?style=onestore',
            'product_nextread' => 'https://storeapi.kobo.com/v1/products/{ProductIds}/nextread',
            'product_prices' => 'https://storeapi.kobo.com/v1/products/{ProductIds}/prices',
            'product_recommendations' => 'https://storeapi.kobo.com/v1/products/{ProductId}/recommendations',
            'product_reviews' => 'https://storeapi.kobo.com/v1/products/{ProductIds}/reviews',
            'products' => 'https://storeapi.kobo.com/v1/products',
            'productsv2' => 'https://storeapi.kobo.com/v2/products',
            'provider_external_sign_in_page' => 'https://authorize.kobo.com/ExternalSignIn/{providerName}?returnUrl=http://kobo.com/',
            'purchase_buy' => 'https://www.kobo.com/checkoutoption/',
            'purchase_buy_templated' => 'https://www.kobo.com/{region}/{language}/checkoutoption/{ProductId}',
            'quickbuy_checkout' => 'https://storeapi.kobo.com/v1/store/quickbuy/{PurchaseId}/checkout',
            'quickbuy_create' => 'https://storeapi.kobo.com/v1/store/quickbuy/purchase',
            'rakuten_token_exchange' => 'https://storeapi.kobo.com/v1/auth/rakuten_token_exchange',
            'rating' => 'https://storeapi.kobo.com/v1/products/{ProductId}/rating/{Rating}',
            'reading_services_host' => 'https://readingservices.kobo.com',
            'reading_state' => 'https://storeapi.kobo.com/v1/library/{Ids}/state',
            'redeem_interstitial_page' => 'https://www.kobo.com',
            'registration_page' => 'https://authorize.kobo.com/signup?returnUrl=http://kobo.com/',
            'related_items' => 'https://storeapi.kobo.com/v1/products/{Id}/related',
            'remaining_book_series' => 'https://storeapi.kobo.com/v1/products/books/series/{SeriesId}',
            'rename_tag' => 'https://storeapi.kobo.com/v1/library/tags/{TagId}',
            'review' => 'https://storeapi.kobo.com/v1/products/reviews/{ReviewId}',
            'review_sentiment' => 'https://storeapi.kobo.com/v1/products/reviews/{ReviewId}/sentiment/{Sentiment}',
            'shelfie_recommendations' => 'https://storeapi.kobo.com/v1/user/recommendations/shelfie',
            'sign_in_page' => 'https://auth.kobobooks.com/ActivateOnWeb',
            'social_authorization_host' => 'https://social.kobobooks.com:8443',
            'social_host' => 'https://social.kobobooks.com',
            'store_home' => 'www.kobo.com/{region}/{language}',
            'store_host' => 'www.kobo.com',
            'store_newreleases' => 'https://www.kobo.com/{region}/{language}/List/new-releases/961XUjtsU0qxkFItWOutGA',
            'store_search' => 'https://www.kobo.com/{region}/{language}/Search?Query={query}',
            'store_top50' => 'https://www.kobo.com/{region}/{language}/ebooks/Top',
            'subs_landing_page' => 'https://www.kobo.com/{region}/{language}/plus',
            'subs_management_page' => 'https://www.kobo.com/{region}/{language}/account/subscriptions',
            'subs_plans_page' => 'https://www.kobo.com/{region}/{language}/plus/plans',
            'subs_purchase_buy_templated' => 'https://www.kobo.com/{region}/{language}/Checkoutoption/{ProductId}/{TierId}',
            'tag_items' => 'https://storeapi.kobo.com/v1/library/tags/{TagId}/Items',
            'tags' => 'https://storeapi.kobo.com/v1/library/tags',
            'taste_profile' => 'https://storeapi.kobo.com/v1/products/tasteprofile',
            'terms_of_sale_page' => 'https://authorize.kobo.com/{region}/{language}/terms/termsofsale',
            'topproducts' => 'https://storeapi.kobo.com/v2/products/list/topproducts',
            'update_accessibility_to_preview' => 'https://storeapi.kobo.com/v1/library/{EntitlementIds}/preview',
            'use_one_store' => 'True',
            'user_loyalty_benefits' => 'https://storeapi.kobo.com/v1/user/loyalty/benefits',
            'user_platform' => 'https://storeapi.kobo.com/v1/user/platform',
            'user_profile' => 'https://storeapi.kobo.com/v1/user/profile',
            'user_ratings' => 'https://storeapi.kobo.com/v1/user/ratings',
            'user_recommendations' => 'https://storeapi.kobo.com/v1/user/recommendations',
            'user_reviews' => 'https://storeapi.kobo.com/v1/user/reviews',
            'user_wishlist' => 'https://storeapi.kobo.com/v1/user/wishlist',
            'userguide_host' => 'https://ereaderfiles.kobo.com',
            'wishlist_page' => 'https://www.kobo.com/{region}/{language}/account/wishlist',
        ];
    }

    public function setUseProxyEverywhere(bool $useProxyEverywhere): self
    {
        $this->useProxyEverywhere = $useProxyEverywhere;

        return $this;
    }

    public function getReadingServiceUrl(): string
    {
        if ($this->readingServiceUrl === '') {
            throw new \InvalidArgumentException('Reading Service URL is not set');
        }

        return $this->readingServiceUrl;
    }

    public function setReadingServiceUrl(string $readingServiceUrl): self
    {
        $this->readingServiceUrl = $readingServiceUrl;

        return $this;
    }
}
