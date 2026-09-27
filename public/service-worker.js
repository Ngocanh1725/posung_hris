const CACHE_NAME = 'posung-hris-v1';
const urlsToCache = [
  '/posung_hris/public/',
  '/posung_hris/public/css/style.min.css'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  // Bỏ qua các API call để luôn lấy dữ liệu mới
  if (event.request.url.includes('/api/') || event.request.method !== 'GET') {
    return;
  }
  
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response; // Trả về file từ Cache nếu có
        }
        return fetch(event.request); // Lấy từ mạng nếu chưa Cache
      })
  );
});

// Xóa cache cũ khi update
self.addEventListener('activate', event => {
  const cacheAllowlist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheAllowlist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
