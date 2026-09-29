// POSUNG HRIS - Service Worker (Network-First Cache Strategy)
const CACHE_NAME = 'posung-hris-v2-' + Date.now();

// Cài đặt và kích hoạt ngay lập tức
self.addEventListener('install', event => {
  self.skipWaiting();
});

// Xóa sạch toàn bộ cache cũ khi activate
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          return caches.delete(cacheName);
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Chiến lược Network-First: Luôn tải trang HTML mới nhất từ server
self.addEventListener('fetch', event => {
  // Chỉ xử lý GET request
  if (event.request.method !== 'GET') {
    return;
  }

  // Đối với request tải trang HTML (Navigation), luôn luôn lấy trực tiếp từ Network
  if (event.request.mode === 'navigate' || (event.request.headers.get('accept') && event.request.headers.get('accept').includes('text/html'))) {
    event.respondWith(
      fetch(event.request).catch(() => {
        return caches.match(event.request);
      })
    );
    return;
  }

  // Đối với tài nguyên tĩnh (ảnh, css, js), ưu tiên fetch trước
  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request);
    })
  );
});
