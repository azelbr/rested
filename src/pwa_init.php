<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('service-worker.js')
                .then(reg => console.log('SW registred'))
                .catch(err => console.log('SW fail', err));
        });
    }
</script>