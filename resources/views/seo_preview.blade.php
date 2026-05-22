<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    
    <!-- Standard SEO Meta Tags -->
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook / Instagram / TikTok / WhatsApp / Telegram / Discord / Slack / YouTube -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ $canonical_url }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $image_url }}">
    <meta property="og:image:secure_url" content="{{ $image_url }}">
    <meta property="og:site_name" content="{{ $site_name }}">
    <meta property="product:price:amount" content="{{ $price }}">
    <meta property="product:price:currency" content="BDT">

    <!-- Twitter / X Card Metadata -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $canonical_url }}">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $image_url }}">

    <!-- Redirect real users/browsers to the actual storefront React SPA -->
    <script type="text/javascript">
        window.location.href = "{!! $storefront_url !!}";
    </script>
    
    <!-- Fallback meta refresh redirect if JS is disabled but it's a real browser -->
    <meta http-equiv="refresh" content="0;url={!! $storefront_url !!}">
</head>
<body>
    <p>Redirecting you to the product details page... If you are not redirected, <a href="{!! $storefront_url !!}">click here</a>.</p>
</body>
</html>
