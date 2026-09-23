<?php
/**
 * TATVAM - Meta Pixel & CAPI Universal Script Header
 * Automatically renders client-side Meta Pixel code for all pages.
 */
require_once __DIR__ . '/../config.php';

$pixel_id = (defined('META_PIXEL_ID') && META_PIXEL_ID !== '123456789012345' && !empty(META_PIXEL_ID)) ? META_PIXEL_ID : '';
?>
<?php if ($pixel_id): ?>
<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?php echo htmlspecialchars($pixel_id); ?>');
fbq('track', 'PageView');

// Helper to reliably extract/store fbclid & fbp/fbc cookies for server CAPI attribution
(function() {
    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(^|;\\s*)(' + name + ')=([^;]*)'));
        return match ? decodeURIComponent(match[3]) : '';
    }
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/; SameSite=Lax";
    }

    var params = new URLSearchParams(window.location.search);
    var fbclid = params.get('fbclid');
    if (fbclid) {
        try {
            sessionStorage.setItem('tatvam_fbclid', fbclid);
            localStorage.setItem('tatvam_fbclid', fbclid);
            if (!getCookie('_fbc')) {
                setCookie('_fbc', 'fb.1.' + (+new Date()) + '.' + fbclid, 90);
            }
        } catch(e) {}
    }

    window.getMetaTrackingData = function() {
        var fbp = getCookie('_fbp');
        var fbc = getCookie('_fbc');
        var storedFbclid = fbclid || sessionStorage.getItem('tatvam_fbclid') || localStorage.getItem('tatvam_fbclid') || '';
        if (!fbc && storedFbclid) {
            fbc = 'fb.1.' + (+new Date()) + '.' + storedFbclid;
        }
        return {
            fbp: fbp || '',
            fbc: fbc || '',
            fbclid: storedFbclid || ''
        };
    };
})();
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=<?php echo htmlspecialchars($pixel_id); ?>&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
<?php endif; ?>