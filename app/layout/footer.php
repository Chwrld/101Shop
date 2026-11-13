<footer class="footer">
    <div class="container-fluid">
        <div class="footer-in">
            <p class="mb-0">Repair Service</p>
        </div>
    </div>
</footer>

<!-- Ensure pages restored from the back/forward cache are reloaded so server-side auth runs -->
<script>
    // When a page is loaded from bfcache (persisted), reload to ensure session validation runs server-side
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            // Use replace so history isn't polluted
            window.location.replace(window.location.href);
        }
    });
</script>