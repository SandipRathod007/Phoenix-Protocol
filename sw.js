// The "Empty" Service Worker
// This satisfies Chrome's installation requirement.
self.addEventListener('fetch', function(event) {
    // We do nothing, just let the network handle requests.
    return;
});